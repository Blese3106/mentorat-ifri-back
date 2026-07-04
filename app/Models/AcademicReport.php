<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicReport extends Model
{
    protected $fillable = [
        'mentoring_request_id', 'created_by',
        'titre', 'periode', 'date_debut', 'date_fin',
        'objectifs_atteints', 'competences_developpees',
        'points_ameliorer', 'difficultes_persistantes', 'recommandations',
        'note_engagement', 'note_progression', 'statut', 'published_at',
    ];
 
    protected $casts = [
        'date_debut'   => 'date:Y-m-d',
        'date_fin'     => 'date:Y-m-d',
        'published_at' => 'datetime',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
