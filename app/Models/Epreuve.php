<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epreuve extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'filiere',
        'year',
        'semester',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'downloads',
        'uploaded_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'downloads' => 'integer',
        'file_size' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads');
    }

    public function getFormattedFileSizeAttribute()
    {
        $size = $this->file_size;
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / (1024 * 1024), 2) . ' MB';
        }
    }
}