<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatePlan extends Model
{
    protected $fillable = ['room_type_id', 'name', 'base_price'];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}