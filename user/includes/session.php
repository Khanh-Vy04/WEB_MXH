<?php
session_start();

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Hàm lấy thông tin user hiện tại
function getCurrentUser() {
    if (isLoggedIn()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

// Hàm login user
function loginUser($userData) {
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['fullname'] = $userData['fullname'] ?? $userData['full_name'] ?? '';
    $_SESSION['full_name'] = $userData['full_name'] ?? $userData['fullname'] ?? '';
    $_SESSION['role_id'] = $userData['role_id'] ?? 2;
    $_SESSION['gender'] = $userData['gender'] ?? '';
    $_SESSION['phone'] = $userData['phone'] ?? '';
    $_SESSION['address'] = $userData['address'] ?? '';
}

// Hàm logout user
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
}

// Hàm redirect nếu chưa login (dùng cho admin)
function requireLogin($redirectUrl = '/WEB_MXH/index.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectUrl");
        exit();
    }
}

// Hàm mới: kiểm tra đăng nhập với popup (dùng cho user)
function requireLoginWithPopup() {
    if (!isLoggedIn()) {
        // Hiển thị popup đăng nhập thay vì redirect
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showLoginModal();
        });
        
        function showLoginModal() {
            // Tạo modal đăng nhập
            const modalHTML = `
            <div id="loginModal" class="login-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div class="login-modal-content" style="
                    background: white;
                    padding: 30px;
                    border-radius: 15px;
                    max-width: 400px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                ">
                    <h3 style="margin-bottom: 20px; color: #333;">Cần Đăng Nhập</h3>
                    <p style="margin-bottom: 25px; color: #666;">Vui lòng đăng nhập để sử dụng tính năng này</p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <a href="/WEB_MXH/index.php" class="btn btn-login" style="
                            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
                            color: white;
                            padding: 12px 20px;
                            border-radius: 25px;
                            text-decoration: none;
                            font-weight: 600;
                            transition: all 0.3s ease;
                        ">Đăng Nhập</a>
                        <button onclick="closeLoginModal()" class="btn btn-cancel" style="
                            background: #6c757d;
                            color: white;
                            padding: 12px 20px;
                            border: none;
                            border-radius: 25px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        ">Đóng</button>
                    </div>
                </div>
            </div>`;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.remove();
            }
        }
        
        // Đóng modal khi click outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'loginModal') {
                closeLoginModal();
            }
        });
        </script>
        <?php
        return false;
    }
    return true;
}

// Hàm kiểm tra quyền truy cập với popup tùy chỉnh
function requireLoginForFeature($featureName = "tính năng này") {
    if (!isLoggedIn()) {
        return [
            'success' => false,
            'message' => "Vui lòng đăng nhập để sử dụng $featureName",
            'require_login' => true
        ];
    }
    return [
        'success' => true,
        'user' => getCurrentUser()
    ];
}

// Hàm trả về JSON response cho AJAX
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?> 
