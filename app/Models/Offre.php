<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $fillable = [
        'title',
        'company',
        'location',
        'type',
        'duration',
        'filiere',
        'description',
        'requirements',
        'created_by',
    ];

    protected $casts = [
        'requirements' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}