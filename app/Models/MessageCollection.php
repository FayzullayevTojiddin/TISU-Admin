<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Template;
use App\Models\Message;

class MessageCollection extends Model
{
    protected $fillable = [
        'title',
        'template_id',
        'success_count',
        'failed_count',
        'description',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}