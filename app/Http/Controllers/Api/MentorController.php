<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mentor;
use Illuminate\Http\Request;

class MentorController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Mentor::where('status', 'approved');

        if ($request->filled('filiere') && $request->filiere !== 'all') {
            $query->where('filiere', $request->filiere);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('firstname', 'like', "%$s%")
                  ->orWhere('lastname',  'like', "%$s%")
                  ->orWhere('bio',       'like', "%$s%")
                  ->orWhere('poste',     'like', "%$s%")
                  ->orWhere('company',   'like', "%$s%");
            });
        }

        $mentors = $query
            ->withCount([
                'mentoringRequests as sessions_count' => fn($q) => $q->where('status', 'completed'),
                
                'mentoringRequests as students_count' => fn($q) => $q->where('status', 'accepted'),
            ])
            ->orderBy('firstname')
            ->paginate(20);

        return response()->json($mentors);
    }

    public function show($id)
    {
        $mentor = Mentor::where('status', 'approved')
            ->withCount([
                'mentoringRequests as sessions_count' => fn($q) => $q->where('status', 'completed'),
                'mentoringRequests as students_count' => fn($q) => $q->where('status', 'accepted'),
            ])
            ->findOrFail($id);

        return response()->json($mentor);
    }
}