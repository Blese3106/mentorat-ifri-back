<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Mentor;
use App\Models\MentoringRequest;
use App\Models\MentoringSession;
use App\Notifications\MentoringRequestAccepted;
use App\Notifications\MentoringRequestRejected;
use App\Notifications\NewMentoringRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MentoringRequestController extends Controller
{
    public function mentors(Request $request)
    {
        $query = Mentor::where('status', 'approved');

        if ($request->filled('filiere') && $request->filiere !== 'all')
            $query->where('filiere', $request->filiere);
        if ($request->filled('type') && $request->type !== 'all')
            $query->where('type', $request->type);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('firstname', 'like', "%$s%")
                ->orWhere('lastname',  'like', "%$s%")
                ->orWhere('bio',       'like', "%$s%")
                ->orWhere('poste',     'like', "%$s%")
                ->orWhere('company',   'like', "%$s%")
            );
        }

        return response()->json(
            $query->withCount([
                'mentoringRequests as sessions_count' => fn($q) => $q->where('status', 'accepted'),
            ])->orderBy('firstname')->get()
        );
    }

    public function myProfile()
    {
        $mentor = Mentor::where('user_id', Auth::id())->first();
        return response()->json($mentor);
    }

    // ── CÔTÉ ÉTUDIANT 

    // GET /api/mentoring-requests
    public function studentIndex()
    {
        $requests = MentoringRequest::with('mentor')
            ->where('student_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $formatted = $requests->map(function ($req) {
            // Compter les messages non lus (envoyés par le mentor, pas encore lus)
            $unreadCount = ChatMessage::where('request_id', $req->id)
                ->where('sender_type', 'mentor')
                ->where('read', false)
                ->count();

            return [
                'id'             => $req->id,
                'subject'        => $req->subject,
                'message'        => $req->message,
                'preferred_date' => $req->preferred_date,
                'status'         => $req->status,
                'mentor_note'    => $req->mentor_note,
                'created_at'     => $req->created_at,
                'unread_count'   => $unreadCount,   // ← messages non lus
                'mentor'         => $req->mentor ? [
                    'id'        => $req->mentor->id,
                    'firstname' => $req->mentor->firstname,
                    'lastname'  => $req->mentor->lastname,
                    'photo'     => $req->mentor->photo,
                    'filiere'   => $req->mentor->filiere,
                    'poste'     => $req->mentor->poste,
                    'company'   => $req->mentor->company,
                    'type'      => $req->mentor->type,
                    'price'     => $req->mentor->price,
                ] : null,
            ];
        });

        return response()->json($formatted);
    }

    // GET /api/student/stats — stats pour le dashboard étudiant
    public function studentStats()
    {
        $userId   = Auth::id();
        $requests = MentoringRequest::where('student_id', $userId)->get();

        $acceptedRequests = $requests->where('status', 'accepted');

        // Sessions planifiées
        $sessionsCount = MentoringSession::whereIn('request_id', $acceptedRequests->pluck('id'))
            ->where('status', 'planned')
            ->count();

        // Sessions terminées
        $sessionsDone = MentoringSession::whereIn('request_id', $acceptedRequests->pluck('id'))
            ->where('status', 'done')
            ->count();

        // Messages non lus (du mentor vers l'étudiant)
        $unreadMessages = ChatMessage::whereIn('request_id', $acceptedRequests->pluck('id'))
            ->where('sender_type', 'mentor')
            ->where('read', false)
            ->count();

        // Prochain session planifiée
        $nextSession = MentoringSession::whereIn('request_id', $acceptedRequests->pluck('id'))
            ->where('status', 'planned')
            ->where('scheduled_at', '>=', now())
            ->with('request.mentor')
            ->orderBy('scheduled_at')
            ->first();

        return response()->json([
            'mentors_count'     => $acceptedRequests->count(),
            'sessions_planned'  => $sessionsCount,
            'sessions_done'     => $sessionsDone,
            'unread_messages'   => $unreadMessages,
            'requests_pending'  => $requests->where('status', 'pending')->count(),
            'next_session'      => $nextSession ? [
                'id'           => $nextSession->id,
                'title'        => $nextSession->title,
                'scheduled_at' => $nextSession->scheduled_at,
                'meet_link'    => $nextSession->meet_link,
                'mentor_name'  => $nextSession->request->mentor
                    ? $nextSession->request->mentor->firstname . ' ' . $nextSession->request->mentor->lastname
                    : 'Mentor',
            ] : null,
        ]);
    }

    // POST /api/mentoring-requests
    public function store(Request $request)
    {
        $request->validate([
            'mentor_id'      => 'required|exists:mentors,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'student_filiere'      => 'nullable|string|max:20',
            'student_niveau'       => 'nullable|string|max:10',
            'student_promotion'    => 'nullable|integer',
            'student_difficulties' => 'nullable|string|max:1000',
            'student_goals'        => 'nullable|string|max:1000',
            'preferred_date' => 'nullable|date|after:today',
        ]);

        $mentor = Mentor::where('id', $request->mentor_id)->where('status', 'approved')->first();
        if (!$mentor) return response()->json(['message' => 'Mentor non disponible'], 404);

        $existing = MentoringRequest::where('student_id', Auth::id())
            ->where('mentor_id', $request->mentor_id)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => $existing->status === 'pending'
                    ? 'Vous avez déjà une demande en attente pour ce mentor'
                    : 'Vous êtes déjà en relation avec ce mentor'
            ], 409);
        }

        $req = MentoringRequest::create([
            'student_id'     => Auth::id(),
            'mentor_id'      => $request->mentor_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'student_filiere'      => $request->student_filiere,
            'student_niveau'       => $request->student_niveau,
            'student_promotion'    => $request->student_promotion,
            'student_difficulties' => $request->student_difficulties,
            'student_goals'        => $request->student_goals,
            'preferred_date' => $request->preferred_date,
            'status'         => 'pending',
        ]);

        try {
            $mentorModel = Mentor::find($request->mentor_id);
            $mentorModel->user->notify(new NewMentoringRequest(
                Auth::user()->name,
                $req->subject,
                $req->message
            ));
            } catch (\Exception $e) {
                Log::warning('Email mentor non envoyé : ' . $e->getMessage());
            }

        return response()->json(['message' => 'Demande envoyée', 'data' => $req->load('mentor')], 201);
    }

    // ── CÔTÉ MENTOR ───────────────────────────────────────────────

    // GET /api/mentor/requests
    public function mentorIndex(Request $request)
    {
        $mentor = Mentor::where('user_id', Auth::id())->first();
        if (!$mentor) return response()->json(['message' => 'Profil mentor introuvable'], 404);

        $query = MentoringRequest::with('student:id,name,email')
            ->where('mentor_id', $mentor->id);

        if ($request->filled('status') && $request->status !== 'all')
            $query->where('status', $request->status);

        $requests = $query->orderByDesc('created_at')->get();

        $formatted = $requests->map(function ($req) {
            // Messages non lus (envoyés par l'étudiant)
            $unreadCount = ChatMessage::where('request_id', $req->id)
                ->where('sender_type', 'student')
                ->where('read', false)
                ->count();

            return [
                'id'           => $req->id,
                'subject'      => $req->subject,
                'message'      => $req->message,
                'preferred_date'=> $req->preferred_date,
                'status'       => $req->status,
                'mentor_note'  => $req->mentor_note,
                'created_at'   => $req->created_at,
                'unread_count' => $unreadCount,
                'student'      => $req->student ? [
                    'id'    => $req->student->id,
                    'name'  => $req->student->name,
                    'email' => $req->student->email,
                ] : null,
            ];
        });

        return response()->json($formatted);
    }

    // POST /api/mentor/requests/{id}/accept
    public function accept(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:500']);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        $req    = MentoringRequest::where('mentor_id', $mentor->id)->where('status', 'pending')->findOrFail($id);
        $req->update(['status' => 'accepted', 'mentor_note' => $request->note]);
        $request_model = MentoringRequest::findOrFail($id);
        $student = $request_model->student;           // relation user étudiant
        $mentorName = $request_model->mentor->firstname . ' ' . $request_model->mentor->lastname;
        $student->notify(new MentoringRequestAccepted($mentorName, $request_model->subject));
        return response()->json(['message' => 'Demande acceptée', 'data' => $req]);
    }

    // POST /api/mentor/requests/{id}/reject
    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string|max:500']);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        $req    = MentoringRequest::where('mentor_id', $mentor->id)->where('status', 'pending')->findOrFail($id);
        $req->update(['status' => 'rejected', 'mentor_note' => $request->note]);
        $request_model = MentoringRequest::findOrFail($id);
        $student = $request_model->student;
        $mentorName = $request_model->mentor->firstname . ' ' . $request_model->mentor->lastname;
        $note = $request->input('note');
        $student->notify(new MentoringRequestRejected($mentorName, $request_model->subject, $note));
        return response()->json(['message' => 'Demande refusée']);
    }

    // ── CHAT ─────────────────────────────────────────────────────

    private function authorizeChat(MentoringRequest $req): void
    {
        $userId = Auth::id();
        if ($req->student_id === $userId) return;
        $mentor = Mentor::where('user_id', $userId)->first();
        if ($mentor && $req->mentor_id === $mentor->id) return;
        abort(403, 'Non autorisé');
    }

    // GET /api/mentoring-requests/{id}/messages
    public function messages($id)
    {
        $req = MentoringRequest::findOrFail($id);
        $this->authorizeChat($req);

        // Marquer les messages de l'autre comme lus
        $userId  = Auth::id();
        $mentor  = Mentor::where('user_id', $userId)->first();
        $mySenderType = $mentor ? 'mentor' : 'student';
        $otherType    = $mySenderType === 'mentor' ? 'student' : 'mentor';

        ChatMessage::where('request_id', $id)
            ->where('sender_type', $otherType)
            ->where('read', false)
            ->update(['read' => true]);

        $messages = $req->messages()->with('sender:id,name')->get();

        // Ajouter l'URL du fichier si présent
        $storageUrl = env('APP_URL', 'http://localhost:8000') . '/storage/';
        $messages = $messages->map(function ($msg) use ($storageUrl) {
            $arr = $msg->toArray();
            if ($msg->file_path) {
                $arr['file_url'] = $storageUrl . $msg->file_path;
            }
            return $arr;
        });

        return response()->json($messages);
    }

    // POST /api/mentoring-requests/{id}/messages
    // Supporte texte ET fichier
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:2000',
            'file'    => 'nullable|file|max:10240', // max 10 Mo
        ]);

        // Au moins un des deux doit être présent
        if (!$request->filled('content') && !$request->hasFile('file')) {
            return response()->json(['message' => 'Message ou fichier requis'], 422);
        }

        $req = MentoringRequest::findOrFail($id);
        $this->authorizeChat($req);

        if ($req->status !== 'accepted') {
            return response()->json(['message' => 'Chat disponible uniquement pour les demandes acceptées'], 403);
        }

        $mentor     = Mentor::where('user_id', Auth::id())->first();
        $senderType = $mentor ? 'mentor' : 'student';

        // Upload du fichier si présent
        $filePath = null;
        $fileName = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getMimeType();
            $filePath = $file->store('chat/files', 'public');
        }

        $msg = ChatMessage::create([
            'request_id'  => $id,
            'sender_id'   => Auth::id(),
            'sender_type' => $senderType,
            'content'     => $request->content,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_type'   => $fileType,
        ]);

        $storageUrl = env('APP_URL', 'http://localhost:8000') . '/storage/';
        $data = $msg->load('sender:id,name')->toArray();
        if ($filePath) $data['file_url'] = $storageUrl . $filePath;

        return response()->json(['data' => $data], 201);
    }

    // ── SESSIONS ──────────────────────────────────────────────────

    public function sessions($id)
    {
        $req = MentoringRequest::findOrFail($id);
        $this->authorizeChat($req);
        return response()->json($req->sessions);
    }

    public function createSession(Request $request, $id)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'scheduled_at'     => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:240',
            'meet_link'        => 'nullable|url',
            'notes'            => 'nullable|string',
        ]);

        $req    = MentoringRequest::where('status', 'accepted')->findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($req->mentor_id !== $mentor->id) abort(403);

        $session = MentoringSession::create([
            'request_id'       => $id,
            'title'            => $request->title,
            'scheduled_at'     => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes ?? 60,
            'meet_link'        => $request->meet_link,
            'notes'            => $request->notes,
            'status'           => 'planned',
        ]);

        return response()->json(['message' => 'Session planifiée', 'data' => $session], 201);
    }

    public function updateSession(Request $request, $id)
    {
        $request->validate([
            'status'           => 'sometimes|in:planned,done,cancelled',
            'meet_link'        => 'nullable|url',
            'notes'            => 'nullable|string',
            'scheduled_at'     => 'sometimes|date',
            'title'            => 'sometimes|string',
            'duration_minutes' => 'sometimes|integer',
        ]);

        $session = MentoringSession::with('request')->findOrFail($id);
        $mentor  = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($session->request->mentor_id !== $mentor->id) abort(403);

        $session->update($request->only(['status','meet_link','notes','scheduled_at','title','duration_minutes']));
        return response()->json(['data' => $session]);
    }
}