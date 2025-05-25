<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug'];
    
    public function articles()
    {
        return $this->belongsToMany(Article::class, 
            'article_category_pivot', 'category_id', 'article_id')
            ->withTimestamps(false, false);
    }
    
    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = \Illuminate\Support\Str::slug($category->name);
        });
    }
}
