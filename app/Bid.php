<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'player', 'trump', 'line', 'isPass'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }

}
