<?php
$currentPage = 'product_list';
require_once '../../../../config/database.php';
require_once '../../../../includes/session.php';

// Mock Data for Projects (Synchronized with user request)
$projects = [
    [
        'id' => 'PRJ-2025-001',
        'name' => 'Thiết kế Logo nhận diện thương hiệu',
        'price' => 500000,
        'deadline' => '2025-12-30',
        'status' => 'ongoing',
        'quote_status' => 'quoted', // unquoted, quoted
        'customer' => 'Nguyễn Văn A',
        'chat_id' => 690662066 // Example ID
    ],
    [
        'id' => 'PRJ-2025-002',
        'name' => 'Lập trình Website bán hàng',
        'price' => 5000000,
        'deadline' => '2026-01-15',
        'status' => 'ongoing',
        'quote_status' => 'unquoted',
        'customer' => 'Trần Thị B',
        'chat_id' => 0
    ]
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Dự án của tôi - UniWork</title>
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
    <link href="../../../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="../../../lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../../dashboard/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../../dashboard/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .project-tabs {
            background: white;
            padding: 15px 20px 0;
            border-radius: 10px 10px 0 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .nav-tabs {
            border-bottom: none;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 10px 20px;
            margin-right: 5px;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: #412d3b;
            background: transparent;
        }
        
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #412d3b;
            border-radius: 3px 3px 0 0;
        }
        
        .nav-tabs .nav-link:hover {
            color: #412d3b;
        }
        
        .project-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .project-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4e54c8;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-quote-no {
            background-color: #ffebee;
            color: #ef4444;
        }
        
        .badge-quote-yes {
            background-color: #ecfdf5;
            color: #10b981;
        }
        
        .text-price {
            color: #412d3b;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
        }
        
        .btn-chat {
            background: linear-gradient(135deg, #412d3b 0%, #deccca 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-chat:hover {
            background: linear-gradient(135deg, #6c4a57 0%, #412d3b 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(65, 45, 59, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #adb5bd;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #e9ecef;
        }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include '../../dashboard/sidebar.php'; ?>
    
    <div class="content">
        <?php include '../../dashboard/navbar.php'; ?>
        
        <div class="container-fluid pt-4 px-4">
            <h3 class="mb-4" style="color: #412d3b;">Quản lý dự án</h3>
            
            <div class="project-tabs">
                <ul class="nav nav-tabs" id="projectTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab">
                            Đang thực hiện (<?= count($projects) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                            Dự án đã hoàn thành (0)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="complaints-tab" data-bs-toggle="tab" data-bs-target="#complaints" type="button" role="tab">
                            Khiếu nại (0)
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content" id="projectTabsContent">
                <!-- Ongoing Projects Tab -->
                <div class="tab-pane fade show active" id="ongoing" role="tabpanel">
                    <?php if (empty($projects)): ?>
                        <div class="empty-state bg-white rounded">
                            <i class="far fa-folder-open"></i>
                            <p>Chưa có dự án nào đang thực hiện</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($projects as $project): ?>
                        <div class="project-card">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="project-icon">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1" style="color: #412d3b; font-weight: 700;"><?= htmlspecialchars($project['name']) ?></h5>
                                            <div class="d-flex gap-3 align-items-center">
                                                <small class="text-muted"><i class="fas fa-barcode me-1"></i> <?= $project['id'] ?></small>
                                                <?php if($project['quote_status'] == 'unquoted'): ?>
                                                    <span class="badge-status badge-quote-no"><i class="fas fa-exclamation-circle me-1"></i> Chưa báo giá</span>
                                                <?php else: ?>
                                                    <span class="badge-status badge-quote-yes"><i class="fas fa-check-circle me-1"></i> Đã báo giá</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 mb-3 mb-md-0">
                                    <div class="info-label">Số tiền</div>
                                    <div class="text-price"><?= number_format($project['price']) ?> VNĐ</div>
                                </div>
                                
                                <div class="col-md-2 mb-3 mb-md-0">
                                    <div class="info-label">Hạn chót</div>
                                    <div class="info-value text-danger">
                                        <i class="far fa-clock me-1"></i> <?= date('d/m/Y', strtotime($project['deadline'])) ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-md-end">
                                    <div class="info-label mb-1">Thao tác</div>
                                    <a href="../../customer_support/message/message.php?id=<?= $project['chat_id'] ?>" class="btn-chat text-decoration-none d-inline-block">
                                        <i class="far fa-comments me-1"></i> Chat Support
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">Khách hàng: <strong><?= htmlspecialchars($project['customer']) ?></strong></small>
                                <div class="progress" style="width: 200px; height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Completed Projects Tab -->
                <div class="tab-pane fade" id="completed" role="tabpanel">
                    <div class="empty-state bg-white rounded">
                        <i class="fas fa-check-circle"></i>
                        <p>Chưa có dự án nào hoàn thành</p>
                    </div>
                </div>
                
                <!-- Complaints Tab -->
                <div class="tab-pane fade" id="complaints" role="tabpanel">
                    <div class="empty-state bg-white rounded">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không có khiếu nại nào</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include '../../dashboard/footer.php'; ?>
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
<script src="../../dashboard/js/main.js"></script>
</body>
</html>
