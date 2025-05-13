<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedItem extends Model
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

    protected $table = 'feed_items';
    
    public function feed()
    {
        return $this->belongsTo(Feed::class, 'feed_id');
    }

}
