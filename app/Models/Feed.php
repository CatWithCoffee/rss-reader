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
        'update_frequency',
        'last_modified',
    ];

    public function items()
    {
        return $this->hasMany(Feed_Item::class);
    }
}
