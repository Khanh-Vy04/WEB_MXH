<?php
// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm hiển thị popup đăng nhập
function showLoginPopup() {
    echo '
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" role="dialog" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginRequiredModalLabel">Thông báo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Vui lòng đăng nhập để tiếp tục!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <a href="/WEB_MXH/index.php" class="btn btn-primary">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Sử dụng vanilla JavaScript thay vì jQuery
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("loginRequiredModal");
            if (modal && typeof bootstrap !== "undefined") {
                var bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else {
                // Fallback cho jQuery nếu có
                if (typeof $ !== "undefined") {
                    $("#loginRequiredModal").modal("show");
                } else {
                    console.log("Vui lòng đăng nhập để tiếp tục!");
                    window.location.href = "/WEB_MXH/index.php";
                }
            }
        });
    </script>
    ';
}

// Hàm bảo vệ trang yêu cầu đăng nhập với popup
function requireLoginWithPopup() {
    if (!isLoggedIn()) {
        showLoginPopup();
        return false;
    }
    return true;
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

// Hàm đăng nhập và lưu session
function loginUser($user_id, $username) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
}

// Hàm đăng xuất
function logoutUser() {
    session_unset();
    session_destroy();
}

// Hàm kiểm tra quyền admin (dựa trên session)
function isAdminBySession() {
    if (isLoggedIn() && isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'] === 'admin';
    }
    return false;
}

// Hàm kiểm tra quyền admin (dựa trên user data)
function isAdmin($user = null) {
    if ($user) {
        // Kiểm tra từ dữ liệu user được truyền vào - chỉ dựa vào role_id
        return isset($user['role_id']) && $user['role_id'] == 1;
    } else {
        // Kiểm tra từ session hoặc database
        if (isLoggedIn()) {
            global $conn;
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT role_id FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $userData = $result->fetch_assoc();
                return $userData['role_id'] == 1;
            }
        }
        return false;
    }
}

// Hàm bảo vệ trang yêu cầu đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Hàm bảo vệ trang admin
function requireAdmin() {
    // Sử dụng isAdmin() mới có thể nhận parameter hoặc kiểm tra từ session
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}
?> 