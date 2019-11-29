<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'isFull'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }
}
