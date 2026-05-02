<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_zh_cn', 'title_en', 'title_th',
        'desc_zh_cn', 'desc_en', 'desc_th',
        'speaker_name', 'speaker_title', 'speaker_bio', 'speaker_avatar',
        'thumbnail', 'stream_url', 'stream_key',
        'target_audience', 'status', 'permission',
        'max_viewers', 'current_viewers', 'max_concurrent_viewers',
        'starts_at', 'duration_min', 'ended_at',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'ended_at'    => 'datetime',
        'duration_min'=> 'integer',
    ];

    public function registrations()
    {
        return $this->hasMany(SeminarRegistration::class);
    }

    public function recording()
    {
        return $this->hasOne(SeminarRecording::class);
    }

    public function messages()
    {
        return $this->hasMany(SeminarMessage::class);
    }
}
