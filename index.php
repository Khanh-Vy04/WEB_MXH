<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình session trước khi khởi động session
ini_set('session.cookie_samesite', 'Lax');     // Thêm SameSite=Lax
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');      // Chỉ sử dụng cookie cho session
ini_set('session.use_strict_mode', '1');       // Strict mode cho session ID

// Khởi động session sau khi đã cấu hình
session_start();

// Debug: Xóa session cũ nếu đang test
if (isset($_GET['clear_session'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Kiểm tra kết nối database
require_once 'config/database.php';
require_once 'includes/session.php';

if (!isset($conn) || $conn->connect_error) {
    die("Lỗi kết nối database: " . $conn->connect_error);
}

// Debug: In ra thông tin POST
if (isset($_POST['login'])) {
    echo "Debug POST data:<br>";
    echo "Username: " . (isset($_POST['username']) ? $_POST['username'] : 'not set') . "<br>";
    echo "Password: " . (isset($_POST['password']) ? '[hidden]' : 'not set') . "<br>";
}

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $login_error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Debug: In ra thông tin trước khi query
        echo "Debug: Attempting to find user '$username'<br>";
        
        // Truy vấn user từ bảng users
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Debug: In ra kết quả query
        echo "Debug: Found " . $result->num_rows . " matching users<br>";
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Debug: In ra thông tin user (không bao gồm password)
            echo "Debug: User found:<br>";
            echo "- User ID: " . $user['user_id'] . "<br>";
            echo "- Username: " . $user['username'] . "<br>";
            echo "- Role ID: " . $user['role_id'] . "<br>";
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                echo "Debug: Password verified successfully<br>";
                
                // Lưu session trước khi redirect
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = ($user['role_id'] == 1) ? 'admin' : 'user';
                $_SESSION['login_time'] = time();
                
                // Chuyển hướng dựa vào role_id
                if ($user['role_id'] == 1) {
                    echo "Debug: User is admin, redirecting to dashboard...<br>";
                    echo "<script>console.log('Redirecting to admin dashboard...');</script>";
                    header("Location: /WEB_MXH/admin/pages/dashboard/dashboard.php");
                    exit();
                } else {
                    echo "Debug: User is not admin, redirecting to user page...<br>";
                    header("Location: /WEB_MXH/user/index.php");
                    exit();
                }
            } else {
                echo "Debug: Password verification failed<br>";
                $login_error = "Sai mật khẩu!";
            }
        } else {
            echo "Debug: No user found with username '$username'<br>";
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
    
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($fullname) || empty($gender) || empty($phone)) {
        $register_error = "Vui lòng điền đầy đủ thông tin!";
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
                
                $sql = "INSERT INTO users (role_id, username, password, email, full_name, gender, phone) 
                        VALUES (2, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Lỗi prepare statement: " . $conn->error);
                }
                
                $stmt->bind_param("ssssss", $username, $hashed_password, $email, $fullname, $gender, $phone);
                
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
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.9);
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
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn-auth:hover {
            background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
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
        console.log("Debug: Trang đăng nhập đã load");
        console.log("Session status: <?php echo session_status(); ?>");
        console.log("Session ID: <?php echo session_id(); ?>");
        
        // Debug form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            console.log("Debug: Form đăng nhập được submit");
            var username = document.getElementById('login_username').value;
            console.log("Debug: Username:", username);
        });
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            console.log("Debug: Form đăng ký được submit");
            var username = document.getElementById('reg_username').value;
            console.log("Debug: Register username:", username);
        });
    </script>
</body>
</html> 