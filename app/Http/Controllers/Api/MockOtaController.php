<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtaInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class MockOtaController extends Controller
{
    // OTA-A: JSON
    public function a(Request $request)
    {
        foreach ($request->input('nights', []) as $night) {
            $this->store('ota-a', $request->input('room_type_id'), $night['date'], $night['available']);
        }
        return response()->json(['status' => 'ok']);
    }

    // OTA-B: XML
    public function b(Request $request)
    {
        $xml = @simplexml_load_string($request->getContent());
        if ($xml === false) {
            return response('<Error>Invalid XML</Error>', 400)->header('Content-Type', 'application/xml');
        }
        $roomId = (int) $xml->Room['id'];
        foreach ($xml->Room->Night as $night) {
            $this->store('ota-b', $roomId, (string) $night['date'], (int) $night['available']);
        }
        return response('<Result>OK</Result>', 200)->header('Content-Type', 'application/xml');
    }

    // OTA-C: rate limited (5 requests a minute) and randomly unavailable
    public function c(Request $request)
    {
        $key = 'mock-ota-c';
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'rate limit exceeded'], 429);
        }
        RateLimiter::hit($key, 60);

        if (random_int(1, 100) <= 20) {
            return response()->json(['error' => 'temporarily unavailable'], 503);
        }

        foreach ($request->input('calendar', []) as $date => $available) {
            $this->store('ota-c', $request->input('room'), $date, $available);
        }
        return response()->json(['accepted' => true]);
    }

    private function store(string $ota, $roomTypeId, string $date, $available): void
    {
        OtaInventory::updateOrCreate(
            ['ota' => $ota, 'room_type_id' => $roomTypeId, 'date' => $date],
            ['available_count' => $available]
        );
    }
}
