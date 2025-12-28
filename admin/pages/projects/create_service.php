<?php
$currentPage = 'projects';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Tạo dịch vụ mới - UniWork</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/user/assets/logo/favicon.png" rel="icon">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="../../lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../dashboard/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../dashboard/css/style.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 20px;
        }
        
        .form-section-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3436;
            margin-bottom: 5px;
        }
        
        .form-section-desc {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.95rem;
            color: #2d3436;
        }
        
        .required { color: #dc3545; margin-left: 3px; }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border-color: #e9ecef;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(65, 45, 59, 0.1);
            border-color: #412d3b;
        }
        
        /* Package Tabs */
        .package-tabs {
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .package-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            margin-right: 5px;
            background: transparent;
            border-bottom: 3px solid transparent;
        }
        
        .package-tabs .nav-link.active {
            color: #412d3b;
            border-bottom-color: #412d3b;
        }
        
        .package-tabs .nav-link:hover {
            color: #412d3b;
        }
        
        /* Image Upload */
        .upload-area {
            border: 2px dashed #e9ecef;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }
        
        .upload-area:hover {
            border-color: #412d3b;
            background: #fff;
        }
        
        .upload-icon {
            font-size: 2rem;
            color: #adb5bd;
            margin-bottom: 15px;
        }
        
        .btn-submit {
            background: #2d3436;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #000;
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-outline {
            border: 1px solid #e9ecef;
            background: white;
            color: #2d3436;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .char-count {
            font-size: 0.8rem;
            color: #adb5bd;
            margin-top: 5px;
            text-align: right;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
        }
        
        .tag-input-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-add-feature {
            background: #6c757d;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include '../dashboard/sidebar.php'; ?>
    
    <div class="content">
        <?php include '../dashboard/navbar.php'; ?>
        
        <div class="container-fluid pt-4 px-4 pb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1" style="color: #2d3436; font-weight: 700;">Tạo dịch vụ mới</h3>
                    <p class="text-muted mb-0">Tạo và đăng bán dịch vụ của bạn trên UniWork</p>
                </div>
                <a href="project_list.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>
            
            <form action="" method="POST">
                <!-- Basic Info -->
                <div class="form-card">
                    <div class="form-section-title">Thông tin cơ bản</div>
                    <p class="form-section-desc">Nhập thông tin cơ bản về dịch vụ của bạn</p>
                    
                    <div class="mb-4">
                        <label class="form-label">Tiêu đề dịch vụ <span class="required">*</span></label>
                        <input type="text" class="form-control" placeholder="VD: Thiết kế logo chuyên nghiệp cho thương hiệu">
                        <div class="char-count">0/100 ký tự</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Mô tả chi tiết <span class="required">*</span></label>
                        <textarea class="form-control" rows="5" placeholder="Mô tả chi tiết về dịch vụ, quy trình làm việc, những gì khách hàng sẽ nhận được..."></textarea>
                        <div class="char-count">0/2000 ký tự</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Danh mục <span class="required">*</span></label>
                        <select class="form-select">
                            <option selected disabled>Chọn danh mục dịch vụ</option>
                            <option value="design">Thiết kế đồ họa</option>
                            <option value="dev">Lập trình & CNTT</option>
                            <option value="marketing">Digital Marketing</option>
                            <option value="writing">Viết lách & Dịch thuật</option>
                            <option value="video">Video & Âm thanh</option>
                        </select>
                    </div>
                </div>
                
                <!-- Pricing Packages -->
                <div class="form-card">
                    <div class="form-section-title">Gói dịch vụ</div>
                    <p class="form-section-desc">Thiết lập các gói dịch vụ để khách hàng lựa chọn</p>
                    
                    <ul class="nav nav-tabs package-tabs" id="packageTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Gói Cơ bản</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard" type="button" role="tab">Gói Tiêu chuẩn</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="premium-tab" data-bs-toggle="tab" data-bs-target="#premium" type="button" role="tab">Gói Cao cấp</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="packageContent">
                        <!-- Basic Package -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Giá dịch vụ (VND) <span class="required">*</span></label>
                                <input type="number" class="form-control" placeholder="500000">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thời gian hoàn thành (ngày) <span class="required">*</span></label>
                                <input type="number" class="form-control" placeholder="3">
                                <small class="text-muted">Thời gian bạn cần để hoàn thành dịch vụ</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lần chỉnh sửa <span class="required">*</span></label>
                                <input type="number" class="form-control" placeholder="2">
                                <small class="text-muted">Số lần khách hàng có thể yêu cầu chỉnh sửa</small>
                            </div>
                        </div>
                        
                        <!-- Standard & Premium (Similar structure, can be populated via JS or PHP loop in real app) -->
                        <div class="tab-pane fade" id="standard" role="tabpanel">
                            <div class="text-center py-3 text-muted">Cấu hình tương tự gói Cơ bản</div>
                        </div>
                        <div class="tab-pane fade" id="premium" role="tabpanel">
                            <div class="text-center py-3 text-muted">Cấu hình tương tự gói Cơ bản</div>
                        </div>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="form-card">
                    <div class="form-section-title">Tính năng bao gồm</div>
                    <p class="form-section-desc">Liệt kê những gì khách hàng sẽ nhận được khi đặt dịch vụ</p>
                    
                    <div class="tag-input-container mb-2">
                        <input type="text" class="form-control" placeholder="Nhập tính năng (VD: File PNG/JPG, Hỗ trợ 24/7)">
                        <button type="button" class="btn-add-feature"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="small text-muted">0/20 tính năng</div>
                </div>
                
                <!-- Tags -->
                <div class="form-card">
                    <div class="form-section-title">Tags</div>
                    <p class="form-section-desc">Thêm các từ khóa để khách hàng dễ tìm thấy dịch vụ của bạn</p>
                    
                    <div class="tag-input-container mb-2">
                        <input type="text" class="form-control" placeholder="Nhập tag (VD: logo, design, branding)">
                        <button type="button" class="btn-add-feature"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="small text-muted">0/10 tags</div>
                </div>
                
                <!-- Images -->
                <div class="form-card">
                    <div class="form-section-title">Hình ảnh mẫu</div>
                    <p class="form-section-desc">Thêm hình ảnh để showcase dịch vụ của bạn (tối đa 10 ảnh)</p>
                    
                    <div class="upload-area">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <h6 class="mb-1">Nhấp để tải ảnh hoặc kéo thả</h6>
                        <p class="small text-muted mb-0">PNG, JPG, GIF tối đa 10MB mỗi file</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline"><i class="far fa-save me-2"></i> Lưu nháp</button>
                    <div>
                        <button type="button" class="btn btn-outline me-2"><i class="far fa-eye me-2"></i> Xem trước</button>
                        <button type="submit" class="btn btn-submit">Đăng dịch vụ</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php include '../dashboard/footer.php'; ?>
    </div>
</div>

<!-- Javascript -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../lib/chart/chart.min.js"></script>
<script src="../../../lib/easing/easing.min.js"></script>
<script src="../../../lib/waypoints/waypoints.min.js"></script>
<script src="../../../lib/owlcarousel/owl.carousel.min.js"></script>
<script src="../../../lib/tempusdominus/js/moment.min.js"></script>
<script src="../../../lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="../../../lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../dashboard/js/main.js"></script>
</body>
</html>

