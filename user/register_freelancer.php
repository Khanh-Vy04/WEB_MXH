<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Security check
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$currentUser = getCurrentUser();
if ($currentUser['role_id'] != 2) {
    // If already freelancer or admin, redirect home
    header('Location: index.php');
    exit();
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký Freelancer - UniWork</title>
    
    <!-- Standard Favicon -->
    <link rel="shortcut icon" href="assets/logo/favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="assets/css/bootsnav.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
        }
        
        .registration-container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            padding: 40px;
            min-height: 600px;
        }

        /* Stepper */
        .stepper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .stepper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            color: #fff;
            position: relative;
            z-index: 2;
            margin: 0 40px;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: #666; /* Using a neutral dark grey for active step */
            transform: scale(1.1);
        }
        
        .step.completed {
            background: #412d3b; /* Using brand color */
        }

        /* Form Header */
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-weight: 700;
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #666;
            font-size: 14px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            height: auto;
            box-shadow: none;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #412d3b;
            box-shadow: 0 0 0 3px rgba(65, 45, 59, 0.1);
        }
        
        .form-row {
            display: flex;
            margin: 0 -10px;
        }
        
        .col-half {
            width: 50%;
            padding: 0 10px;
        }
        
        .col-third {
            width: 33.33%;
            padding: 0 10px;
        }
        
        /* Freelancer Type Radio */
        .radio-group {
            display: flex;
            gap: 15px;
        }
        
        .radio-card {
            flex: 1;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .radio-card:hover {
            border-color: #412d3b;
        }
        
        .radio-card.selected {
            border-color: #412d3b;
            background-color: rgba(65, 45, 59, 0.05);
        }
        
        .radio-card input {
            display: none;
        }
        
        .radio-circle {
            width: 18px;
            height: 18px;
            border: 2px solid #ccc;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }
        
        .radio-card.selected .radio-circle {
            border-color: #412d3b;
        }
        
        .radio-card.selected .radio-circle::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            background: #412d3b;
            border-radius: 50%;
        }

        /* Buttons */
        .btn-submit {
            background-color: #0d6efd; /* Blue color as in design */
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        
        .btn-verify {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 8px 8px 0;
            font-weight: 500;
            font-size: 13px;
        }

        .input-group-verify {
            display: flex;
        }
        
        .input-group-verify .form-control {
            border-radius: 8px 0 0 8px;
            flex: 1;
        }

        .helper-text {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 20px;
        }
        
        .checkbox-group input {
            margin-top: 4px;
        }
        
        .checkbox-group label {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        
        .checkbox-group a {
            color: #0d6efd;
            text-decoration: none;
        }

        /* Display Control */
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <?php include 'includes/navigation.php'; ?>

    <div class="registration-container">
        <!-- Stepper -->
        <div class="stepper">
            <div class="step active" id="step1-indicator">1</div>
            <div class="step" id="step2-indicator">2</div>
        </div>

        <form id="freelancerForm" method="POST" action="ajax/register_freelancer_handler.php">
            <!-- STEP 1: Authentication Info -->
            <div class="step-content active" id="step1">
                <div class="form-header">
                    <h2>Thông tin xác thực</h2>
                    <p>Vui lòng cung cấp thông tin chính xác để xác minh danh tính</p>
                </div>

                <div class="form-row">
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Tên đệm và tên</label>
                            <input type="text" class="form-control" name="firstname" placeholder="Nhập tên đệm và tên" required>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Họ</label>
                            <input type="text" class="form-control" name="lastname" placeholder="Nhập họ" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Số CMND/CCCD</label>
                    <input type="text" class="form-control" name="id_card" placeholder="Nhập số CMND/CCCD 12 số" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ theo CMND/CCCD</label>
                    <input type="text" class="form-control" name="address_full" placeholder="Chi tiết địa chỉ theo CMND/CCCD" required>
                </div>

                <div class="form-row">
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Mã bưu điện</label>
                            <input type="text" class="form-control" name="zipcode" placeholder="Mã bưu điện">
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Xã/Phường</label>
                            <input type="text" class="form-control" name="ward" placeholder="Nhập xã/phường">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Quận/Huyện</label>
                            <input type="text" class="form-control" name="district" placeholder="Nhập quận/huyện">
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" class="form-control" name="city" placeholder="Nhập tỉnh/thành phố">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ngày sinh</label>
                    <div class="form-row">
                        <div class="col-third">
                            <select class="form-control" name="dob_day" required>
                                <option value="">Ngày</option>
                                <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>
                        <div class="col-third">
                            <select class="form-control" name="dob_month" required>
                                <option value="">Tháng</option>
                                <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>
                        <div class="col-third">
                            <select class="form-control" name="dob_year" required>
                                <option value="">Năm</option>
                                <?php for($i=date('Y')-18; $i>=1950; $i--) echo "<option value='$i'>$i</option>"; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Email liên hệ</label>
                            <div class="input-group-verify">
                                <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                                <button type="button" class="btn-verify" disabled>Đã xác nhận</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label class="form-label">Số điện thoại liên hệ (tại Việt Nam)</label>
                            <div class="input-group-verify">
                                <input type="text" class="form-control" name="phone" placeholder="Nhập số liên lạc">
                                <button type="button" class="btn-verify">Xác nhận</button>
                            </div>
                            <p class="helper-text">Nếu sử dụng số điện thoại nước ngoài, vui lòng liên hệ trung tâm hỗ trợ</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ hiện tại</label>
                    <div class="radio-group">
                        <div class="radio-card selected" onclick="selectRadio(this, 'location_type')">
                            <div class="radio-circle"></div>
                            <span>Việt Nam</span>
                            <input type="radio" name="location_type" value="vietnam" checked>
                        </div>
                        <div class="radio-card" onclick="selectRadio(this, 'location_type')">
                            <div class="radio-circle"></div>
                            <span>Nước ngoài</span>
                            <input type="radio" name="location_type" value="foreign">
                        </div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">
                        Tôi đã đọc và chấp nhận <a href="#">Điều khoản và Điều kiện sử dụng</a><br>
                        Tôi đã đọc và chấp nhận <a href="#">Chính sách bảo mật</a>
                    </label>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <button type="button" class="btn-submit" onclick="nextStep()">Đăng ký làm freelancer</button>
                </div>
            </div>

            <!-- STEP 2: Profile Setup -->
            <div class="step-content" id="step2">
                <div class="form-header">
                    <h2>Tạo hồ sơ freelancer của bạn</h2>
                    <p>Thiết lập thông tin cơ bản để tạo uy tín</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <p class="helper-text" style="margin-bottom: 5px;">Username sẽ hiển thị trong URL. Vui lòng chỉ sử dụng tiếng Anh.</p>
                    <input type="text" class="form-control" name="username_display" value="Fastlance.vn/user/<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Tên hiển thị trên hệ thống</label>
                    <p class="helper-text" style="margin-bottom: 5px;">Nên sử dụng tên thật để tăng độ uy tín</p>
                    <input type="text" class="form-control" name="display_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Loại freelancer (không hiển thị)</label>
                    <p class="helper-text" style="margin-bottom: 5px;">Chỉ sử dụng để cải thiện hệ thống</p>
                    <div class="radio-group">
                        <div class="radio-card" onclick="selectRadio(this, 'freelancer_type')">
                            <div class="radio-circle"></div>
                            <span>Bán thời gian</span>
                            <input type="radio" name="freelancer_type" value="part_time">
                        </div>
                        <div class="radio-card" onclick="selectRadio(this, 'freelancer_type')">
                            <div class="radio-circle"></div>
                            <span>Toàn thời gian</span>
                            <input type="radio" name="freelancer_type" value="full_time">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Về freelancer</label>
                    <textarea class="form-control" name="bio" rows="5" placeholder="Mô tả ngắn gọn điểm mạnh của bạn để giúp khách hàng quyết định"></textarea>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <button type="submit" class="btn-submit">Lưu và tiếp tục <i class="fa fa-arrow-right"></i></button>
                </div>
                
                <div class="form-group" style="text-align: center; margin-top: 15px;">
                    <a href="javascript:void(0)" onclick="prevStep()" style="color: #666; text-decoration: none;">Quay lại bước trước</a>
                </div>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    
    <script>
        function selectRadio(element, groupName) {
            // Remove selected class from all items in this group
            const group = element.parentElement;
            const cards = group.getElementsByClassName('radio-card');
            for (let card of cards) {
                card.classList.remove('selected');
                const radio = card.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
            }
            
            // Add selected class to clicked element
            element.classList.add('selected');
            const radio = element.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        function nextStep() {
            // Validate Step 1
            const firstname = document.querySelector('input[name="firstname"]').value;
            const lastname = document.querySelector('input[name="lastname"]').value;
            const idCard = document.querySelector('input[name="id_card"]').value;
            const terms = document.getElementById('terms').checked;

            if (!firstname || !lastname || !idCard) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                return;
            }

            if (!terms) {
                alert('Vui lòng chấp nhận điều khoản và chính sách');
                return;
            }

            // Switch to Step 2
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            
            document.getElementById('step1-indicator').classList.remove('active');
            document.getElementById('step1-indicator').classList.add('completed');
            document.getElementById('step1-indicator').innerHTML = '<i class="fa fa-check"></i>';
            document.getElementById('step2-indicator').classList.add('active');
            
            window.scrollTo(0, 0);
        }

        function prevStep() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            
            document.getElementById('step2-indicator').classList.remove('active');
            document.getElementById('step1-indicator').classList.remove('completed');
            document.getElementById('step1-indicator').classList.add('active');
            document.getElementById('step1-indicator').innerHTML = '1';
            
            window.scrollTo(0, 0);
        }

        // Handle form submission
        $('#freelancerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'ajax/register_freelancer_handler.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert('Đăng ký thành công! Chào mừng bạn trở thành Freelancer.');
                            window.location.href = '../admin/dashboard.php'; // Or appropriate redirection
                        } else {
                            alert('Lỗi: ' + res.message);
                        }
                    } catch (e) {
                        console.error(response);
                        alert('Có lỗi xảy ra trong quá trình xử lý.');
                    }
                },
                error: function() {
                    alert('Không thể kết nối tới máy chủ.');
                }
            });
        });
    </script>
</body>
</html>

