<?php
$currentPage = 'settings';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Ensure SuperAdmin
if (!isSuperAdmin()) {
    header("Location: /WEB_MXH/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cài đặt Hệ thống - UniWork SuperAdmin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- CSS Includes -->
    <link href="/WEB_MXH/user/assets/logo/favicon.png" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
        }
        
        .nav-pills .nav-link {
            color: #6c757d;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            margin-right: 2px;
            padding: 12px 25px;
            font-weight: 500;
        }
        
        .nav-pills .nav-link.active {
            color: #2d3436;
            background: white;
            border: 1px solid #f0f0f0;
            border-bottom: none;
            font-weight: 700;
            box-shadow: 0 -2px 6px rgba(0,0,0,0.01);
        }
        
        .tab-content {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 0 8px 8px 8px;
            padding: 30px;
            margin-top: -1px; /* Overlap border */
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border-color: #e9ecef;
            font-size: 0.95rem;
            background-color: #fff;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i { margin-right: 10px; }
        
        .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-top: 0;
            cursor: pointer;
        }
        
        .setting-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .setting-row:last-child { border-bottom: none; }
        
        .setting-info h6 { margin-bottom: 2px; font-weight: 600; font-size: 0.95rem; }
        .setting-info p { margin-bottom: 0; font-size: 0.85rem; color: #6c757d; }
        
        .btn-save {
            background: #0d1b2a;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
        }
        
        .btn-save:hover { background: #000; color: white; }
        
        .btn-refresh {
            background: white;
            border: 1px solid #dee2e6;
            color: #2d3436;
            padding: 8px 15px;
            border-radius: 8px;
            margin-right: 10px;
        }
        
        .btn-refresh:hover { background: #f8f9fa; }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <div class="content">
            <!-- Navbar -->
            <?php include '../../includes/navbar.php'; ?>

            <div class="container-fluid pt-4 px-4 pb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="mb-1" style="color: #2d3436; font-weight: 700;">Cài đặt Hệ thống</h3>
                        <p class="text-muted mb-0">Cấu hình và quản lý các thiết lập hệ thống</p>
                    </div>
                    <div>
                        <button class="btn btn-refresh"><i class="fas fa-sync-alt me-2"></i>Làm mới</button>
                        <button class="btn btn-save"><i class="fas fa-save me-2"></i>Lưu cài đặt</button>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-pills mb-3" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">Chung</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button" role="tab">Thanh toán</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="notification-tab" data-bs-toggle="pill" data-bs-target="#notification" type="button" role="tab">Thông báo</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">Bảo mật</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="content-tab" data-bs-toggle="pill" data-bs-target="#content" type="button" role="tab">Nội dung</button>
                    </li>
                </ul>

                <div class="tab-content" id="settingsTabContent">
                    
                    <!-- Tab 1: General -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="section-title"><i class="fas fa-cog"></i> Cài đặt chung</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên website</label>
                                <input type="text" class="form-control" value="UniWork Marketplace">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email liên hệ</label>
                                <input type="email" class="form-control" value="contact@uniwork.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả website</label>
                            <textarea class="form-control" rows="2">Nền tảng kết nối freelancer và khách hàng hàng đầu Việt Nam</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Email hỗ trợ</label>
                            <input type="email" class="form-control" value="support@uniwork.com">
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Chế độ bảo trì</h6>
                                <p>Kích hoạt để tạm thời đóng website</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Cho phép đăng ký</h6>
                                <p>Người dùng mới có thể tạo tài khoản</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Payment -->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <div class="section-title"><i class="fas fa-credit-card"></i> Cài đặt thanh toán</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tỷ lệ hoa hồng (%)</label>
                                <input type="number" class="form-control" value="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Đơn vị tiền tệ</label>
                                <select class="form-select">
                                    <option value="VND" selected>VND</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Số tiền rút tối thiểu</label>
                                <input type="number" class="form-control" value="100000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số tiền rút tối đa</label>
                                <input type="number" class="form-control" value="50000000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phí rút tiền</label>
                            <input type="number" class="form-control" value="0">
                        </div>
                    </div>

                    <!-- Tab 3: Notifications -->
                    <div class="tab-pane fade" id="notification" role="tabpanel">
                        <div class="section-title"><i class="fas fa-bell"></i> Cài đặt thông báo</div>
                        
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Thông báo email</h6>
                                <p>Gửi thông báo qua email cho người dùng</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Thông báo SMS</h6>
                                <p>Gửi thông báo qua tin nhắn SMS</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Thông báo đẩy</h6>
                                <p>Gửi thông báo đẩy trên trình duyệt</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Thông báo admin</h6>
                                <p>Gửi thông báo cho admin khi có hoạt động mới</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Security -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="section-title"><i class="fas fa-shield-alt"></i> Cài đặt bảo mật</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Độ dài mật khẩu tối thiểu</label>
                                <input type="number" class="form-control" value="8">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thời gian hết hạn phiên (phút)</label>
                                <input type="number" class="form-control" value="60">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Số lần đăng nhập sai tối đa</label>
                            <input type="number" class="form-control" value="5">
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Yêu cầu xác thực email</h6>
                                <p>Người dùng phải xác thực email khi đăng ký</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Xác thực hai yếu tố</h6>
                                <p>Bật xác thực hai yếu tố cho tài khoản</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: Content -->
                    <div class="tab-pane fade" id="content" role="tabpanel">
                        <div class="section-title"><i class="fas fa-file-alt"></i> Cài đặt nội dung</div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Số dịch vụ tối đa mỗi người</label>
                                <input type="number" class="form-control" value="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số ảnh tối đa mỗi dịch vụ</label>
                                <input type="number" class="form-control" value="10">
                            </div>
                        </div>
                        
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Kiểm duyệt tự động</h6>
                                <p>Tự động kiểm duyệt nội dung mới</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        <div class="setting-row">
                            <div class="setting-info">
                                <h6>Bộ lọc từ ngữ</h6>
                                <p>Lọc các từ ngữ không phù hợp</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Footer -->
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/lib/chart/chart.min.js"></script>
    <script src="/WEB_MXH/admin/lib/easing/easing.min.js"></script>
    <script src="/WEB_MXH/admin/lib/waypoints/waypoints.min.js"></script>
    <script src="/WEB_MXH/admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>
</html>

