<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình session trước khi khởi động session
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

// Khởi động session sau khi đã cấu hình
session_start();

// Kiểm tra kết nối database
require_once 'config/database.php';
require_once 'includes/session.php';

if (!isset($conn) || $conn->connect_error) {
    die("Lỗi kết nối database: " . $conn->connect_error);
}

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $login_error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Truy vấn user từ bảng users
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                // Lưu session trước khi redirect
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = ($user['role_id'] == 1) ? 'admin' : 'user';
                $_SESSION['login_time'] = time();
                
                // Lưu thêm thông tin người dùng vào session
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['gender'] = $user['gender'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['address'] = $user['address'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['created_at'] = $user['created_at'];
                
                // Chuyển hướng dựa vào role_id
                if ($user['role_id'] == 1) {
                    header("Location: admin/pages/dashboard/dashboard.php");
                    exit();
                } else {
                    header("Location: user/index.php");
                    exit();
                }
            } else {
                $login_error = "Sai mật khẩu!";
            }
        } else {
            $login_error = "Tài khoản không tồn tại!";
        }
    }
}

// Xử lý đăng ký
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $password = trim($_POST['reg_password']);
    $email = trim($_POST['reg_email']);
    $fullname = trim($_POST['reg_fullname']);
    $gender = $_POST['reg_gender'];
    $phone = trim($_POST['reg_phone']);
    $address = trim($_POST['reg_address']);
    
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($fullname) || empty($gender) || empty($phone) || empty($address)) {
        $register_error = "Vui lòng điền đầy đủ thông tin!";
    } 
    // Validate số điện thoại Việt Nam
    elseif (!preg_match('/^(0|\+84)[0-9]{9}$/', $phone)) {
        $register_error = "Số điện thoại không hợp lệ! Vui lòng nhập số điện thoại Việt Nam (10 số bắt đầu bằng 0 hoặc +84 theo sau 9 số).";
    } else {
        try {
            // Kiểm tra username và email đã tồn tại chưa
            $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Lỗi prepare statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("ss", $username, $email);
            if (!$check_stmt->execute()) {
                throw new Exception("Lỗi execute statement: " . $check_stmt->error);
            }
            
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $register_error = "Username hoặc email đã tồn tại!";
            } else {
                // Thêm user mới với role_id = 2 (user thường)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (role_id, username, password, email, full_name, gender, phone, address) 
                        VALUES (2, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                
                $stmt->bind_param("sssssss", $username, $hashed_password, $email, $fullname, $gender, $phone, $address);
                
                if ($stmt->execute()) {
                    $register_success = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    throw new Exception("Lỗi thêm user: " . $stmt->error);
                }
            }
        } catch (Exception $e) {
            $register_error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraDisc - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #deccca;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            background: #f3eeeb;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            margin: 20px;
        }
        .auth-row {
            display: flex;
            min-height: 600px;
        }
        .auth-side {
            flex: 1;
            padding: 40px;
        }
        .auth-divider {
            width: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-auth {
            background: #deccca;
            border: none;
            border-radius: 10px;
            color: #412d3b;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn-auth:hover {
            background: #412d3b;
            color: #deccca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }
        .auth-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .text-center a {
            text-decoration: none;
            color: #412d3b;
            font-weight: 600;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .auth-row {
                flex-direction: column;
            }
            .auth-divider {
                width: 100%;
                height: 1px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-row">
            <!-- Đăng nhập -->
            <div class="auth-side">
                <h2 class="auth-title text-center">Đăng nhập</h2>
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST" autocomplete="on" id="loginForm">
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               id="login_username"
                               autocomplete="username" 
                               placeholder="Tên đăng nhập" 
                               required>
                    </div>
                    <div class="mb-3">
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="login_password"
                               autocomplete="current-password" 
                               placeholder="Mật khẩu" 
                               required>
                    </div>
                    <button type="submit" name="login" class="btn btn-auth">
                        <i class="fas fa-sign-in-alt me-2"></i> Đăng nhập
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="admin/pages/auth/forgot-password.php">
                            <i class="fas fa-lock me-1"></i> Quên mật khẩu?
                        </a>
                    </div>
                    
                    <div class="text-center mt-2">
                        <a href="user/index.php">
                            <i class="fas fa-home me-1"></i> Về trang chủ
                        </a>
                    </div>
                </form>
            </div>

            <div class="auth-divider"></div>

            <!-- Đăng ký -->
            <div class="auth-side">
                <h2 class="auth-title text-center">Đăng ký</h2>
                <?php if (isset($register_error)): ?>
                    <div class="alert alert-danger"><?php echo $register_error; ?></div>
                <?php endif; ?>
                <?php if (isset($register_success)): ?>
                    <div class="alert alert-success"><?php echo $register_success; ?></div>
                <?php endif; ?>
                <form method="POST" autocomplete="on" id="registerForm">
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="reg_username" 
                               id="reg_username"
                               autocomplete="username" 
                               placeholder="Tên đăng nhập" 
                               required>
                    </div>
                    <div class="mb-3">
                        <input type="password" 
                               class="form-control" 
                               name="reg_password" 
                               id="reg_password"
                               autocomplete="new-password" 
                               placeholder="Mật khẩu" 
                               required>
                    </div>
                    <div class="mb-3">
                        <input type="email" 
                               class="form-control" 
                               name="reg_email" 
                               id="reg_email"
                               autocomplete="email" 
                               placeholder="Email" 
                               required>
                    </div>
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="reg_fullname" 
                               id="reg_fullname"
                               autocomplete="name" 
                               placeholder="Họ và tên" 
                               required>
                    </div>
                    <div class="mb-3">
                        <select class="form-control" 
                                name="reg_gender" 
                                id="reg_gender"
                                autocomplete="sex" 
                                required>
                            <option value="">Chọn giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="tel" 
                               class="form-control" 
                               name="reg_phone" 
                               id="reg_phone"
                               autocomplete="tel" 
                               placeholder="Số điện thoại" 
                               required>
                        <div id="phone-error" class="text-danger small" style="display: none;">
                            Số điện thoại không hợp lệ! Vui lòng nhập số điện thoại Việt Nam (10 số bắt đầu bằng 0 hoặc +84 theo sau 9 số).
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               name="reg_address" 
                               id="reg_address"
                               autocomplete="street-address" 
                               placeholder="Địa chỉ" 
                               required>
                    </div>
                    <button type="submit" name="register" class="btn btn-auth">
                        <i class="fas fa-user-plus me-2"></i> Đăng ký
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Kiểm tra số điện thoại real-time
        document.getElementById('reg_phone').addEventListener('input', function() {
            const phone = this.value.trim();
            const phoneError = document.getElementById('phone-error');
            const phoneRegex = /^(0|\+84)[0-9]{9}$/;
            
            if (phone && !phoneRegex.test(phone)) {
                phoneError.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                phoneError.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });
        
        // Kiểm tra khi submit form
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('reg_phone').value.trim();
            const phoneRegex = /^(0|\+84)[0-9]{9}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                document.getElementById('phone-error').style.display = 'block';
                document.getElementById('reg_phone').classList.add('is-invalid');
                document.getElementById('reg_phone').focus();
            }
        });
    </script>
</body>
</html> 
