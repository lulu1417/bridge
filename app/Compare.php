<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Compare extends Model
{
    protected $fillable = [
        'name', 'color', 'card', 'round', 'priority'
    ];
    protected $hidden = [
        'created_at',  'updated_at'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class, 'name', 'name');
    }

}
