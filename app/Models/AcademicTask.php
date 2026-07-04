<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicTask extends Model
{
    protected $fillable = [
        'mentoring_request_id', 'created_by',
        'titre', 'description', 'type', 'priorite', 'statut',
        'date_limite', 'commentaire_mentor', 'rendu_etudiant',
        'completed_at',
    ];
 
    protected $casts = [
        'date_limite'  => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
 
    // Vérifie si la tâche est en retard
    public function getIsLateAttribute(): bool
    {
        return $this->date_limite
            && $this->statut !== 'termine'
            && $this->date_limite->isPast();
    }
}
