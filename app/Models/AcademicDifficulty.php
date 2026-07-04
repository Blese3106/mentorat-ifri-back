<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicDifficulty extends Model
{
    protected $fillable = [
        'mentoring_request_id',
        'titre', 'description', 'categorie', 'severite', 'statut',
        'recommandations', 'ressources', 'plan_action',
        'date_reevaluation', 'date_resolution',
    ];
 
    protected $casts = [
        'date_reevaluation' => 'date:Y-m-d',
        'date_resolution'   => 'date:Y-m-d',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
}
