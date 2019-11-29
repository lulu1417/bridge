<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'name', 'color', 'card'
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }
}
