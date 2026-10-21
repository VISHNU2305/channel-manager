<?php

namespace App\Http\Controllers;

use App\Exceptions\NoAvailabilityException;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\RoomType;
use App\Services\BookingService;
use Illuminate\Http\Request;

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

        return view('dashboard', compact('dates', 'roomTypes', 'inventory', 'bookings', 'stats'));
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

        return back()->with('success', "Booking #{$booking->id} confirmed and inventory updated.");
    }
}