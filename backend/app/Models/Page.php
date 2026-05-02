<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title_zh_cn', 'title_en', 'title_th',
        'content_zh_cn', 'content_en', 'content_th',
        'meta_title_zh_cn', 'meta_title_en', 'meta_title_th',
        'meta_desc_zh_cn', 'meta_desc_en', 'meta_desc_th',
        'status', 'type', 'order_num',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
