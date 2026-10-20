<?php

namespace App\Services;

use App\Exceptions\NoAvailabilityException;
use App\Models\Booking;
use App\Models\Inventory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Same OTA booking sent twice? Return the original, change nothing.
            $existing = Booking::where('source', $data['source'])
                ->where('external_reference', $data['external_reference'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $rooms = (int) ($data['rooms'] ?? 1);
            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $nights = (int) $checkIn->diffInDays($checkOut);

            // Lock the inventory rows for these nights until the transaction ends.
            // A second request for the same rooms waits here, then sees the new count.
            $rows = Inventory::where('room_type_id', $data['room_type_id'])
                ->where('date', '>=', $checkIn->toDateString())
                ->where('date', '<', $checkOut->toDateString())
                ->orderBy('date')
                ->lockForUpdate()
                ->get();

            if ($rows->count() !== $nights) {
                throw new NoAvailabilityException('No inventory loaded for some of the requested dates.');
            }

            if ($rows->contains(fn ($row) => $row->available_count < $rooms)) {
                throw new NoAvailabilityException('Not enough rooms available for the requested dates.');
            }

            foreach ($rows as $row) {
                $row->decrement('available_count', $rooms);
            }

            return Booking::create([
                'room_type_id' => $data['room_type_id'],
                'source' => $data['source'],
                'external_reference' => $data['external_reference'],
                'guest_name' => $data['guest_name'],
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'rooms' => $rooms,
                'status' => 'confirmed',
            ]);
        });
    }
}