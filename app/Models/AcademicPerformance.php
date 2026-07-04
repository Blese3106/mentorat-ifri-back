<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPerformance extends Model
{
    protected $fillable = [
        'mentoring_request_id',
        'matiere', 'note', 'note_precedente', 'moyenne_generale',
        'semestre', 'annee_academique', 'ue_validee',
        'niveau_maitrise', 'commentaire',
    ];
 
    protected $casts = [
        'note'             => 'float',
        'note_precedente'  => 'float',
        'moyenne_generale' => 'float',
        'ue_validee'       => 'boolean',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
 
    // Calcule la progression entre l'ancienne et la nouvelle note
    public function getProgressionAttribute(): ?float
    {
        if ($this->note !== null && $this->note_precedente !== null) {
            return round($this->note - $this->note_precedente, 2);
        }
        return null;
    }
}
