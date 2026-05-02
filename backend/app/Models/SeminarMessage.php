<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'seminar_id', 'user_id', 'user_name', 'content',
        'color', 'position', 'font_size', 'send_at',
    ];

    protected $casts = [
        'send_at'   => 'datetime',
        'font_size' => 'integer',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
