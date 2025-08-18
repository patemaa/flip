<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pomodoro extends Model
{
    protected $fillable = [
        'type',
        'duration_seconds',
        'started_at',
        'ended_at',
        'status',
        'project_name',
        'priority',
        'tags'
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'tags' => 'array',
    ];
}
