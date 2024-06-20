<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    protected $casts = [
        'members' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
