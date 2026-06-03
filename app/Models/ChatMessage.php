<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model {
    protected $fillable = [
        'request_id',
        'sender_id',
        'sender_type',
        'content',
        'read'
    ];
    protected $casts    = [
        'read' => 'boolean'
    ];

    public function sender()  { return $this->belongsTo(User::class, 'sender_id'); }
    public function request() { return $this->belongsTo(MentoringRequest::class, 'request_id'); }
}