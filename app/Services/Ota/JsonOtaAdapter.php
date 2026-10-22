<?php

namespace App\Services\Ota;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class JsonOtaAdapter extends HttpOtaAdapter
{
    public function channel(): string { return 'ota-a'; }

    protected function path(): string { return '/api/mock-ota/a'; }

    protected function send(PendingRequest $http, int $roomTypeId, array $nights): Response
    {
        $list = [];
        foreach ($nights as $date => $available) {
            $list[] = ['date' => $date, 'available' => $available];
        }

        return $http->acceptJson()->post($this->path(), [
            'room_type_id' => $roomTypeId,
            'nights' => $list,
        ]);
    }
}
