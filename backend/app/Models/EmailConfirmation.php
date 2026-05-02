<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfirmation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'email', 'token', 'confirmed_at', 'expires_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'expires_at'   => 'datetime',
        'created_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
