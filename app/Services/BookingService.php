<?php

namespace App\Services;

use App\Exceptions\NoAvailabilityException;
use App\Jobs\SyncAvailabilityJob;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\SyncLog;
use App\Services\Ota\OtaRegistry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {
        $booking = DB::transaction(function () use ($data) {
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

        if ($booking->wasRecentlyCreated) {
            $this->queueSync($booking);
        }

        return $booking;
    }

    private function queueSync(Booking $booking): void
    {
        $nights = Inventory::where('room_type_id', $booking->room_type_id)
            ->where('date', '>=', $booking->check_in->toDateString())
            ->where('date', '<', $booking->check_out->toDateString())
            ->get()
            ->mapWithKeys(fn ($row) => [$row->date->toDateString() => $row->available_count])
            ->all();

        foreach (OtaRegistry::all() as $channel => $adapter) {
            // Don't tell a site about a booking it sent us itself.
            if ($channel === $booking->source) {
                continue;
            }

            $log = SyncLog::create([
                'booking_id' => $booking->id,
                'channel' => $channel,
                'status' => 'pending',
            ]);

            SyncAvailabilityJob::dispatch($log->id, $booking->room_type_id, $nights);
        }
    }
}
