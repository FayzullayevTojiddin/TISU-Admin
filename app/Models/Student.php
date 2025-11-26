<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'full_name',
        'group_id',
        'status',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'status' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}