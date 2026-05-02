<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'native_name', 'flag', 'description', 'is_active', 'position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
