<?php
// Reset Password Page

require_once '../config/database.php';

$message = '';
$message_type = '';
$valid_token = false;
$user_info = null;

// Kiểm tra token từ URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Token không hợp lệ!';
    $message_type = 'error';
} else {
    // Kiểm tra token trong database
    $stmt = $conn->prepare("
        SELECT pr.user_id, pr.expires_at, u.username, u.email, u.full_name 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.user_id 
        WHERE pr.reset_token = ? AND pr.expires_at > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user_info = $result->fetch_assoc();
        $valid_token = true;
    } else {
        $message = 'Token không hợp lệ hoặc đã hết hạn!';
        $message_type = 'error';
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password)) {
        $message = 'Vui lòng nhập mật khẩu mới!';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Mật khẩu xác nhận không khớp!';
        $message_type = 'error';
    } else {
        // Hash mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Cập nhật mật khẩu trong database
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_info['user_id']);
        
        if ($update_stmt->execute()) {
            // Xóa token đã sử dụng
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE reset_token = ?");
            $delete_stmt->bind_param("s", $token);
            $delete_stmt->execute();
            
            $message = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.';
            $message_type = 'success';
            $valid_token = false; // Ẩn form sau khi thành công
        } else {
            $message = 'Có lỗi xảy ra khi cập nhật mật khẩu!';
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
    
    <title>Đặt lại mật khẩu - AuraDisc</title>
    
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
        
        .reset-password-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 90%;
            margin: 20px;
        }
        
        .reset-password-header {
            background: #412d3b;
            padding: 40px 30px 30px;
            text-align: center;
            color: #deccca;
        }
        
        .reset-password-header h2 {
            margin: 0 0 10px;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .reset-password-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .user-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .reset-password-form {
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
        
        .icon-lock {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .password-strength {
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .strength-indicator {
            height: 5px;
            background: #e1e1e1;
            border-radius: 3px;
            margin: 5px 0;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .strength-weak { width: 33%; background: #dc3545; }
        .strength-medium { width: 66%; background: #ffc107; }
        .strength-strong { width: 100%; background: #28a745; }
        
        .success-container {
            text-align: center;
            padding: 40px 30px;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .btn-login {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-login:hover {
            background: #218838;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .reset-password-container {
                margin: 10px;
                width: 95%;
            }
            
            .reset-password-header,
            .reset-password-form,
            .success-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="reset-password-container">
        <?php if ($valid_token): ?>
            <div class="reset-password-header">
                <div class="icon-lock">
                    <i class="fa fa-lock"></i>
                </div>
                <h2>Đặt lại mật khẩu</h2>
                <p>Nhập mật khẩu mới cho tài khoản của bạn</p>
                
                <?php if ($user_info): ?>
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($user_info['full_name']); ?></strong>
                        <small><?php echo htmlspecialchars($user_info['email']); ?></small>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="reset-password-form">
                <?php if (!empty($message)): ?>
                    <div class="message message-<?php echo $message_type; ?>">
                        <i class="fa <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="resetForm">
                    <div class="form-group">
                        <label for="password">
                            <i class="fa fa-lock"></i> Mật khẩu mới
                        </label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Nhập mật khẩu mới..." 
                               required minlength="6" onkeyup="checkPasswordStrength()">
                        <div class="password-strength">
                            <div class="strength-indicator">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <small id="strengthText">Mật khẩu ít nhất 6 ký tự</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fa fa-lock"></i> Xác nhận mật khẩu
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Nhập lại mật khẩu mới..." 
                               required onkeyup="checkPasswordMatch()">
                        <small id="matchText"></small>
                    </div>
                    
                    <button type="submit" class="btn-reset" id="submitBtn">
                        <i class="fa fa-save"></i> Cập nhật mật khẩu
                    </button>
                </form>
                
                <div class="back-to-login">
                                    <a href="../index.php">
                    <i class="fa fa-arrow-left"></i> Quay lại đăng nhập
                </a>
                </div>
            </div>
        <?php elseif ($message_type === 'success'): ?>
            <div class="success-container">
                <div class="success-icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h3>Thành công!</h3>
                <p><?php echo $message; ?></p>
                <a href="../index.php" class="btn-login">
                    <i class="fa fa-sign-in"></i> Đăng nhập ngay
                </a>
            </div>
        <?php else: ?>
            <div class="reset-password-header">
                <div class="icon-lock">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <h2>Lỗi xác thực</h2>
                <p><?php echo $message; ?></p>
            </div>
            
            <div class="reset-password-form">
                <div class="back-to-login">
                    <a href="forgot-password.php">
                        <i class="fa fa-arrow-left"></i> Quay lại trang quên mật khẩu
                    </a>
                    <br><br>
                    <a href="../index.php">
                        <i class="fa fa-sign-in"></i> Đăng nhập
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'strength-bar';
            
            if (password.length === 0) {
                text = 'Mật khẩu ít nhất 6 ký tự';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                text = 'Mật khẩu yếu';
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
                text = 'Mật khẩu trung bình';
            } else {
                strengthBar.classList.add('strength-strong');
                text = 'Mật khẩu mạnh';
            }
            
            strengthText.textContent = text;
            checkPasswordMatch();
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('matchText');
            const submitBtn = document.getElementById('submitBtn');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (password === confirmPassword) {
                matchText.textContent = '✅ Mật khẩu khớp';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = '❌ Mật khẩu không khớp';
                matchText.style.color = '#dc3545';
            }
            
            // Enable/disable submit button
            submitBtn.disabled = password.length < 6 || password !== confirmPassword;
            submitBtn.style.opacity = submitBtn.disabled ? '0.6' : '1';
        }
        
        // Form validation on submit
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return;
            }
        });
    </script>
</body>
</html>