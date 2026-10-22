<?php

namespace App\Services\Ota;

class OtaRegistry
{
    /** @return array<string,OtaAdapter> */
    public static function all(): array
    {
        $adapters = [new JsonOtaAdapter(), new XmlOtaAdapter(), new RateLimitedOtaAdapter()];

        $map = [];
        foreach ($adapters as $adapter) {
            $map[$adapter->channel()] = $adapter;
        }
        return $map;
    }

    public static function for(string $channel): OtaAdapter
    {
        return self::all()[$channel];
    }
}
