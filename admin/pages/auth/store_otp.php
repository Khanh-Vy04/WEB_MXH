<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = strtolower(trim($data['email'] ?? ''));
    $otp_code = $data['otp_code'] ?? '';
    $action = $data['action'] ?? '';

    // Lưu OTP vào session
    if ($action === 'store' || $action === '' || !$action) {
        if (empty($email) || empty($otp_code)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu email hoặc mã OTP.']);
            exit;
        }
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_code'] = $otp_code;
        $_SESSION['otp_timestamp'] = time();
        unset($_SESSION['otp_verified']);
        echo json_encode(['success' => true, 'message' => 'OTP đã được lưu vào session.']);
        exit;
    }

    // Xác thực OTP
    if ($action === 'verify') {
        if (empty($email) || empty($otp_code)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu email hoặc mã OTP.']);
            exit;
        }
        if (
            isset($_SESSION['reset_email'], $_SESSION['otp_code'], $_SESSION['otp_timestamp']) &&
            $_SESSION['reset_email'] === $email
        ) {
            // Thống nhất thời gian hết hạn (ví dụ: 15 phút)
            $otp_expiry_time = 900;
            if ((time() - $_SESSION['otp_timestamp']) > $otp_expiry_time) {
                unset($_SESSION['otp_code'], $_SESSION['reset_email'], $_SESSION['otp_timestamp'], $_SESSION['otp_verified']);
                echo json_encode(['success' => false, 'message' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.']);
                exit;
            }
            if ((string)$_SESSION['otp_code'] === (string)$otp_code) {
                $_SESSION['otp_verified'] = true;
                echo json_encode(['success' => true, 'message' => 'Mã OTP hợp lệ.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Mã OTP không đúng. Vui lòng thử lại.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu mã mới.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
} 