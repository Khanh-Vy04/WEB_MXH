<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Chỉ chấp nhận POST request']);
    exit();
}

// Lấy input JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch($action) {
    case 'create_order':
        createVNPayOrder($input);
        break;
    case 'check_status':
        checkVNPayStatus($input);
        break;
    default:
        echo json_encode(['error' => 'Action không hợp lệ']);
}

function createVNPayOrder($input) {
    $amount = intval($input['amount'] ?? 0);
    $orderInfo = $input['orderInfo'] ?? 'Thanh toán đơn hàng';
    
    if ($amount <= 0) {
        echo json_encode(['error' => 'Số tiền không hợp lệ']);
        return;
    }
    
    $postData = json_encode([
        'amount' => $amount,
        'orderInfo' => $orderInfo
    ]);
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'https://duc-spring.ngodat0103.live/demo/api/app/order');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: AuraDisc/1.0'
    ]);
    
    // Disable SSL verification for development (remove in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['error' => 'Lỗi kết nối: ' . $error]);
        return;
    }
    
    if ($httpCode !== 200) {
        echo json_encode(['error' => 'API trả về lỗi: HTTP ' . $httpCode]);
        return;
    }
    
    $responseData = json_decode($response, true);
    
    if ($responseData === null) {
        echo json_encode(['error' => 'Phản hồi API không hợp lệ']);
        return;
    }
    
    echo json_encode($responseData);
}

function checkVNPayStatus($input) {
    $orderId = $input['orderId'] ?? '';
    
    if (empty($orderId)) {
        echo json_encode(['error' => 'Order ID không hợp lệ']);
        return;
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'https://duc-spring.ngodat0103.live/demo/api/app/order/' . urlencode($orderId));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: AuraDisc/1.0'
    ]);
    
    // Disable SSL verification for development (remove in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode(['error' => 'Lỗi kết nối: ' . $error]);
        return;
    }
    
    if ($httpCode !== 200) {
        echo json_encode(['error' => 'API trả về lỗi: HTTP ' . $httpCode]);
        return;
    }
    
    $responseData = json_decode($response, true);
    
    if ($responseData === null) {
        echo json_encode(['error' => 'Phản hồi API không hợp lệ']);
        return;
    }
    
    echo json_encode($responseData);
}
?> 