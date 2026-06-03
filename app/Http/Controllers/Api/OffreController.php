<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OffreController extends Controller
{
    // GET /api/offres — accessible à tous les connectés (apprenants + mentors)
    public function index(Request $request)
    {
        $query = Offre::with('creator:id,name')->orderByDesc('created_at');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('filiere') && $request->filiere !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('filiere', $request->filiere)
                  ->orWhere('filiere', 'Toutes');
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title',   'like', "%$s%")
                  ->orWhere('company','like', "%$s%");
            });
        }

        return response()->json($query->get());
    }

    // GET /api/offres/{id}
    public function show($id)
    {
        return response()->json(Offre::with('creator:id,name')->findOrFail($id));
    }

    // POST /api/admin/offres — admin uniquement
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'company'      => 'required|string|max:255',
            'location'     => 'required|string|max:255',
            'type'         => 'required|in:Stage,CDI,CDD,Freelance,Alternance',
            'duration'     => 'required|string|max:100',
            'filiere'      => 'required|in:GL,SI,IM,IA,SEIOT,Toutes',
            'description'  => 'required|string',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
        ]);

        $offre = Offre::create([
            'title'        => $request->title,
            'company'      => $request->company,
            'location'     => $request->location,
            'type'         => $request->type,
            'duration'     => $request->duration,
            'filiere'      => $request->filiere,
            'description'  => $request->description,
            'requirements' => $request->requirements ?? [],
            'created_by'   => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Offre créée avec succès',
            'data'    => $offre->load('creator:id,name'),
        ], 201);
    }

    // PUT /api/admin/offres/{id}
    public function update(Request $request, $id)
    {
        $offre = Offre::findOrFail($id);

        $request->validate([
            'title'        => 'sometimes|string|max:255',
            'company'      => 'sometimes|string|max:255',
            'location'     => 'sometimes|string|max:255',
            'type'         => 'sometimes|in:Stage,CDI,CDD,Freelance,Alternance',
            'duration'     => 'sometimes|string|max:100',
            'filiere'      => 'sometimes|in:GL,SI,IM,IA,SEIOT,Toutes',
            'description'  => 'sometimes|string',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
        ]);

        $offre->update($request->only([
            'title', 'company', 'location', 'type',
            'duration', 'filiere', 'description', 'requirements',
        ]));

        return response()->json([
            'message' => 'Offre mise à jour',
            'data'    => $offre->load('creator:id,name'),
        ]);
    }

    // DELETE /api/admin/offres/{id}
    public function destroy($id)
    {
        Offre::findOrFail($id)->delete();
        return response()->json(['message' => 'Offre supprimée']);
    }
}