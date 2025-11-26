<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id',
        'group_id',
        'room_id',
        'date',
        'image',
        'details',
    ];

    protected $casts = [
        'date' => 'date',
        'details' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}