<?php

namespace App\Services\Ota;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class XmlOtaAdapter extends HttpOtaAdapter
{
    public function channel(): string { return 'ota-b'; }

    protected function path(): string { return '/api/mock-ota/b'; }

    protected function send(PendingRequest $http, int $roomTypeId, array $nights): Response
    {
        $xml = '<Update><Room id="'.$roomTypeId.'">';
        foreach ($nights as $date => $available) {
            $xml .= '<Night date="'.$date.'" available="'.$available.'"/>';
        }
        $xml .= '</Room></Update>';

        return $http->withBody($xml, 'application/xml')->post($this->path());
    }
}
