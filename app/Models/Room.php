<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'fakultet',
        'status',
    ];

    public function lessons()
    {
        return $this->hasMany(Room::class);
    }
}
