<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện hành động này']);
    exit();
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method không được phép']);
    exit();
}

// Lấy thông tin user hiện tại
$current_user = getCurrentUser();
$user_id = $current_user['user_id'];

try {
    // Lấy và validate dữ liệu từ form
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = 'Họ và tên không được để trống';
    }

    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }

    if (empty($gender) || !in_array($gender, ['Nam', 'Nữ', 'Khác'])) {
        $errors[] = 'Giới tính không hợp lệ';
    }

    if (empty($phone)) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!preg_match('/^[0-9+\-\s\(\)]+$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }

    // Kiểm tra email đã tồn tại (trừ email của user hiện tại)
    $check_email_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_email_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = 'Email này đã được sử dụng bởi tài khoản khác';
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit();
    }

    // Cập nhật thông tin user
    $update_sql = "UPDATE users SET full_name = ?, email = ?, gender = ?, phone = ?, address = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $full_name, $email, $gender, $phone, $address, $user_id);

    if ($stmt->execute()) {
        // Cập nhật session với thông tin mới
        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['gender'] = $gender;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['address'] = $address;

        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật thông tin thành công',
            'data' => [
                'full_name' => $full_name,
                'email' => $email,
                'gender' => $gender,
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật thông tin: ' . $conn->error]);
    }

} catch (Exception $e) {
    error_log("Error updating profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật thông tin']);
}

$conn->close();
?> 