<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'isFull'
    ];
    protected $hidden = [
        'created_at',  'updated_at'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }
}
