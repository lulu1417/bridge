<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'name', 'color', 'card'
    ];
    protected $hidden = [
        'created_at',  'updated_at'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }
}
