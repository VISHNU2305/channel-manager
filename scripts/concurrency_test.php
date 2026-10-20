<?php

$url = $argv[1] ?? 'http://localhost/channel-manager/public/api/bookings';
$total = 50;
$run = uniqid();

$mh = curl_multi_init();
$handles = [];

for ($i = 1; $i <= $total; $i++) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'source' => 'stress-test',
            'external_reference' => "{$run}-{$i}",
            'room_type_id' => 2,
            'guest_name' => "Guest {$i}",
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-11',
            'rooms' => 1,
        ]),
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

echo "Firing {$total} simultaneous booking requests...\n";

do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($active && $status === CURLM_OK);

$counts = [];
foreach ($handles as $ch) {
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $counts[$code] = ($counts[$code] ?? 0) + 1;
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);

ksort($counts);
foreach ($counts as $code => $n) {
    echo "HTTP {$code}: {$n}\n";
}