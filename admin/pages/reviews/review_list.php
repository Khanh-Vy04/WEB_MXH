<?php
$currentPage = 'reviews';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Mock Data
$stats = [
    'average_rating' => 4.8,
    'total_reviews' => 127,
    'referral_rate' => 95,
    'trend' => 'up'
];

$distribution = [
    5 => 100,
    4 => 20,
    3 => 5,
    2 => 1,
    1 => 1
];

$reviews = [
    [
        'id' => 101,
        'service_name' => 'Thiết kế Logo chuyên nghiệp',
        'project_id' => 'PJ-2023-001',
        'customer_name' => 'Nguyễn Văn A',
        'avatar' => '/WEB_MXH/user/assets/images/collection/client1.png',
        'rating' => 5,
        'comment' => 'Dịch vụ rất tốt, thiết kế đẹp và nhanh chóng. Sẽ ủng hộ lần sau!',
        'date' => '2023-10-25',
        'status' => 'replied', // replied, pending
        'reply' => 'Cảm ơn bạn đã tin tưởng sử dụng dịch vụ!'
    ],
    [
        'id' => 102,
        'service_name' => 'Lập trình Web trọn gói',
        'project_id' => 'PJ-2023-005',
        'customer_name' => 'Trần Thị B',
        'avatar' => '/WEB_MXH/user/assets/images/collection/client2.png',
        'rating' => 4,
        'comment' => 'Web chạy ổn, nhưng cần tối ưu thêm mobile. Freelancer nhiệt tình.',
        'date' => '2023-10-24',
        'status' => 'pending',
        'reply' => ''
    ],
    [
        'id' => 103,
        'service_name' => 'Viết bài SEO chuẩn',
        'project_id' => 'PJ-2023-012',
        'customer_name' => 'Lê Văn C',
        'avatar' => '/WEB_MXH/user/assets/images/collection/client3.png',
        'rating' => 5,
        'comment' => 'Bài viết chất lượng, chuẩn SEO, đúng deadline. Rất hài lòng.',
        'date' => '2023-10-23',
        'status' => 'replied',
        'reply' => 'Cảm ơn anh C, mong được hợp tác lâu dài!'
    ],
    [
        'id' => 104,
        'service_name' => 'Thiết kế Banner quảng cáo',
        'project_id' => 'PJ-2023-015',
        'customer_name' => 'Phạm Thị D',
        'avatar' => '/WEB_MXH/user/assets/images/collection/client4.png',
        'rating' => 3,
        'comment' => 'Thiết kế tạm ổn, nhưng sửa hơi lâu. Cần cải thiện tốc độ.',
        'date' => '2023-10-20',
        'status' => 'pending',
        'reply' => ''
    ],
    [
        'id' => 105,
        'service_name' => 'Edit Video Tiktok',
        'project_id' => 'PJ-2023-018',
        'customer_name' => 'Hoàng Văn E',
        'avatar' => '/WEB_MXH/user/assets/images/collection/client2.png',
        'rating' => 5,
        'comment' => 'Video edit cực cuốn, bắt trend tốt. 10 điểm!',
        'date' => '2023-10-18',
        'status' => 'replied',
        'reply' => 'Thanks bạn nhiều nha <3'
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quản lý Đánh giá - UniWork</title>
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
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            height: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
        }
        
        .stat-icon-wrapper {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 0;
            line-height: 1.2;
        }
        
        .stat-desc {
            font-size: 0.8rem;
            color: #adb5bd;
        }
        
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
        }
        
        .progress-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-label {
            width: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #2d3436;
        }
        
        .progress-wrapper {
            flex-grow: 1;
            margin: 0 15px;
            height: 8px;
            background-color: #f1f3f5;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar {
            background-color: #adb5bd;
            height: 100%;
            border-radius: 4px;
        }
        
        .progress-count {
            width: 30px;
            text-align: right;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #f0f0f0;
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
        
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .customer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            background: #f0f0f0;
        }
        
        .btn-reply {
            background: #2d3436;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-reply:hover {
            background: #000;
            color: white;
            transform: translateY(-1px);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-replied {
            background-color: #e6fcf5;
            color: #0ca678;
        }
        
        .status-pending {
            background-color: #fff4e6;
            color: #f76707;
        }
        
        .rating-stars {
            color: #fcc419;
            font-size: 0.8rem;
            margin-bottom: 3px;
        }
        /* Specific badge style for project IDs only */
        .badge-project-id {
            background-color: #deccca !important;
            color: #412d3b !important;
            border-color: transparent !important;
            font-weight: 600;
        }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include '../dashboard/sidebar.php'; ?>
    
    <div class="content">
        <?php include '../dashboard/navbar.php'; ?>
        
        <div class="container-fluid pt-4 px-4 pb-5">
            <h3 class="mb-1" style="color: #2d3436; font-weight: 700;">Đánh giá</h3>
            <p class="text-muted mb-4">Quản lý và xem đánh giá của bạn</p>
            
            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper text-warning">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-label">Đánh giá trung bình</div>
                        <div class="stat-number">4.8 <span class="text-muted fw-normal fs-6">/5.0</span></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper text-primary">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-label">Tổng đánh giá</div>
                        <div class="stat-number"><?= $stats['total_reviews'] ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper text-success">
                            <i class="far fa-thumbs-up"></i>
                        </div>
                        <div class="stat-label">Tỷ lệ giới thiệu</div>
                        <div class="stat-number"><?= $stats['referral_rate'] ?>%</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper text-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-label">Xu hướng</div>
                        <div class="stat-number text-success"><i class="fas fa-arrow-up" style="font-size: 1.5rem;"></i></div>
                    </div>
                </div>
            </div>
            
            <!-- Distribution -->
            <div class="chart-card">
                <h5 class="mb-4 fw-bold">Phân bố đánh giá</h5>
                <?php 
                $max_count = max($distribution); 
                for($i = 5; $i >= 1; $i--): 
                    $percent = ($distribution[$i] / $stats['total_reviews']) * 100; // rough calc for visual
                ?>
                <div class="progress-row">
                    <div class="progress-label"><?= $i ?> <i class="fas fa-star text-warning" style="font-size: 0.8rem;"></i></div>
                    <div class="progress-wrapper">
                        <div class="progress-bar" style="width: <?= $percent ?>%; background-color: #dee2e6;"></div>
                    </div>
                    <div class="progress-count"><?= $distribution[$i] ?></div>
                </div>
                <?php endfor; ?>
            </div>
            
            <!-- Filter & Action -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-outline-secondary btn-sm bg-white" style="border-radius: 6px;">
                    <i class="fas fa-filter me-2"></i> Làm mới
                </button>
            </div>
            
            <!-- Review List Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Tên dịch vụ</th>
                                <th>ID dự án</th>
                                <th>Tên khách hàng</th>
                                <th>Đánh giá</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reviews as $review): ?>
                            <tr>
                                <td style="max-width: 250px;">
                                    <div class="fw-bold text-truncate" title="<?= htmlspecialchars($review['service_name']) ?>">
                                        <?= htmlspecialchars($review['service_name']) ?>
                                    </div>
                                    <small class="text-muted"><?= $review['date'] ?></small>
                                </td>
                                <td><span class="badge badge-project-id border"><?= $review['project_id'] ?></span></td>
                                <td>
                                    <div class="customer-info">
                                        <img src="<?= htmlspecialchars($review['avatar']) ?>" 
                                             onerror="this.src='https://via.placeholder.com/36'" 
                                             class="customer-avatar" alt="Avatar">
                                        <span class="fw-bold"><?= htmlspecialchars($review['customer_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        <?php for($k=1; $k<=5; $k++): ?>
                                            <i class="<?= $k <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($review['comment']) ?>">
                                        "<?= htmlspecialchars($review['comment']) ?>"
                                    </small>
                                </td>
                                <td>
                                    <?php if($review['status'] == 'replied'): ?>
                                        <span class="status-badge status-replied">
                                            <i class="fas fa-check-circle"></i> Đã phản hồi
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock"></i> Chưa phản hồi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($review['status'] == 'pending'): ?>
                                        <button class="btn-reply">Phản hồi</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light border" disabled>Đã xong</button>
                                    <?php endif; ?>
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
