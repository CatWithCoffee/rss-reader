<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorites extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function feedItem(){
        return $this->belongsTo(FeedItem::class);
    }
}
