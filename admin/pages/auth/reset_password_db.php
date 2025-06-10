<?php
session_start();
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . "/WEB_MXH/config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = strtolower(trim($data['email'] ?? ''));
    $new_password = $data['new_password'] ?? '';

    // Kiểm tra đầu vào
    if (empty($email) || empty($new_password)) {
        echo json_encode(['success' => false, 'message' => 'Thiếu email hoặc mật khẩu mới.']);
        exit;
    }

    // Kiểm tra xem OTP đã được xác thực cho email này trong session chưa
    if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || $_SESSION['reset_email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'Không được phép đặt lại mật khẩu. Vui lòng bắt đầu lại quá trình.']);
        exit;
    }

    // Hash mật khẩu mới (RẤT QUAN TRỌNG CHO BẢO MẬT)
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        // Xóa tất cả các biến session liên quan đến reset password sau khi thành công
        unset($_SESSION['otp_code']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_timestamp']);
        unset($_SESSION['otp_verified']);
        echo json_encode(['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi đặt lại mật khẩu: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
} 