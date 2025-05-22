<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorites extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function Article(){
        return $this->belongsTo(Article::class);
    }
}
