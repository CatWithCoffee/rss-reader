<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $fillable = [
        'title',
        'url',
        'site_url',
        'description',
        'language',
        'category',
        'favicon',
        'image',
        'color',
        'is_active',
        'etag',
        'content_hash',
        'update_frequency',
        'last_modified',
    ];

    protected $casts = [
        'last_modified' => 'datetime'
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
