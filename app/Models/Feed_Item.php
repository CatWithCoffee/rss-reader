<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed_Item extends Model
{
    protected $fillable = [
        'feed_id',
        'guid',
        'title',
        'description',
        'content',
        'link',
        'published_at',
        'thumbnail',
        'authors',
        'categories',
        'enclosures',
    ];

    protected $casts = [
        'authors' => 'array',
        'categories' => 'array',
        'enclosures' => 'array'
    ];
}
