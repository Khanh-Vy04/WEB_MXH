<?php
$currentPage = 'projects'; // Corresponds to 'Quản lý dịch vụ' in sidebar
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Mock Data for Services
$services = [
    [
        'id' => 1,
        'name' => 'Thiết kế Logo chuyên nghiệp',
        'image' => '/WEB_MXH/user/assets/images/collection/arrivals1.png',
        'category' => 'Thiết kế đồ họa',
        'status' => 'active', // active, pending, draft, paused, inactive
        'views' => 120,
        'orders' => 15
    ],
    [
        'id' => 2,
        'name' => 'Lập trình Web trọn gói',
        'image' => '/WEB_MXH/user/assets/images/collection/arrivals2.png',
        'category' => 'Lập trình & CNTT',
        'status' => 'pending',
        'views' => 0,
        'orders' => 0
    ],
    [
        'id' => 3,
        'name' => 'Viết bài SEO chuẩn',
        'image' => '/WEB_MXH/user/assets/images/collection/arrivals3.png',
        'category' => 'Viết lách & Dịch thuật',
        'status' => 'draft',
        'views' => 0,
        'orders' => 0
    ],
    [
        'id' => 4,
        'name' => 'Dịch thuật Anh - Việt',
        'image' => '/WEB_MXH/user/assets/images/collection/arrivals4.png',
        'category' => 'Viết lách & Dịch thuật',
        'status' => 'paused',
        'views' => 50,
        'orders' => 5
    ],
    [
        'id' => 5,
        'name' => 'Edit Video Tiktok',
        'image' => '/WEB_MXH/user/assets/images/collection/arrivals5.png',
        'category' => 'Video & Âm thanh',
        'status' => 'inactive',
        'views' => 10,
        'orders' => 1
    ]
];

function getStatusBadge($status) {
    switch ($status) {
        case 'active': return '<span class="badge badge-draft">Hoạt động</span>';
        case 'pending': return '<span class="badge badge-draft">Chờ phê duyệt</span>';
        case 'draft': return '<span class="badge badge-draft">Bản nháp</span>';
        case 'paused': return '<span class="badge badge-draft">Tạm dừng</span>';
        case 'inactive': return '<span class="badge badge-draft">Không hoạt động</span>';
        default: return '<span class="badge badge-draft">Không xác định</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quản lý dịch vụ - UniWork</title>
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
        body {
            background-color: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .stat-title {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .stat-icon {
            color: #adb5bd;
            font-size: 1.2rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-subtitle {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .stat-trend {
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .text-success { color: #22c55e !important; }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
        }
        
        .filter-tabs .btn {
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 6px 15px;
            margin-right: 5px;
            border: 1px solid #e9ecef;
            color: #6c757d;
            background: white;
        }
        
        .filter-tabs .btn.active {
            background: #2d3436;
            color: white;
            border-color: #2d3436;
        }
        
        .service-table-card {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
            overflow: hidden;
        }
        
        .table thead th {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 15px 20px;
        }
        
        .table tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 0.95rem;
        }
        
        .service-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .service-img {
            width: 60px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
            background: #f0f0f0;
        }
        
        .action-btn {
            color: #adb5bd;
            font-size: 1.1rem;
            margin-left: 10px;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .action-btn:hover {
            color: #412d3b;
        }
        
        .btn-create {
            background: #2d3436;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-create:hover {
            background: #000;
            color: white;
            transform: translateY(-2px);
        }
        
        .search-input {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 8px 15px;
            padding-left: 35px;
            background: #f8f9fa url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23adb5bd' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center;
        }
        
        .search-input:focus {
            background-color: white;
            border-color: #2d3436;
            box-shadow: none;
        }
        
        .category-badge {
            background: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge-draft {
    background-color: #deccca !important;
    color: #412d3b !important;
    border-color: transparent !important;
}
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include '../dashboard/sidebar.php'; ?>
    
    <div class="content">
        <?php include '../dashboard/navbar.php'; ?>
        
        <div class="container-fluid pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1" style="color: #2d3436; font-weight: 700;">Quản lý dịch vụ</h3>
                    <p class="text-muted mb-0">Quản lý và theo dõi hiệu suất các dịch vụ của bạn</p>
                </div>
                <a href="create_service.php" class="btn btn-create">
                    <i class="fas fa-plus me-2"></i> Tạo dịch vụ mới
                </a>
            </div>
            
            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Tổng dịch vụ</span>
                            <i class="fas fa-user stat-icon"></i>
                        </div>
                        <div>
                            <div class="stat-value">5</div>
                            <div class="stat-subtitle">1 đang hoạt động</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Tổng thu nhập</span>
                            <i class="fas fa-dollar-sign stat-icon"></i>
                        </div>
                        <div>
                            <div class="stat-value">16 Tr đ</div>
                            <div class="stat-subtitle text-success">+18% từ tháng trước</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Đánh giá trung bình</span>
                            <i class="far fa-star stat-icon"></i>
                        </div>
                        <div>
                            <div class="stat-value">4.8 <i class="fas fa-star text-warning" style="font-size: 1.2rem;"></i></div>
                            <div class="stat-subtitle">Từ 127 đánh giá</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Hiệu suất</span>
                            <i class="fas fa-chart-line stat-icon"></i>
                        </div>
                        <div>
                            <div class="stat-value text-success">+25%</div>
                            <div class="stat-subtitle">Tăng trưởng tháng này</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="filter-section">
                <h5 class="mb-3" style="font-weight: 700;">Bộ lọc</h5>
                <div class="row align-items-center">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <input type="text" class="form-control search-input" placeholder="Tìm kiếm dịch vụ...">
                    </div>
                    <div class="col-md-7 filter-tabs text-md-end">
                        <button class="btn active">Tất cả</button>
                        <button class="btn">Hoạt động</button>
                        <button class="btn">Nháp</button>
                        <button class="btn">Tạm dừng</button>
                        <button class="btn">Không hoạt động</button>
                    </div>
                </div>
            </div>
            
            <!-- Service List -->
            <div class="service-table-card">
                <div class="p-3 border-bottom bg-white">
                    <h5 class="mb-0" style="font-weight: 700;">Danh sách dịch vụ</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Dịch vụ</th>
                                <th class="text-center">Danh mục kĩ năng</th>
                                <th>Trạng thái dịch vụ</th>
                                <th class="text-end">Quản lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($services as $service): ?>
                            <tr>
                                <td>
                                    <div class="service-info">
                                        <img src="<?= htmlspecialchars($service['image']) ?>" 
                                             onerror="this.src='https://via.placeholder.com/60x40'" 
                                             class="service-img" alt="Service">
                                        <span style="font-weight: 600;"><?= htmlspecialchars($service['name']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="category-badge"><?= htmlspecialchars($service['category']) ?></span>
                                </td>
                                <td><?= getStatusBadge($service['status']) ?></td>
                                <td class="text-end">
                                    <i class="far fa-comment-alt action-btn" title="Đánh giá"></i>
                                    <i class="far fa-edit action-btn" title="Chỉnh sửa"></i>
                                    <i class="far fa-trash-alt action-btn" title="Xóa"></i>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php include '../dashboard/footer.php'; ?>
    </div>
</div>

<!-- Javascript -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../lib/chart/chart.min.js"></script>
<script src="../../lib/easing/easing.min.js"></script>
<script src="../../lib/waypoints/waypoints.min.js"></script>
<script src="../../lib/owlcarousel/owl.carousel.min.js"></script>
<script src="../../lib/tempusdominus/js/moment.min.js"></script>
<script src="../../lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="../../lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../dashboard/js/main.js"></script>
</body>
</html>
