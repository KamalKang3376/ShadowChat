<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participants extends Model
{
    protected $fillable = [
        'room_id',
        'session_id',
        'nicname',
        'joined_at',
        'last_seen_at',
        'left_at',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
        
    }
}
