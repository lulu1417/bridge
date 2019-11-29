<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Compare extends Model
{
    protected $fillable = [
        'name', 'color', 'card', 'round', 'priority'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }

}
