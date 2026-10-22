<?php

namespace App\Services\Ota;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class RateLimitedOtaAdapter extends HttpOtaAdapter
{
    public function channel(): string { return 'ota-c'; }

    protected function path(): string { return '/api/mock-ota/c'; }

    protected function send(PendingRequest $http, int $roomTypeId, array $nights): Response
    {
        return $http->acceptJson()->post($this->path(), [
            'room' => $roomTypeId,
            'calendar' => $nights,
        ]);
    }
}
