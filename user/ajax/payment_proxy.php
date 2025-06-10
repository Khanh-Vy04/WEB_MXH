<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'No input data']);
    exit;
}

$action = $input['action'] ?? '';

if ($action === 'create_order') {
    // Create payment order
    $amount = $input['amount'] ?? 0;
    $orderInfo = $input['orderInfo'] ?? 'Nạp tiền AuraDisc';
    
    $postData = json_encode([
        'amount' => $amount,
        'orderInfo' => $orderInfo
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://duc-spring.ngodat0103.live/demo/api/app/order');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['error' => 'CURL Error: ' . $error]);
    } else {
        header('HTTP/1.1 ' . $httpCode);
        echo $response;
    }
    
} elseif ($action === 'check_status') {
    // Check payment status
    $orderId = $input['orderId'] ?? '';
    
    if (!$orderId) {
        echo json_encode(['error' => 'Missing orderId']);
        exit;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://duc-spring.ngodat0103.live/demo/api/app/order/' . $orderId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['error' => 'CURL Error: ' . $error]);
    } else {
        header('HTTP/1.1 ' . $httpCode);
        echo $response;
    }
    
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?> 