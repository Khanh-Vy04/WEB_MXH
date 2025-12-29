<?php
$currentPage = 'content';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Ensure SuperAdmin
if (!isSuperAdmin()) {
    header("Location: /WEB_MXH/login.php");
    exit;
}

// Mock Data: Categories
$categories = [
    1 => ['id' => 1, 'name' => 'Thiết kế đồ họa', 'icon' => 'fa-paint-brush', 'service_count' => 150, 'status' => 'active'],
    2 => ['id' => 2, 'name' => 'Lập trình & CNTT', 'icon' => 'fa-code', 'service_count' => 120, 'status' => 'active'],
    3 => ['id' => 3, 'name' => 'Viết lách & Dịch thuật', 'icon' => 'fa-pen-nib', 'service_count' => 85, 'status' => 'active'],
    4 => ['id' => 4, 'name' => 'Video & Âm thanh', 'icon' => 'fa-video', 'service_count' => 60, 'status' => 'active'],
    5 => ['id' => 5, 'name' => 'Digital Marketing', 'icon' => 'fa-bullhorn', 'service_count' => 90, 'status' => 'hidden']
];

// Mock Data: Services (Linked to Category ID)
$allServices = [
    1 => [
        ['id' => 101, 'name' => 'Thiết kế Logo chuyên nghiệp', 'freelancer' => 'Nguyen Van A', 'price' => '500.000', 'rating' => 4.8],
        ['id' => 102, 'name' => 'Thiết kế Banner quảng cáo', 'freelancer' => 'Tran Thi B', 'price' => '300.000', 'rating' => 4.5],
        ['id' => 103, 'name' => 'Vẽ minh họa 2D', 'freelancer' => 'Le Van C', 'price' => '1.200.000', 'rating' => 5.0]
    ],
    2 => [
        ['id' => 201, 'name' => 'Lập trình Web trọn gói', 'freelancer' => 'Dev Pro', 'price' => '5.000.000', 'rating' => 4.9],
        ['id' => 202, 'name' => 'Fix bug PHP/Laravel', 'freelancer' => 'Code Master', 'price' => '500.000', 'rating' => 4.7]
    ]
];

// Determine View Mode
$viewMode = 'categories';
$selectedCategory = null;
$categoryServices = [];

if (isset($_GET['category_id']) && isset($categories[$_GET['category_id']])) {
    $viewMode = 'services';
    $catId = $_GET['category_id'];
    $selectedCategory = $categories[$catId];
    $categoryServices = $allServices[$catId] ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quản lý Nội dung/Dịch vụ - UniWork SuperAdmin</title>
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
        .action-btn { cursor: pointer; margin: 0 5px; font-size: 1.1rem; border: none; background: transparent; }
        .btn-view { color: #000; }
        .btn-edit { color: #000; }
        .btn-delete { color: #412D3B; }
        .cat-icon { width: 40px; height: 40px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #2d3436; }
        .clickable-row:hover { background-color: #f8f9fa; cursor: pointer; }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <div class="content">
            <!-- Navbar -->
            <?php include '../../includes/navbar.php'; ?>

            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    
                    <?php if ($viewMode == 'categories'): ?>
                        <!-- CATEGORIES VIEW -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="mb-0">Danh sách Danh mục Kĩ năng</h6>
                            <button class="btn btn-primary btn-sm"><i class="fa fa-plus me-2"></i>Thêm danh mục</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-white">
                                        <th scope="col">Icon</th>
                                        <th scope="col">Tên danh mục</th>
                                        <th scope="col" class="text-center">Số lượng dịch vụ</th>
                                        <th scope="col" class="text-center">Trạng thái</th>
                                        <th scope="col" class="text-end">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories as $cat): ?>
                                    <tr class="clickable-row" onclick="window.location.href='?category_id=<?= $cat['id'] ?>'">
                                        <td>
                                            <div class="cat-icon"><i class="fa <?= $cat['icon'] ?>"></i></div>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td class="text-center"><span class="badge bg-light text-dark"><?= $cat['service_count'] ?></span></td>
                                        <td class="text-center">
                                            <?php if($cat['status'] == 'active'): ?>
                                                <span class="badge bg-success">Hiển thị</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <button class="action-btn btn-edit" title="Sửa"><i class="fa fa-edit"></i></button>
                                            <button class="action-btn btn-delete" title="Xóa" onclick="return confirm('Xóa danh mục này?')"><i class="fa fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    
                    <?php else: ?>
                        <!-- SERVICES VIEW -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="fa fa-arrow-left"></i></a>
                                <h6 class="mb-0">Dịch vụ thuộc: <span class="text-primary"><?= htmlspecialchars($selectedCategory['name']) ?></span></h6>
                            </div>
                            <button class="btn btn-outline-primary btn-sm">Quản lý danh mục này</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-white">
                                        <th scope="col">ID</th>
                                        <th scope="col">Tên dịch vụ</th>
                                        <th scope="col">Freelancer</th>
                                        <th scope="col">Giá (VND)</th>
                                        <th scope="col" class="text-center">Đánh giá</th>
                                        <th scope="col" class="text-end">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($categoryServices) > 0): ?>
                                        <?php foreach($categoryServices as $service): ?>
                                        <tr>
                                            <td>#<?= $service['id'] ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($service['name']) ?></td>
                                            <td><?= htmlspecialchars($service['freelancer']) ?></td>
                                            <td><?= $service['price'] ?></td>
                                            <td class="text-center text-warning">
                                                <?= $service['rating'] ?> <i class="fa fa-star"></i>
                                            </td>
                                            <td class="text-end">
                                                <button class="action-btn btn-view" title="Xem chi tiết"><i class="fa fa-eye"></i></button>
                                                <button class="action-btn btn-delete" title="Gỡ dịch vụ" onclick="return confirm('Gỡ dịch vụ này?')"><i class="fa fa-ban"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">Chưa có dịch vụ nào trong danh mục này</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

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

