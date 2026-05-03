<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'nationality', 'phone', 'avatar', 'avatar_key',
        'birth_date', 'gender', 'address', 'bio', 'verified', 'prefer_lang',
    ];

    protected $casts = [
        'verified'   => 'boolean',
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resume()
    {
        return $this->hasMany(Resume::class);
    }

    public function talentCard()
    {
        return $this->hasOne(TalentCard::class);
    }
}
