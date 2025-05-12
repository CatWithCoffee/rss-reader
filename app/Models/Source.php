<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = ['title', 'link', 'description', 'image', 'favicon', 'language', 'category'];
}
