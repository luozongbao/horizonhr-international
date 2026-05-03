<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id', 'enterprise_id', 'student_id', 'job_id', 'title',
        'scheduled_at', 'duration', 'room_id', 'trtc_room_id', 'room_token', 'status', 'reminder_sent',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'reminder_sent' => 'boolean',
        'duration'      => 'integer',
        'trtc_room_id'  => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function records()
    {
        return $this->hasMany(InterviewRecord::class);
    }
}
