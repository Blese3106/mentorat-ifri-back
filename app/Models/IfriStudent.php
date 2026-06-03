<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IfriStudent extends Model
{
    protected $table = 'ifristudents';

    protected $fillable = [
        'identifier',
        'name',
        'filiere',
        'promotion',
        'is_registered',
    ];

}
