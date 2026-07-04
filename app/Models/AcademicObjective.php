<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class AcademicObjective extends Model
{
    protected $fillable = [
        'mentoring_request_id', 'created_by',
        'titre', 'description', 'categorie', 'statut',
        'progression', 'date_cible', 'date_atteint',
        'commentaire_mentor',
    ];
 
    protected $casts = [
        'date_cible'   => 'date:Y-m-d',
        'date_atteint' => 'date:Y-m-d',
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
