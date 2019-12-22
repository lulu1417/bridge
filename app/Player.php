<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'name', 'password', 'trick' ,'goal',
    ];
    protected $hidden = [
        'password', 'created_at',  'updated_at'
    ];
}
