<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $fillable = [
        'status',
        'room_id',
        'name',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}