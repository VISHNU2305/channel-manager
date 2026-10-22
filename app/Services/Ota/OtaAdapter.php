<?php

namespace App\Services\Ota;

interface OtaAdapter
{
    public function channel(): string;

    /** @param array<string,int> $nights  date => rooms available. Returns the HTTP status, throws OtaException on failure. */
    public function push(int $roomTypeId, array $nights): int;
}
