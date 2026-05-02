<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_zh_cn', 'name_en', 'name_th',
        'logo', 'cover_image', 'location', 'location_city', 'location_region',
        'website', 'description', 'majors', 'program_types',
        'established_year', 'ranking',
    ];

    protected $casts = [
        'majors'        => 'array',
        'program_types' => 'array',
    ];
}
