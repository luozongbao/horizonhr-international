<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    const CREATED_AT = 'applied_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'job_id', 'student_id', 'resume_id', 'cover_letter', 'status', 'notes',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
