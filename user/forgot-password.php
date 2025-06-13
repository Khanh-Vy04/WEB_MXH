<?php
// Forgot Password Page

require_once '../config/database.php';

// Xử lý form submit
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Vui lòng nhập email!';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ!';
        $message_type = 'error';
    } else {
        // Kiểm tra email có tồn tại không
        $stmt = $conn->prepare("SELECT user_id, username, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Tạo reset token
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+3 hours')); // Token hết hạn sau 3 giờ
            
            // Xóa token cũ nếu có
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user['user_id']);
            $delete_stmt->execute();
            
            // Thêm token mới
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, reset_token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iss", $user['user_id'], $reset_token, $expires_at);
            
            if ($insert_stmt->execute()) {
                $reset_link = "http://localhost/WEB_MXH/user/reset-password.php?token=" . $reset_token;
                $message = 'Link đặt lại mật khẩu đã được tạo thành công! (Có hiệu lực trong 3 giờ)';
                $message_type = 'success';
                
                // Trong localhost, hiển thị link trực tiếp
                $reset_link_display = $reset_link;
            } else {
                $message = 'Có lỗi xảy ra khi tạo link đặt lại mật khẩu!';
                $message_type = 'error';
            }
        } else {
            $message = 'Không tìm thấy tài khoản với email này!';
            $message_type = 'error';
        }
    }
}
?>

<!doctype html>
<html class="no-js" lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Quên mật khẩu - AuraDisc</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        
        .forgot-password-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 90%;
            margin: 20px;
        }
        
        .forgot-password-header {
            background: #412d3b;
            padding: 40px 30px 30px;
            text-align: center;
            color: #deccca;
        }
        
        .forgot-password-header h2 {
            margin: 0 0 10px;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .forgot-password-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .forgot-password-form {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }
        
        .btn-reset {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .reset-link-container {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            text-align: center;
        }
        
        .reset-link-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .reset-link {
            display: block;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #495057;
            text-decoration: none;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .reset-link:hover {
            background: #e9ecef;
            text-decoration: none;
            color: #495057;
        }
        
        .btn-copy-link {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-copy-link:hover {
            background: #218838;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e1e1e1;
        }
        
        .back-to-login a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #e55a2b;
            text-decoration: none;
        }
        
        .icon-email {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        @media (max-width: 480px) {
            .forgot-password-container {
                margin: 10px;
                width: 95%;
            }
            
            .forgot-password-header,
            .forgot-password-form {
                padding: 30px 20px;
            }
            
            .reset-link {
                font-size: 0.8rem;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-password-container">
        <div class="forgot-password-header">
            <div class="icon-email">
                <i class="fa fa-envelope"></i>
            </div>
            <h2>Quên mật khẩu?</h2>
            <p>Nhập email để nhận link đặt lại mật khẩu</p>
        </div>
        
        <div class="forgot-password-form">
            <?php if (!empty($message)): ?>
                <div class="message message-<?php echo $message_type; ?>">
                    <i class="fa <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($reset_link_display)): ?>
                <div class="reset-link-container">
                    <div class="reset-link-title">
                        <i class="fa fa-link"></i> Link đặt lại mật khẩu (Hiệu lực 3 giờ từ <?php echo date('H:i'); ?>):
                    </div>
                    <a href="<?php echo $reset_link_display; ?>" class="reset-link" id="resetLink">
                        <?php echo $reset_link_display; ?>
                    </a>
                    <button type="button" class="btn-copy-link" onclick="copyResetLink()">
                        <i class="fa fa-copy"></i> Copy link
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">
                        <i class="fa fa-envelope"></i> Email đăng ký
                    </label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Nhập email của bạn..." 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
                
                <button type="submit" class="btn-reset">
                    <i class="fa fa-paper-plane"></i> Gửi link đặt lại
                </button>
            </form>
            
            <div class="back-to-login">
                <a href="../index.php">
                    <i class="fa fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyResetLink() {
            const linkElement = document.getElementById('resetLink');
            const link = linkElement.textContent;
            
            navigator.clipboard.writeText(link).then(function() {
                alert('✅ Đã copy link vào clipboard!');
            }).catch(function() {
                // Fallback cho trình duyệt cũ
                const textArea = document.createElement('textarea');
                textArea.value = link;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('✅ Đã copy link vào clipboard!');
            });
        }
        
        // Auto redirect sau khi copy (optional)
        function autoRedirect() {
            const resetLink = document.getElementById('resetLink');
            if (resetLink) {
                setTimeout(function() {
                    if (confirm('Bạn có muốn tự động chuyển đến trang đặt lại mật khẩu không?')) {
                        window.location.href = resetLink.href;
                    }
                }, 3000);
            }
        }
        
        // Call auto redirect if reset link exists
        if (document.getElementById('resetLink')) {
            autoRedirect();
        }
    </script>
</body>
</html> 