<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MockOtaController;
use Illuminate\Support\Facades\Route;

Route::post('/bookings', [BookingController::class, 'store']);

Route::post('/mock-ota/a', [MockOtaController::class, 'a']);
Route::post('/mock-ota/b', [MockOtaController::class, 'b']);
Route::post('/mock-ota/c', [MockOtaController::class, 'c']);
