<?php

namespace App\Services\Ota;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class HttpOtaAdapter implements OtaAdapter
{
    abstract protected function path(): string;

    abstract protected function send(PendingRequest $http, int $roomTypeId, array $nights): Response;

    public function push(int $roomTypeId, array $nights): int
    {
        $http = Http::timeout(10)->baseUrl(config('ota.base_url'));
        $response = $this->send($http, $roomTypeId, $nights);

        if (! $response->successful()) {
            throw new OtaException(
                'HTTP '.$response->status().': '.substr($response->body(), 0, 200),
                $response->status()
            );
        }

        return $response->status();
    }
}
