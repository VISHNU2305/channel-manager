<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = ['booking_id', 'channel', 'status', 'attempts', 'response_code', 'message'];
}