<?php

namespace App\Http\Controllers;

use App\Exceptions\NoAvailabilityException;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\OtaInventory;
use App\Models\RoomType;
use App\Models\SyncLog;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $start = now()->startOfDay();
        $dates = collect(range(0, 13))->map(fn ($i) => $start->copy()->addDays($i));

        $roomTypes = RoomType::with('hotel')->orderBy('id')->get();

        $inventory = Inventory::whereBetween('date', [
                $dates->first()->toDateString(),
                $dates->last()->toDateString(),
            ])
            ->get()
            ->groupBy('room_type_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($r) => $r->date->toDateString()));

        $bookings = Booking::with('roomType')->latest('id')->take(15)->get();

        $stats = [
            'bookings' => Booking::count(),
            'rooms_booked' => (int) Booking::sum('rooms'),
            'sources' => Booking::distinct()->count('source'),
        ];

        $formats = ['ota-a' => 'JSON', 'ota-b' => 'XML', 'ota-c' => 'Rate-limited API'];
        $channels = collect($formats)->map(function ($format, $channel) {
            $count = fn ($status) => SyncLog::where('channel', $channel)->where('status', $status)->count();
            $failed = $count('failed');
            $retrying = $count('retrying') + $count('pending');
            $last = SyncLog::where('channel', $channel)->where('status', 'success')->latest('updated_at')->first();

            return [
                'name' => strtoupper($channel),
                'format' => $format,
                'success' => $count('success'),
                'retrying' => $retrying,
                'failed' => $failed,
                'health' => $failed > 0 ? 'bad' : ($retrying > 0 ? 'warn' : 'good'),
                'label' => $failed > 0 ? 'Needs attention' : ($retrying > 0 ? 'Syncing / retrying' : 'Healthy'),
                'last' => $last?->updated_at->diffForHumans(),
            ];
        })->values();

        $recentSync = SyncLog::latest('id')->take(10)->get();
        $hasPending = SyncLog::whereIn('status', ['pending', 'retrying'])->exists();

        return view('dashboard', compact(
            'dates', 'roomTypes', 'inventory', 'bookings', 'stats', 'channels', 'recentSync', 'hasPending'
        ));
    }

    public function book(Request $request, BookingService $service)
    {
        $data = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'rooms' => ['required', 'integer', 'min:1'],
        ]);

        $data['source'] = 'dashboard';
        $data['external_reference'] = uniqid('DASH-');

        try {
            $booking = $service->create($data);
        } catch (NoAvailabilityException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Booking #{$booking->id} confirmed and inventory updated. Syncing to the other sites...");
    }

    public function reset()
    {
        DB::transaction(function () {
            SyncLog::query()->delete();
            OtaInventory::query()->delete();
            Booking::query()->delete();

            foreach (RoomType::all() as $roomType) {
                Inventory::where('room_type_id', $roomType->id)
                    ->update(['available_count' => $roomType->total_rooms]);
            }
        });

        return back()->with('success', 'Demo reset: all bookings and sync history cleared, inventory restored.');
    }
}
