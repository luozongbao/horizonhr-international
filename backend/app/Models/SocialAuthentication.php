<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAuthentication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider', 'provider_id', 'provider_email',
        'provider_name', 'provider_avatar',
        'access_token', 'refresh_token', 'token_expires_at',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
