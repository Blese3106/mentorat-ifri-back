<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestMessage extends Model
{
    protected $fillable = [
        'request_id',
        'sender_id',
        'sender_type',
        'content',
    ];

    public function request()
    {
        return $this->belongsTo(MentoringRequest::class, 'request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}