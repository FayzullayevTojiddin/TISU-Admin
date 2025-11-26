<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'lesson_id',
        'came',
    ];

    protected $casts = [
        'came' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}