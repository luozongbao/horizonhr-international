<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'email',
        'password',
        'status',
        'enterprise_status',
        'prefer_lang',
        'email_verified',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'last_login_at'  => 'datetime',
        'password'       => 'hashed',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function enterprise()
    {
        return $this->hasOne(Enterprise::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function socialAuthentications()
    {
        return $this->hasMany(SocialAuthentication::class);
    }
}
