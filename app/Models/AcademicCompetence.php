<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicCompetence extends Model
{
    protected $fillable = [
        'mentoring_request_id',
        'nom', 'type',
        'niveau_initial', 'niveau_actuel', 'niveau_cible',
        'commentaire_mentor', 'date_evaluation',
    ];
 
    protected $casts = [
        'date_evaluation' => 'date:Y-m-d',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
 
    // Calcule le % de progression vers le niveau cible
    public function getProgressionPourcentageAttribute(): int
    {
        if ($this->niveau_cible > 0) {
            return min(100, round(($this->niveau_actuel / $this->niveau_cible) * 100));
        }
        return 0;
    }
}
