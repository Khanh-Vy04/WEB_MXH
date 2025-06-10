<?php
include $_SERVER['DOCUMENT_ROOT'] . "/WEB_MXH/config/database.php";

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$email = strtolower(trim($data['email'] ?? ''));

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu email']);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy username nào trong hệ thống với email này.']);
    exit;
}

$user = $result->fetch_assoc();
echo json_encode([
    'success' => true, 
    'username' => $user['username'],
    'message' => 'Đã tìm thấy username: ' . $user['username'] . ' ứng với email ' . $email
]);
exit; 