<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use Illuminate\Http\Request;

class AdminMentorController extends Controller
{
    // GET /api/admin/mentors?status=pending|approved|rejected
    public function index(Request $request)
    {
        $query = Mentor::with('user:id,name,email');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    // GET /api/admin/mentors/{id}
    public function show($id)
    {
        return response()->json(
            Mentor::with('user:id,name,email')->findOrFail($id)
        );
    }

    // POST /api/admin/mentors/{id}/approve
    public function approve($id)
    {
        $mentor = Mentor::findOrFail($id);

        if ($mentor->status !== 'pending') {
            return response()->json(['message' => 'Ce mentor a déjà été traité'], 400);
        }

        $mentor->update(['status' => 'approved']);

        return response()->json(['message' => 'Mentor approuvé. Il peut maintenant se connecter.']);
    }

    // POST /api/admin/mentors/{id}/reject
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $mentor = Mentor::with('user')->findOrFail($id);

        if ($mentor->status !== 'pending') {
            return response()->json(['message' => 'Ce mentor a déjà été traité'], 400);
        }

        // Supprimer le User pour libérer l'email
        // → le mentor pourra se réinscrire avec la même adresse si besoin
        // La suppression du User entraîne la suppression du Mentor
        // grâce au onDelete('cascade') sur user_id dans la migration
        if ($mentor->user) {
            $mentor->user->delete();
        } else {
            // Sécurité : si pas de user lié, on marque juste comme rejeté
            $mentor->update(['status' => 'rejected']);
        }

        return response()->json(['message' => 'Candidature rejetée. L\'email est de nouveau disponible.']);
    }
}