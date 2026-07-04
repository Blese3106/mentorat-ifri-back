<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IfriStudent;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\NewMentorApplication;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AuthController extends Controller
{

    public function checkIdentifier(Request $request)
    {
        $request->validate(['identifier' => 'required']);
        $student = IfriStudent::where('identifier', $request->identifier)->first();
        if (!$student) return response()->json(['message' => 'Identifiant IFRI invalide'], 404);
        if ($student->is_registered) return response()->json(['message' => 'Cet étudiant possède déjà un compte'], 409);
        return response()->json([
            'message' => 'Identifiant valide',
            'student' => ['name' => $student->name, 'filiere' => $student->filiere, 'promotion' => $student->promotion],
        ]);
    }

    public function registerStudent(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6|confirmed',
        ]);

        $student = IfriStudent::where('identifier', $request->identifier)->first();
        if (!$student || $student->is_registered) {
            return response()->json(['message' => 'Inscription impossible'], 400);
        }

        $user = User::create([
            'name'     => $student->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'student',
        ]);

        $student->update([
            'is_registered' => true,
            'user_id'       => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['message' => 'Compte créé avec succès', 'user' => $user, 'token' => $token]);
    }

    public function registerMentor(Request $request)
    {
        $request->validate([
            'firstname'       => 'required|string|max:255',
            'lastname'        => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email|unique:mentors,email',
            'password'        => 'required|min:6|confirmed',
            'phone'           => 'required|string|max:20',
            'photo'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'oldstudent_ifri' => 'required|boolean',
            'promotion'       => 'nullable|required_if:oldstudent_ifri,true|integer',
            'filiere'         => 'nullable|string',
            'company'         => 'nullable|string',
            'poste'           => 'nullable|string',
            'email_contact'   => 'required|email',
            'experience'      => 'required|integer|min:0',
            'expertise'       => 'required|array|min:1',
            'expertise.*'     => 'string',
            'bio'             => 'required|string',
            'linkedin'        => 'required|url',
            'portfolio'       => 'nullable|url',
            'type'            => 'required|in:free,paid,both',
            'price'           => 'nullable|numeric|min:0',
            'cv'              => 'required|file|mimes:pdf,doc,docx|max:10240',
            'diplome'         => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->firstname . ' ' . $request->lastname,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'mentor',
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('mentors/photos', 'public');
            }
            $cvPath      = $request->file('cv')->store('mentors/cv', 'public');
            $diplomePath = null;
            if ($request->hasFile('diplome')) {
                $diplomePath = $request->file('diplome')->store('mentors/diplomes', 'public');
            }
            
            $mentor = Mentor::create([
                'user_id'         => $user->id,
                'firstname'       => $request->firstname,
                'lastname'        => $request->lastname,
                'photo'           => $photoPath,
                'email'           => $request->email,
                'phone'           => $request->phone,
                'oldstudent_ifri' => $request->boolean('oldstudent_ifri'),
                'promotion'       => $request->promotion ?: null,
                'filiere'         => $request->filiere   ?: null,
                'company'         => $request->company   ?: null,
                'poste'           => $request->poste     ?: null,
                'email_contact'   => $request->email_contact,
                'experience'      => $request->experience,
                'expertise'       => $request->expertise,
                'bio'             => $request->bio,
                'linkedin'        => $request->linkedin,
                'portfolio'       => $request->portfolio ?: null,
                'type'            => $request->type,
                'price'           => $request->price ?? 0,
                'path_cv'         => $cvPath,
                'diplome'         => $diplomePath,
                'status'          => 'pending',
            ]);

            DB::commit();

            try {
                $admins = User::where('role', 'admin')->get();
                NotificationFacade::send($admins, new NewMentorApplication($mentor));
            } catch (\Exception $e) {
                Log::warning('Email admin non envoyé : ' . $e->getMessage());
            }

            return response()->json([
                'message' => "Candidature envoyée ! Vous pourrez vous connecter une fois validé.",
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $user = Auth::user();

        if ($user->role === 'mentor') {
            $mentor = Mentor::where('user_id', $user->id)->first();
            if (!$mentor || $mentor->status === 'pending') {
                Auth::logout();
                return response()->json(['message' => "Votre candidature est en cours de validation."], 403);
            }
            if ($mentor->status === 'rejected') {
                Auth::logout();
                return response()->json(['message' => "Votre candidature a été refusée."], 403);
            }
        }

        $token    = $user->createToken('auth_token')->plainTextToken;
        $userData = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        if ($user->role === 'student') {
            $ifriStudent = IfriStudent::where('user_id', $user->id)->first()
                        ?? IfriStudent::where('name', $user->name)->first();
            if ($ifriStudent) {
                $userData['filiere']   = $ifriStudent->filiere;
                $userData['promotion'] = $ifriStudent->promotion;
            }
        }

        return response()->json(['user' => $userData, 'token' => $token, 'message' => 'Connexion réussie']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        if ($status === Password::RESET_LINK_SENT) return response()->json(['message' => 'Lien envoyé']);
        return response()->json(['message' => "Impossible d'envoyer le lien"], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => 'required|min:6|confirmed']);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        if ($status === Password::PASSWORD_RESET) return response()->json(['message' => 'Mot de passe réinitialisé']);
        return response()->json(['message' => 'Lien invalide ou expiré'], 400);
    }
}