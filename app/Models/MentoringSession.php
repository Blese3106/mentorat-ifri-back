<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MentoringSession extends Model {
    protected $fillable = [
        'request_id',
        'title',
        'scheduled_at',
        'duration_minutes',
        'meet_link',
        'notes',
        'status',
    ];
    protected $casts = [
        'scheduled_at' => 'datetime'
    ];

    public function request() { return $this->belongsTo(MentoringRequest::class, 'request_id'); }
}