<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IfriStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::select('id', 'name', 'email', 'role', 'created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name',  'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        $users = $query->orderByDesc('created_at')->get();

        return response()->json($users);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
        }

        if ($user->role === 'student') {
            IfriStudent::where('user_id', $user->id)
                ->update(['is_registered' => false, 'user_id' => null]);

            IfriStudent::where('name', $user->name)
                ->where('is_registered', true)
                ->whereNull('user_id')
                ->update(['is_registered' => false]);
        }

        // Révoquer tous les tokens Sanctum avant suppression
        $user->tokens()->delete();

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}