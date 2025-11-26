<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MessageCollection;

class Message extends Model
{
    protected $fillable = [
        'message_collection_id',
        'details',
        'phone',
        'status',
        'send_at',
    ];

    protected $casts = [
        'details' => 'array',
        'send_at' => 'datetime',
    ];

    public function messageCollection()
    {
        return $this->belongsTo(MessageCollection::class);
    }
}