<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MessageCollection;

class Template extends Model
{
    protected $fillable = [
        'title',
        'message',
        'variables',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function messageCollections()
    {
        return $this->hasMany(MessageCollection::class);
    }
}