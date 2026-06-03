<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Epreuve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EpreuveController extends Controller
{
    public function index(Request $request)
    {
        $query = Epreuve::with('uploader:id,name');

        if ($request->has('filiere') && $request->filiere !== 'all') {
            $query->where('filiere', $request->filiere);
        }

        if ($request->has('year') && $request->year !== 'all') {
            $query->where('year', $request->year);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $epreuves = $query->orderBy('year', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json($epreuves);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'filiere' => 'required|in:GL,SI,IM,IA,SEIOT',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'semester' => 'required|in:S1,S2',
            'type' => 'required|in:Examen final,Rattrapage,TP noté,Projet',
            'file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('epreuves', $fileName, 'public');

        $epreuve = Epreuve::create([
            'title' => $request->title,
            'filiere' => $request->filiere,
            'year' => $request->year,
            'semester' => $request->semester,
            'type' => $request->type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Épreuve ajoutée avec succès',
            'epreuve' => $epreuve->load('uploader:id,name')
        ], 201);
    }

    public function show($id)
    {
        $epreuve = Epreuve::with('uploader:id,name')->findOrFail($id);
        return response()->json($epreuve);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        $epreuve = Epreuve::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'filiere' => 'sometimes|in:GL,SI,IM,IA,SEIOT',
            'year' => 'sometimes|integer|min:2000|max:' . (date('Y') + 1),
            'semester' => 'sometimes|in:S1,S2',
            'type' => 'sometimes|in:Examen final,Rattrapage,TP noté,Projet',
            'file' => 'sometimes|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($epreuve->file_path);

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('epreuves', $fileName, 'public');

            $epreuve->file_path = $filePath;
            $epreuve->file_name = $file->getClientOriginalName();
            $epreuve->file_size = $file->getSize();
        }

        $epreuve->fill($request->only(['title', 'filiere', 'year', 'semester', 'type']));
        $epreuve->save();

        return response()->json([
            'message' => 'Épreuve mise à jour avec succès',
            'epreuve' => $epreuve->load('uploader:id,name')
        ]);
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        $epreuve = Epreuve::findOrFail($id);

        Storage::disk('public')->delete($epreuve->file_path);

        $epreuve->delete();

        return response()->json([
            'message' => 'Épreuve supprimée avec succès'
        ]);
    }

    public function download($id)
    {
        $epreuve = Epreuve::findOrFail($id);

        if (!Storage::disk('public')->exists($epreuve->file_path)) {
            return response()->json([
                'message' => 'Fichier introuvable'
            ], 404);
        }

        $epreuve->incrementDownloads();

        $file = Storage::disk('public')->get($epreuve->file_path);
        
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $epreuve->file_name . '"');
    }

    public function stats()
    {
        $stats = [
            'total' => Epreuve::count(),
            'by_filiere' => Epreuve::selectRaw('filiere, COUNT(*) as count')
                                   ->groupBy('filiere')
                                   ->get(),
            'by_year' => Epreuve::selectRaw('year, COUNT(*) as count')
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->get(),
            'total_downloads' => Epreuve::sum('downloads'),
            'most_downloaded' => Epreuve::orderBy('downloads', 'desc')
                                       ->limit(5)
                                       ->get(),
        ];

        return response()->json($stats);
    }
}