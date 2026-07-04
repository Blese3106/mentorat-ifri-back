<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringRequest extends Model
{
    protected $fillable = [
        'student_id',
        'mentor_id',
        'subject',
        'message',
        'student_filiere',
        'student_niveau',
        'student_promotion',
        'student_difficulties',
        'student_goals',
        'preferred_date',
        'status',
        'mentor_note',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    // student_id → users.id (l'étudiant est un User)
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // mentor_id → mentors.id (le mentor est un Mentor, pas un User)
    public function mentor()
    {
        return $this->belongsTo(Mentor::class, 'mentor_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'request_id')->orderBy('created_at');
    }

    public function sessions()
    {
        return $this->hasMany(MentoringSession::class, 'request_id')->orderBy('scheduled_at');
    }

    public function academicDiagnostic()
    {
        return $this->hasOne(AcademicDiagnostic::class);
    }
    
    public function academicObjectives()
    {
        return $this->hasMany(AcademicObjective::class);
    }
    
    public function academicPerformances()
    {
        return $this->hasMany(AcademicPerformance::class);
    }
    
    public function academicCompetences()
    {
        return $this->hasMany(AcademicCompetence::class);
    }
    
    public function academicTasks()
    {
        return $this->hasMany(AcademicTask::class);
    }
    
    public function academicDifficulties()
    {
        return $this->hasMany(AcademicDifficulty::class);
    }
    
    public function academicReports()
    {
        return $this->hasMany(AcademicReport::class);
    }
}