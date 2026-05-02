<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'consent_type', 'consented_at',
        'ip_address', 'user_agent', 'withdrawal_at',
    ];

    protected $casts = [
        'consented_at'  => 'datetime',
        'withdrawal_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
