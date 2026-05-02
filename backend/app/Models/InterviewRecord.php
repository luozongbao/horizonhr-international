<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewRecord extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'interview_id', 'participant_id', 'participant_type', 'joined_at',
        'left_at', 'duration_sec', 'connection_quality', 'notes',
        'result', 'rating', 'recording_url',
    ];

    protected $casts = [
        'joined_at'    => 'datetime',
        'left_at'      => 'datetime',
        'duration_sec' => 'integer',
        'rating'       => 'integer',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }
}
