<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarRegistration extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'seminar_id', 'email', 'name', 'phone', 'user_id',
        'reminder_sent', 'reminder_sent_at',
    ];

    protected $casts = [
        'reminder_sent'    => 'boolean',
        'reminder_sent_at' => 'datetime',
        'registered_at'    => 'datetime',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
