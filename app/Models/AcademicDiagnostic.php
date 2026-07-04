<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class AcademicDiagnostic extends Model
{
    protected $fillable = [
        'mentoring_request_id',
        'filiere', 'niveau', 'annee_promotion',
        'competences_acquises', 'competences_a_developper',
        'soft_skills', 'objectifs_personnels',
        'difficultes_initiales', 'observations', 'statut',
    ];
 
    protected $casts = [
        'competences_acquises'     => 'array',
        'competences_a_developper' => 'array',
        'soft_skills'              => 'array',
    ];
 
    public function mentoringRequest()
    {
        return $this->belongsTo(MentoringRequest::class);
    }
}