<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Mentor extends Model
{
    use Notifiable;

    public function routeNotificationForMail(): string {
        return $this->email;
    }

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'oldstudent_ifri',
        'promotion',
        'filiere',
        'role',
        'company',
        'poste',
        'email_contact',
        'experience',
        'price',
        'bio',
        'type',
        'linkedin',
        'portfolio',
        'status',
        'expertise',
        'path_cv',
        'diplome',
    ];

    protected $casts = [
        'expertise'       => 'array',
        'oldstudent_ifri' => 'boolean',
        'experience'      => 'integer',
        'price'           => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentoringRequests()
    {
        return $this->hasMany(MentoringRequest::class, 'mentor_id');
    }

    public function pendingRequests()
    {
        return $this->hasMany(MentoringRequest::class, 'mentor_id')->where('status', 'pending');
    }

    public function acceptedStudents()
    {
        return $this->hasMany(MentoringRequest::class, 'mentor_id')->where('status', 'accepted');
    }

    public function getFullNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    
}