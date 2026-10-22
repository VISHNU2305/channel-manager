<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtaInventory extends Model
{
    protected $fillable = ['ota', 'room_type_id', 'date', 'available_count'];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d'];
    }
}