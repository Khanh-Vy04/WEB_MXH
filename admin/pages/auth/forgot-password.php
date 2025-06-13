<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'] . "/WEB_MXH/config/database.php";
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lấy lại mật khẩu - AuraDisc Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: #deccca;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-container {
            background: #f3eeeb;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 20px;
            backdrop-filter: blur(10px);
        }

        .auth-header {
            background: #412d3b;
            padding: 30px;
            text-align: center;
            color: #f3eeeb;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }

        .auth-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .auth-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #412d3b;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 15px 15px 15px 45px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #412d3b;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1.1rem;
        }

        .toggle-password:hover {
            color: #412d3b;
        }

        .btn-auth {
            background: #deccca;
            border: none;
            border-radius: 12px;
            color: #412d3b;
            padding: 15px;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-auth:hover {
            background: #412d3b;
            color: #deccca;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .btn-auth:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #412d3b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #412d3b;
            text-decoration: underline;
        }

        /* Toast Notification */
        .toast-notification {
            display: none;
            position: fixed;
            top: 32px;
            right: 32px;
            z-index: 9999;
            animation: slideIn 0.5s ease-out;
        }

        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            font-size: 1rem;
            font-weight: 500;
            min-width: 300px;
        }

        .toast-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #4ade80;
            border-radius: 50%;
        }

        .toast-error .toast-icon {
            background: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .toast-notification.hide {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e1e5e9;
            color: #6c757d;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .step.active {
            background:#deccca;
            color: #412d3b;
        }

        .step.completed {
            background: #deccca;
            color: #412d3b;
        }

        .username-info {
            margin: 15px 0;
            padding: 15px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border: 1px solid #4ade80;
            animation: slideDown 0.3s ease-out;
        }

        .username-found-content {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #166534;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .username-found-content i {
            color: #4ade80;
            font-size: 1.1rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <!-- Toast Notification -->
    <div id="toast" class="toast-notification">
        <div class="toast-content">
            <span class="toast-icon">
                <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <span id="toast-message">Thông báo</span>
        </div>
    </div>

    <div class="auth-container">
        <div class="auth-header">
            <h1>AuraDisc Admin</h1>
            <p>Lấy lại mật khẩu quản trị</p>
        </div>

        <div class="auth-body">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step1">1</div>
                <div class="step" id="step2">2</div>
                <div class="step" id="step3">3</div>
            </div>

            <!-- Bước 1: Nhập email -->
            <div id="email-section">
                <div class="form-group">
                    <label for="email">Email đăng ký</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope icon"></i>
                        <input type="email" class="form-control" id="email" placeholder="Nhập email của bạn" required>
                    </div>
                </div>

                <!-- Thông báo tìm thấy username -->
                <div id="username-found" class="username-info" style="display: none;">
                    <div class="username-found-content">
                        <i class="fas fa-user-check"></i>
                        <span id="username-message"></span>
                    </div>
                </div>

                <button type="button" class="btn btn-auth" id="send-otp">
                    <i class="fas fa-paper-plane me-2"></i> Gửi mã xác thực
                </button>
            </div>

            <!-- Bước 2: Nhập OTP -->
            <div id="otp-section" style="display: none;">
                <div class="form-group">
                    <label for="otp">Mã xác thực</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-key icon"></i>
                        <input type="text" class="form-control" id="otp" placeholder="Nhập mã xác thực từ email" maxlength="6" required>
                    </div>
                    <small class="text-muted">Mã xác thực có hiệu lực trong 15 phút</small>
                </div>

                <button type="button" class="btn btn-auth" id="verify-otp">
                    <i class="fas fa-check me-2"></i> Xác nhận mã
                </button>
            </div>

            <!-- Bước 3: Nhập mật khẩu mới -->
            <div id="password-section" style="display: none;">
                <div class="form-group">
                    <label for="new-password">Mật khẩu mới</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock icon"></i>
                        <input type="password" class="form-control" id="new-password" placeholder="Nhập mật khẩu mới" required>
                        <span class="toggle-password" onclick="togglePassword('new-password')">
                            <i class="fa-solid fa-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Xác nhận mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock icon"></i>
                        <input type="password" class="form-control" id="confirm-password" placeholder="Nhập lại mật khẩu mới" required>
                        <span class="toggle-password" onclick="togglePassword('confirm-password')">
                            <i class="fa-solid fa-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <button type="button" class="btn btn-auth" id="update-password">
                    <i class="fas fa-save me-2"></i> Cập nhật mật khẩu
                </button>
            </div>

            <div class="back-link">
                <a href="/WEB_MXH/index.php">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector("i");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function showToast(message, isError = false, duration = 3000) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastContent = toast.querySelector('.toast-content');
            
            toastMessage.textContent = message;
            
            if (isError) {
                toastContent.classList.add('toast-error');
            } else {
                toastContent.classList.remove('toast-error');
            }
            
            toast.style.display = 'block';
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.style.display = 'none';
                    toast.classList.remove('hide');
                }, 500);
            }, duration);
        }

        function updateSteps(currentStep) {
            document.querySelectorAll('.step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                }
            });
        }

        // Bước 1: Gửi OTP
        document.getElementById('send-otp').addEventListener('click', async function() {
            const email = document.getElementById('email').value.trim();
            const btn = this;

            if (!email) {
                showToast("Vui lòng nhập email để lấy lại mật khẩu.", true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang gửi...';

            try {
                // Kiểm tra email có tồn tại không
                const verifyRes = await fetch('verify_email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });

                const verifyData = await verifyRes.json();

                if (!verifyData.success) {
                    showToast(verifyData.message || "Email không tồn tại.", true);
                    // Ẩn thông báo username nếu không tìm thấy
                    document.getElementById('username-found').style.display = 'none';
                    return;
                }

                // Hiển thị thông báo tìm thấy username
                if (verifyData.username) {
                    const usernameMessage = document.getElementById('username-message');
                    const usernameFound = document.getElementById('username-found');
                    
                    usernameMessage.textContent = verifyData.message;
                    usernameFound.style.display = 'block';
                }

                // Sinh mã xác thực
                const verificationCode = Math.floor(100000 + Math.random() * 900000);

                // Gửi mã xác thực qua Node.js API
                const sendMailRes = await fetch('http://localhost:3000/send-verification', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, verificationCode })
                });

                if (!sendMailRes.ok) {
                    const err = await sendMailRes.text();
                    showToast("Gửi email thất bại: " + err, true);
                    return;
                }

                // Lưu OTP vào session
                const storeRes = await fetch('store_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({
                        email,
                        otp_code: verificationCode,
                        action: 'store'
                    })
                });

                if (storeRes.ok) {
                    showToast("Mã xác thực đã được gửi tới email của bạn.");
                    
                    // Chuyển sang bước 2
                    document.getElementById('email-section').style.display = 'none';
                    document.getElementById('otp-section').style.display = 'block';
                    updateSteps(2);
                } else {
                    showToast("Gửi email thành công nhưng không lưu được OTP.", true);
                }
            } catch (error) {
                showToast("Có lỗi khi kết nối đến hệ thống.", true);
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Gửi mã xác thực';
            }
        });

        // Bước 2: Xác thực OTP
        document.getElementById('verify-otp').addEventListener('click', async function() {
            const email = document.getElementById('email').value.trim();
            const otp = document.getElementById('otp').value.trim();
            const btn = this;

            if (!otp) {
                showToast("Vui lòng nhập mã xác thực.", true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang xác thực...';

            try {
                const res = await fetch('store_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({
                        email,
                        otp_code: otp,
                        action: 'verify'
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast("Xác thực thành công! Vui lòng nhập mật khẩu mới.");
                    
                    // Chuyển sang bước 3
                    document.getElementById('otp-section').style.display = 'none';
                    document.getElementById('password-section').style.display = 'block';
                    updateSteps(3);
                } else {
                    showToast(data.message || "Mã xác thực không đúng.", true);
                }
            } catch (error) {
                showToast("Có lỗi khi kiểm tra mã xác thực.", true);
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i> Xác nhận mã';
            }
        });

        // Bước 3: Cập nhật mật khẩu
        document.getElementById('update-password').addEventListener('click', async function() {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('new-password').value.trim();
            const confirmPassword = document.getElementById('confirm-password').value.trim();
            const btn = this;

            if (!password || !confirmPassword) {
                showToast("Vui lòng nhập đầy đủ mật khẩu mới.", true);
                return;
            }
            
            if (password !== confirmPassword) {
                showToast("Hai mật khẩu phải giống nhau.", true);
                return;
            }

            if (password.length < 6) {
                showToast("Mật khẩu phải có ít nhất 6 ký tự.", true);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang cập nhật...';

            try {
                const res = await fetch('reset_password_db.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({
                        email,
                        new_password: password
                    })
                });
                
                const data = await res.json();
                if (data.success) {
                    showToast("Đổi mật khẩu thành công!", false, 2000);
                    
                    // Chuyển hướng về trang đăng nhập sau 2 giây
                    setTimeout(() => {
                        window.location.href = "/WEB_MXH/index.php";
                    }, 2000);
                } else {
                    showToast(data.message || "Có lỗi khi đổi mật khẩu.", true);
                }
            } catch (error) {
                showToast("Có lỗi khi kết nối đến hệ thống.", true);
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-2"></i> Cập nhật mật khẩu';
            }
        });

        // Auto focus cho input OTP khi hiển thị
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'otp-section' && mutation.target.style.display !== 'none') {
                    document.getElementById('otp').focus();
                }
                if (mutation.target.id === 'password-section' && mutation.target.style.display !== 'none') {
                    document.getElementById('new-password').focus();
                }
            });
        });

        observer.observe(document.getElementById('otp-section'), { attributes: true, attributeFilter: ['style'] });
        observer.observe(document.getElementById('password-section'), { attributes: true, attributeFilter: ['style'] });

        // Ẩn thông báo username khi thay đổi email
        document.getElementById('email').addEventListener('input', function() {
            document.getElementById('username-found').style.display = 'none';
        });
    </script>
</body>
</html> 