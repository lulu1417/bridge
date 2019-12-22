<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'player', 'trump', 'line', 'isPass'
    ];
    protected $hidden = [
        'created_at',  'updated_at'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }

}
