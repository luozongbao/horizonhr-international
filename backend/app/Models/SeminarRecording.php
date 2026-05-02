<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarRecording extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'seminar_id', 'title', 'video_url', 'thumbnail_url',
        'duration_sec', 'playback_speeds', 'default_speed', 'view_count',
    ];

    protected $casts = [
        'playback_speeds' => 'array',
        'duration_sec'    => 'integer',
        'view_count'      => 'integer',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
