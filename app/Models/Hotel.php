<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $fillable = ['name', 'timezone'];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }
}