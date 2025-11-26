<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}