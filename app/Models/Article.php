<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
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
        'published_at' => 'datetime',
        'categories' => 'array',
        'enclosures' => 'array'
    ];

    protected $table = 'articles';
    
    public function feed()
    {
        return $this->belongsTo(Feed::class, 'feed_id');
    }

    public function favorited_by(){
        return $this->belongsToMany(User::class, 'favorites', 'article_id', 'user_id');
    }
}
