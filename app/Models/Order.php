<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'url',
        'status',
        'user_id',
        'title',
        'description',
        'favicon',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feed()
    {
        return $this->hasOne(Feed::class, 'order_id');
    }
}
