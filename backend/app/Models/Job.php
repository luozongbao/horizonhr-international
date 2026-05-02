<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id', 'title', 'description', 'requirements', 'location',
        'salary_min', 'salary_max', 'salary_currency', 'job_type', 'status',
        'published_at', 'expires_at', 'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        'salary_min'   => 'integer',
        'salary_max'   => 'integer',
        'view_count'   => 'integer',
    ];

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
