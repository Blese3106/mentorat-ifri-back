<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicDiagnostic;
use App\Models\AcademicObjective;
use App\Models\AcademicPerformance;
use App\Models\AcademicCompetence;
use App\Models\AcademicTask;
use App\Models\AcademicDifficulty;
use App\Models\AcademicReport;
use App\Models\Mentor;
use App\Models\MentoringRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────

    private function getMentorRequest(int $requestId): MentoringRequest
    {
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        return MentoringRequest::where('id', $requestId)
            ->where('mentor_id', $mentor->id)
            ->where('status', 'accepted')
            ->firstOrFail();
    }

    private function getSharedRequest(int $requestId): MentoringRequest
    {
        $mentor = Mentor::where('user_id', Auth::id())->first();
        return MentoringRequest::where('id', $requestId)
            ->where(function ($q) use ($mentor) {
                $q->where('student_id', Auth::id());
                if ($mentor) $q->orWhere('mentor_id', $mentor->id);
            })
            ->where('status', 'accepted')
            ->firstOrFail();
    }

    // ════════════════════════════════════════════════════════════
    // DOSSIER COMPLET
    // GET /api/academic/{requestId}/dossier
    // ════════════════════════════════════════════════════════════

    public function getDossier(int $requestId)
    {
        $req = $this->getSharedRequest($requestId);

        $objectives   = AcademicObjective::where('mentoring_request_id', $requestId)->orderByDesc('created_at')->get();
        $performances = AcademicPerformance::where('mentoring_request_id', $requestId)->orderBy('matiere')->get();
        $competences  = AcademicCompetence::where('mentoring_request_id', $requestId)->get();
        $tasks        = AcademicTask::where('mentoring_request_id', $requestId)->orderBy('date_limite')->get();
        $difficulties = AcademicDifficulty::where('mentoring_request_id', $requestId)->orderByDesc('created_at')->get();
        $reports      = AcademicReport::where('mentoring_request_id', $requestId)->orderByDesc('date_fin')->get();
        $diagnostic   = AcademicDiagnostic::where('mentoring_request_id', $requestId)->first();

        return response()->json([
            'request'     => [
                'id'                  => $req->id,
                'student_filiere'     => $req->student_filiere,
                'student_niveau'      => $req->student_niveau,
                'student_promotion'   => $req->student_promotion,
                'student_difficulties'=> $req->student_difficulties,
                'student_goals'       => $req->student_goals,
            ],
            'diagnostic'   => $diagnostic,
            'objectives'   => $objectives,
            'performances' => $performances,
            'competences'  => $competences,
            'tasks'        => $tasks,
            'difficulties' => $difficulties,
            'reports'      => $reports,
            'stats' => [
                'objectives_total'    => $objectives->count(),
                'objectives_atteints' => $objectives->where('statut', 'atteint')->count(),
                'objectives_en_cours' => $objectives->where('statut', 'en_cours')->count(),
                'progression_moy'     => $objectives->count() > 0 ? round($objectives->avg('progression')) : 0,
                'tasks_total'         => $tasks->count(),
                'tasks_done'          => $tasks->where('statut', 'termine')->count(),
                'tasks_en_retard'     => $tasks->where('statut', 'en_retard')->count(),
                'difficulties_open'   => $difficulties->whereIn('statut', ['en_cours', 'persistante'])->count(),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // DIAGNOSTIC
    // ════════════════════════════════════════════════════════════

    public function getDiagnostic(int $requestId)
    {
        $this->getSharedRequest($requestId);
        $req        = MentoringRequest::find($requestId);
        $diagnostic = AcademicDiagnostic::where('mentoring_request_id', $requestId)->first();

        return response()->json([
            'diagnostic' => $diagnostic,
            'student_info' => [
                'filiere'      => $req->student_filiere,
                'niveau'       => $req->student_niveau,
                'promotion'    => $req->student_promotion,
                'difficulties' => $req->student_difficulties,
                'goals'        => $req->student_goals,
            ],
        ]);
    }

    public function saveDiagnostic(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'filiere'                  => 'nullable|string|max:20',
            'niveau'                   => 'nullable|string|max:10',
            'annee_promotion'          => 'nullable|integer',
            'competences_acquises'     => 'nullable|array',
            'competences_a_developper' => 'nullable|array',
            'soft_skills'              => 'nullable|array',
            'objectifs_personnels'     => 'nullable|string',
            'difficultes_initiales'    => 'nullable|string',
            'observations'             => 'nullable|string',
            'statut'                   => 'nullable|in:draft,completed',
        ]);

        $diagnostic = AcademicDiagnostic::updateOrCreate(
            ['mentoring_request_id' => $requestId],
            $request->only([
                'filiere','niveau','annee_promotion','competences_acquises',
                'competences_a_developper','soft_skills','objectifs_personnels',
                'difficultes_initiales','observations','statut',
            ])
        );

        return response()->json(['message' => 'Diagnostic sauvegardé', 'data' => $diagnostic]);
    }

    // ════════════════════════════════════════════════════════════
    // OBJECTIFS
    // ════════════════════════════════════════════════════════════

    public function getObjectives(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicObjective::where('mentoring_request_id', $requestId)->orderByDesc('created_at')->get()
        );
    }

    public function createObjective(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'titre'      => 'required|string|max:255',
            'description'=> 'nullable|string',
            'categorie'  => 'nullable|in:academique,technique,professionnel,soft_skill',
            'date_cible' => 'nullable|date|after:today',
        ]);

        $obj = AcademicObjective::create([
            'mentoring_request_id' => $requestId,
            'created_by'           => Auth::id(),
            'titre'                => $request->titre,
            'description'          => $request->description,
            'categorie'            => $request->categorie ?? 'academique',
            'date_cible'           => $request->date_cible,
        ]);

        return response()->json(['message' => 'Objectif créé', 'data' => $obj], 201);
    }

    public function updateObjective(Request $request, int $id)
    {
        $obj      = AcademicObjective::findOrFail($id);
        $mentor   = Mentor::where('user_id', Auth::id())->first();
        $isMentor = $mentor && $obj->mentoringRequest->mentor_id === $mentor->id;
        $isStudent= $obj->mentoringRequest->student_id === Auth::id();
        if (!$isMentor && !$isStudent) abort(403);

        $data = $isMentor
            ? $request->only(['titre','description','categorie','statut','progression','date_cible','commentaire_mentor'])
            : $request->only(['statut','progression']);

        if (($data['statut'] ?? null) === 'atteint') {
            $data['date_atteint'] = now()->toDateString();
            $data['progression']  = 100;
        }

        $obj->update($data);
        return response()->json(['message' => 'Objectif mis à jour', 'data' => $obj]);
    }

    public function deleteObjective(int $id)
    {
        $obj    = AcademicObjective::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($obj->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $obj->delete();
        return response()->json(['message' => 'Objectif supprimé']);
    }

    // ════════════════════════════════════════════════════════════
    // PERFORMANCES ACADÉMIQUES
    // ════════════════════════════════════════════════════════════

    public function getPerformances(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicPerformance::where('mentoring_request_id', $requestId)->orderBy('matiere')->get()
        );
    }

    public function savePerformance(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'matiere'          => 'required|string|max:100',
            'note'             => 'nullable|numeric|min:0|max:20',
            'note_precedente'  => 'nullable|numeric|min:0|max:20',
            'moyenne_generale' => 'nullable|numeric|min:0|max:20',
            'semestre'         => 'nullable|in:S1,S2,S3,S4,S5,S6',
            'annee_academique' => 'nullable|string|max:20',
            'ue_validee'       => 'nullable|boolean',
            'niveau_maitrise'  => 'nullable|in:faible,moyen,bon,excellent',
            'commentaire'      => 'nullable|string',
        ]);

        $perf = AcademicPerformance::updateOrCreate(
            ['mentoring_request_id' => $requestId, 'matiere' => $request->matiere],
            $request->except(['mentoring_request_id'])
        );

        return response()->json(['message' => 'Performance enregistrée', 'data' => $perf]);
    }

    public function deletePerformance(int $id)
    {
        $p      = AcademicPerformance::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($p->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $p->delete();
        return response()->json(['message' => 'Supprimé']);
    }

    // ════════════════════════════════════════════════════════════
    // COMPÉTENCES
    // ════════════════════════════════════════════════════════════

    public function getCompetences(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicCompetence::where('mentoring_request_id', $requestId)->get()
        );
    }

    public function saveCompetence(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'nom'               => 'required|string|max:100',
            'type'              => 'required|in:technique,soft_skill',
            'niveau_initial'    => 'nullable|integer|min:0|max:5',
            'niveau_actuel'     => 'nullable|integer|min:0|max:5',
            'niveau_cible'      => 'nullable|integer|min:0|max:5',
            'commentaire_mentor'=> 'nullable|string',
            'date_evaluation'   => 'nullable|date',
        ]);

        $comp = AcademicCompetence::updateOrCreate(
            ['mentoring_request_id' => $requestId, 'nom' => $request->nom],
            $request->except(['mentoring_request_id'])
        );

        return response()->json(['message' => 'Compétence enregistrée', 'data' => $comp]);
    }

    public function deleteCompetence(int $id)
    {
        $c      = AcademicCompetence::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($c->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $c->delete();
        return response()->json(['message' => 'Supprimé']);
    }

    // ════════════════════════════════════════════════════════════
    // TÂCHES
    // ════════════════════════════════════════════════════════════

    public function getTasks(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicTask::where('mentoring_request_id', $requestId)->orderBy('date_limite')->get()
        );
    }

    public function createTask(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'titre'      => 'required|string|max:255',
            'description'=> 'nullable|string',
            'type'       => 'nullable|in:lecture,exercice,projet,atelier,autre',
            'priorite'   => 'nullable|in:faible,normale,haute',
            'date_limite'=> 'nullable|date',
            'commentaire_mentor' => 'nullable|string',
        ]);

        $task = AcademicTask::create([
            'mentoring_request_id' => $requestId,
            'created_by'           => Auth::id(),
            ...$request->only(['titre','description','type','priorite','date_limite','commentaire_mentor']),
        ]);

        return response()->json(['message' => 'Tâche créée', 'data' => $task], 201);
    }

    public function updateTask(Request $request, int $id)
    {
        $task     = AcademicTask::findOrFail($id);
        $mentor   = Mentor::where('user_id', Auth::id())->first();
        $isMentor = $mentor && $task->mentoringRequest->mentor_id === $mentor->id;
        $isStudent= $task->mentoringRequest->student_id === Auth::id();
        if (!$isMentor && !$isStudent) abort(403);

        // Mentor : tout modifier / Étudiant : statut + rendu seulement
        $data = $isMentor
            ? $request->only(['titre','description','type','priorite','statut','date_limite','commentaire_mentor','rendu_etudiant'])
            : $request->only(['statut','rendu_etudiant']);

        if (($data['statut'] ?? null) === 'termine') {
            $data['completed_at'] = now();
        }

        $task->update($data);
        return response()->json(['message' => 'Tâche mise à jour', 'data' => $task]);
    }

    public function deleteTask(int $id)
    {
        $task   = AcademicTask::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($task->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $task->delete();
        return response()->json(['message' => 'Tâche supprimée']);
    }

    // ════════════════════════════════════════════════════════════
    // DIFFICULTÉS
    // ════════════════════════════════════════════════════════════

    public function getDifficulties(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicDifficulty::where('mentoring_request_id', $requestId)->orderByDesc('created_at')->get()
        );
    }

    public function createDifficulty(Request $request, int $requestId)
    {
        // Mentor OU étudiant peut signaler une difficulté
        $this->getSharedRequest($requestId);
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie'   => 'nullable|in:matiere,competence,motivation,organisation,personnel,autre',
            'severite'    => 'nullable|in:faible,moderee,elevee',
        ]);

        $diff = AcademicDifficulty::create([
            'mentoring_request_id' => $requestId,
            ...$request->only(['titre','description','categorie','severite']),
        ]);

        return response()->json(['message' => 'Difficulté enregistrée', 'data' => $diff], 201);
    }

    public function updateDifficulty(Request $request, int $id)
    {
        $diff   = AcademicDifficulty::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->first();
        $isMentor = $mentor && $diff->mentoringRequest->mentor_id === $mentor->id;
        $isStudent = $diff->mentoringRequest->student_id === Auth::id();
        if (!$isMentor && !$isStudent) abort(403);

        $data = $isMentor
            ? $request->only(['titre','description','categorie','severite','statut','recommandations','ressources','plan_action','date_reevaluation','date_resolution'])
            : $request->only(['description','statut']);

        $diff->update($data);
        return response()->json(['message' => 'Mise à jour', 'data' => $diff]);
    }

    public function deleteDifficulty(int $id)
    {
        $diff   = AcademicDifficulty::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($diff->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $diff->delete();
        return response()->json(['message' => 'Supprimé']);
    }

    // ════════════════════════════════════════════════════════════
    // BILANS PÉRIODIQUES
    // ════════════════════════════════════════════════════════════

    public function getReports(int $requestId)
    {
        $this->getSharedRequest($requestId);
        return response()->json(
            AcademicReport::where('mentoring_request_id', $requestId)->orderByDesc('date_fin')->get()
        );
    }

    public function createReport(Request $request, int $requestId)
    {
        $this->getMentorRequest($requestId);
        $request->validate([
            'titre'                    => 'required|string|max:255',
            'periode'                  => 'required|in:mensuel,trimestriel,semestriel,final',
            'date_debut'               => 'required|date',
            'date_fin'                 => 'required|date|after:date_debut',
            'objectifs_atteints'       => 'nullable|string',
            'competences_developpees'  => 'nullable|string',
            'points_ameliorer'         => 'nullable|string',
            'difficultes_persistantes' => 'nullable|string',
            'recommandations'          => 'nullable|string',
            'note_engagement'          => 'nullable|integer|min:1|max:5',
            'note_progression'         => 'nullable|integer|min:1|max:5',
            'statut'                   => 'nullable|in:draft,published',
        ]);

        $report = AcademicReport::create([
            'mentoring_request_id' => $requestId,
            'created_by'           => Auth::id(),
            ...$request->only([
                'titre','periode','date_debut','date_fin',
                'objectifs_atteints','competences_developpees','points_ameliorer',
                'difficultes_persistantes','recommandations',
                'note_engagement','note_progression','statut',
            ]),
            'published_at' => $request->statut === 'published' ? now() : null,
        ]);

        return response()->json(['message' => 'Bilan créé', 'data' => $report], 201);
    }

    public function updateReport(Request $request, int $id)
    {
        $report = AcademicReport::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($report->mentoringRequest->mentor_id !== $mentor->id) abort(403);

        $data = $request->only([
            'titre','periode','date_debut','date_fin',
            'objectifs_atteints','competences_developpees','points_ameliorer',
            'difficultes_persistantes','recommandations',
            'note_engagement','note_progression','statut',
        ]);

        if (($data['statut'] ?? null) === 'published' && !$report->published_at) {
            $data['published_at'] = now();
        }

        $report->update($data);
        return response()->json(['message' => 'Bilan mis à jour', 'data' => $report]);
    }

    public function deleteReport(int $id)
    {
        $report = AcademicReport::findOrFail($id);
        $mentor = Mentor::where('user_id', Auth::id())->firstOrFail();
        if ($report->mentoringRequest->mentor_id !== $mentor->id) abort(403);
        $report->delete();
        return response()->json(['message' => 'Bilan supprimé']);
    }
}