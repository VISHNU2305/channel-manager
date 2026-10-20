<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NoAvailabilityException;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request, BookingService $service): JsonResponse
    {
        $data = $request->validate([
            'source' => ['required', 'string', 'max:50'],
            'external_reference' => ['required', 'string', 'max:100'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'rooms' => ['sometimes', 'integer', 'min:1'],
        ]);

        try {
            $booking = $service->create($data);
        } catch (NoAvailabilityException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json($booking, $booking->wasRecentlyCreated ? 201 : 200);
    }
}