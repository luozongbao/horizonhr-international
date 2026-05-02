<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'display_name', 'major', 'education', 'university',
        'languages', 'skills', 'work_experience', 'job_intention', 'status',
    ];

    protected $casts = [
        'languages'       => 'array',
        'skills'          => 'array',
        'work_experience' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
