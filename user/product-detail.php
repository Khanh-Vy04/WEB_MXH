<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy thông tin user nếu đã đăng nhập
$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();

// Lấy tham số từ URL
$type = isset($_GET['type']) ? $_GET['type'] : 'product'; 
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$product_data = null;
$artist_info = null;
$reviews = [];

// Logic lấy dữ liệu (Giữ nguyên logic cũ để không lỗi, nhưng hiển thị sẽ khác)
if ($type === 'product') {
    $sql = "SELECT p.*, g.genre_name FROM products p LEFT JOIN genres g ON p.genre_id = g.genre_id WHERE p.product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product_data = $result->fetch_assoc();
        
        // Lấy thông tin nghệ sĩ (Sẽ dùng làm Freelancer Info)
        $artist_sql = "SELECT a.* FROM artists a JOIN artist_products ap ON a.artist_id = ap.artist_id WHERE ap.product_id = ?";
        $artist_stmt = $conn->prepare($artist_sql);
        $artist_stmt->bind_param("i", $id);
        $artist_stmt->execute();
        $artist_result = $artist_stmt->get_result();
        if ($artist_result->num_rows > 0) {
            $artist_info = $artist_result->fetch_assoc();
        }
        
        // Lấy đánh giá
        $review_sql = "SELECT r.*, u.username, u.full_name FROM reviews r JOIN users u ON r.buyer_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC LIMIT 5";
        $review_stmt = $conn->prepare($review_sql);
        $review_stmt->bind_param("i", $id);
        $review_stmt->execute();
        $review_result = $review_stmt->get_result();
        while ($row = $review_result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }
} else {
    // Fallback cho accessory (nếu cần)
    $sql = "SELECT * FROM accessories WHERE accessory_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product_data = $result->fetch_assoc();
        $product_data['product_name'] = $product_data['accessory_name'];
        $product_data['product_id'] = $product_data['accessory_id'];
    }
}

if (!$product_data) {
    header("Location: index.php");
    exit();
}

// Mock Data cho giao diện mới (Nếu chưa có trong DB)
$rating = 4.9;
$review_count = count($reviews) > 0 ? count($reviews) : 18; // Fake nếu không có review thật
$freelancer_name = $artist_info ? $artist_info['artist_name'] : "Designer UI/UX";
$freelancer_title = "Freelancer chuyên nghiệp";
$freelancer_avatar = $artist_info ? $artist_info['image_url'] : "assets/images/clients/c1.png";

?>

<!doctype html>
<html class="no-js" lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?php echo htmlspecialchars($product_data['product_name']); ?> - AuraDisc Service</title>
    
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootsnav.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        body {
            background-color: #f5f7f9; /* Màu nền nhẹ nhàng */
            color: #404145;
            font-family: 'Roboto', sans-serif;
        }

        .service-detail-container {
            padding: 120px 0 60px;
        }

        /* Header Info */
        .service-breadcrumb {
            color: #74767e;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .service-breadcrumb a {
            color: #74767e;
            text-decoration: none;
        }
        
        .service-breadcrumb .rating-badge {
            color: #ffb33e;
            font-weight: bold;
            margin-left: 10px;
        }

        .service-title {
            font-size: 28px;
            font-weight: 700;
            color: #222325;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        /* Freelancer Card */
        .freelancer-card {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e4e5e7;
            margin-bottom: 30px;
        }

        .freelancer-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .freelancer-info {
            flex: 1;
        }

        .freelancer-name {
            font-size: 16px;
            font-weight: 700;
            color: #222325;
            margin-bottom: 4px;
        }

        .freelancer-title {
            font-size: 14px;
            color: #74767e;
        }

        .freelancer-badges {
            margin-top: 5px;
            font-size: 12px;
            color: #74767e;
        }
        
        .freelancer-badges i {
            margin-right: 4px;
        }
        
        .badge-top-rated {
            color: #ffb33e;
            margin-right: 10px;
            font-weight: 600;
        }

        .badge-verified {
            color: #1dbf73;
            font-weight: 600;
        }

        .btn-contact {
            background: #222325;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-contact:hover {
            background: #404145;
            color: #fff;
            text-decoration: none;
        }

        /* Product Gallery */
        .product-gallery {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e4e5e7;
        }

        .product-main-img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Description Section */
        .content-box {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid #e4e5e7;
            margin-bottom: 30px;
        }

        .content-title {
            font-size: 20px;
            font-weight: 600;
            color: #222325;
            margin-bottom: 16px;
        }

        .content-text {
            font-size: 16px;
            line-height: 1.6;
            color: #404145;
        }

        /* Skills Tags */
        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-tag {
            background: #f4f6f8;
            color: #74767e;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            border: 1px solid #e4e5e7;
        }

        /* Right Column - Packages */
        .package-sidebar {
            position: sticky;
            top: 100px;
        }

        .package-card {
            background: #fff;
            border: 1px solid #dadbdd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .package-tabs {
            display: flex;
            border-bottom: 1px solid #dadbdd;
        }

        .package-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            background: #fafafa;
            color: #74767e;
            font-weight: 600;
            font-size: 14px;
            border-right: 1px solid #dadbdd;
        }

        .package-tab:last-child {
            border-right: none;
        }

        .package-tab.active {
            background: #fff;
            color: #222325;
            border-bottom: 3px solid #1dbf73; /* Green Fiverr-like color */
        }

        .package-content {
            padding: 24px;
        }

        .package-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .package-name {
            font-weight: 700;
            font-size: 16px;
        }

        .package-price {
            font-size: 24px;
            font-weight: 400;
            color: #222325;
        }

        .package-desc {
            font-size: 14px;
            color: #62646a;
            margin-bottom: 20px;
            min-height: 40px;
        }

        .package-features {
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: #62646a;
        }
        
        .feature-item i {
            color: #b5b6ba;
            margin-right: 8px;
        }
        
        .feature-item.checked i {
            color: #1dbf73;
        }

        .package-meta {
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
            color: #222325;
        }
        
        .package-meta i {
            margin-right: 8px;
            color: #62646a;
        }

        .btn-hire {
            display: block;
            width: 100%;
            padding: 12px;
            background: #222325; /* Đen */
            color: #fff;
            text-align: center;
            border-radius: 4px;
            font-weight: 700;
            border: none;
            font-size: 16px;
            transition: all 0.2s;
        }

        .btn-hire:hover {
            background: #404145;
            text-decoration: none;
            color: white;
        }

        .quick-stats {
            background: #fff;
            border: 1px solid #dadbdd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .rating-box {
            text-align: center;
            background: #f4f8ff;
            padding: 15px;
            border-radius: 8px;
            width: 48%;
        }
        
        .rating-num {
            font-size: 24px;
            font-weight: 700;
            color: #2c65cf;
        }

        .review-count-box {
            text-align: center;
            background: #ecfdf5;
            padding: 15px;
            border-radius: 8px;
            width: 48%;
        }
        
        .review-num {
            font-size: 24px;
            font-weight: 700;
            color: #15803d;
        }

        /* Review Item */
        .review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .hidden {
            display: none;
        }

    </style>
</head>

<body>
    <!-- Navigation -->
    <div class="top-area">
        <div class="header-area">
            <?php include 'includes/navigation.php'; ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <section class="service-detail-container">
        <div class="container">
            <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-lg-8 col-md-12">
                    
                    <!-- 1. Header Info -->
                    <div class="service-breadcrumb">
                        <span>UI/UX Design</span> 
                        <span class="rating-badge"><i class="fa fa-star"></i> <?php echo $rating; ?></span> 
                        <span class="text-muted">(<?php echo $review_count; ?> đánh giá)</span>
                    </div>

                    <!-- 2. Service Title -->
                    <h1 class="service-title"><?php echo htmlspecialchars($product_data['product_name']); ?></h1>

                    <!-- 3. Freelancer Profile -->
                    <div class="freelancer-card">
                        <img src="<?php echo htmlspecialchars($freelancer_avatar); ?>" alt="Freelancer" class="freelancer-avatar">
                        <div class="freelancer-info">
                            <div class="freelancer-name"><?php echo htmlspecialchars($freelancer_name); ?></div>
                            <div class="freelancer-title"><?php echo $freelancer_title; ?></div>
                            <div class="freelancer-badges">
                                <span class="badge-top-rated"><i class="fa fa-diamond"></i> Top Rated</span>
                                <span class="badge-verified"><i class="fa fa-check-circle"></i> Đã xác minh</span>
                            </div>
                        </div>
                        <a href="chat.php?product_id=<?php echo $id; ?>" class="btn-contact"><i class="fa fa-comment"></i> Liên hệ ngay</a>
                    </div>

                    <!-- 4. Product Images -->
                    <div class="product-gallery">
                        <img src="<?php echo htmlspecialchars($product_data['image_url']); ?>" alt="Service Image" class="product-main-img">
                    </div>

                    <!-- 5. Description -->
                    <div class="content-box">
                        <h3 class="content-title">Mô tả chi tiết dịch vụ</h3>
                        <div class="content-text">
                            <?php echo nl2br(htmlspecialchars($product_data['description'])); ?>
                            <br><br>
                            <p><strong>Quy trình làm việc:</strong></p>
                            <ul>
                                <li>Phân tích yêu cầu và nghiên cứu người dùng</li>
                                <li>Xây dựng Wireframe và User Flow</li>
                                <li>Thiết kế UI trực quan (High-fidelity)</li>
                                <li>Tạo Prototype tương tác</li>
                                <li>Bàn giao file thiết kế (Figma/Sketch)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 6. Skills -->
                    <div class="content-box">
                        <h3 class="content-title">Kỹ năng & Công nghệ</h3>
                        <div class="skills-tags">
                            <span class="skill-tag">UI Design</span>
                            <span class="skill-tag">UX Research</span>
                            <span class="skill-tag">Mobile App</span>
                            <span class="skill-tag">Figma</span>
                            <span class="skill-tag">Prototyping</span>
                            <span class="skill-tag">Design System</span>
                        </div>
                    </div>

                    <!-- 7. Reviews -->
                    <div class="content-box">
                        <h3 class="content-title">Đánh giá từ khách hàng</h3>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                            <div class="review-item border-bottom pb-3 mb-3">
                                <div class="review-header">
                                    <img src="assets/img/default-user.png" onerror="this.src='https://via.placeholder.com/40'" class="review-avatar">
                                    <div>
                                        <div class="font-weight-bold"><?php echo htmlspecialchars($review['full_name']); ?></div>
                                        <div class="text-warning">
                                            <?php 
                                            for($i=0; $i<5; $i++) {
                                                echo $i < $review['rating'] ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
                                            }
                                            ?>
                                            <span class="text-muted small ml-2"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa fa-star-o fa-3x text-muted mb-3"></i>
                                <p>Chưa có đánh giá nào. Hãy là người đầu tiên trải nghiệm dịch vụ!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- RIGHT COLUMN (Sticky Sidebar) -->
                <div class="col-lg-4 col-md-12">
                    <div class="package-sidebar">
                        
                        <!-- Package Selector -->
                        <div class="package-card">
                            <div class="package-tabs">
                                <div class="package-tab active" onclick="switchPackage('basic')">Cơ bản</div>
                                <div class="package-tab" onclick="switchPackage('standard')">Tiêu chuẩn</div>
                                <div class="package-tab" onclick="switchPackage('premium')">Cao cấp</div>
                            </div>
                            
                            <!-- Basic Package -->
                            <div id="pkg-basic" class="package-content">
                                <div class="package-header">
                                    <span class="package-name">Gói Cơ bản</span>
                                    <span class="package-price"><?php echo number_format($product_data['price']); ?>đ</span>
                                </div>
                                <div class="package-desc">Thiết kế 1-2 màn hình chính, phù hợp cho việc lên ý tưởng ban đầu.</div>
                                <div class="package-meta">
                                    <div class="mb-2"><i class="fa fa-clock-o"></i> 3 ngày giao hàng</div>
                                    <div><i class="fa fa-refresh"></i> 2 lần chỉnh sửa</div>
                                </div>
                                <div class="package-features">
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Source File</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> High Resolution</div>
                                    <div class="feature-item"><i class="fa fa-check"></i> Prototype</div>
                                    <div class="feature-item"><i class="fa fa-check"></i> Commercial Use</div>
                                </div>
                                <button class="btn-hire">Chọn gói này</button>
                            </div>

                            <!-- Standard Package -->
                            <div id="pkg-standard" class="package-content hidden">
                                <div class="package-header">
                                    <span class="package-name">Gói Tiêu chuẩn</span>
                                    <span class="package-price"><?php echo number_format($product_data['price'] * 2.5); ?>đ</span>
                                </div>
                                <div class="package-desc">Thiết kế tối đa 10 màn hình, bao gồm Wireframe và UI Design hoàn chỉnh.</div>
                                <div class="package-meta">
                                    <div class="mb-2"><i class="fa fa-clock-o"></i> 7 ngày giao hàng</div>
                                    <div><i class="fa fa-refresh"></i> 5 lần chỉnh sửa</div>
                                </div>
                                <div class="package-features">
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Source File</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> High Resolution</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Prototype</div>
                                    <div class="feature-item"><i class="fa fa-check"></i> Commercial Use</div>
                                </div>
                                <button class="btn-hire">Chọn gói này</button>
                            </div>

                            <!-- Premium Package -->
                            <div id="pkg-premium" class="package-content hidden">
                                <div class="package-header">
                                    <span class="package-name">Gói Cao cấp</span>
                                    <span class="package-price"><?php echo number_format($product_data['price'] * 5); ?>đ</span>
                                </div>
                                <div class="package-desc">Thiết kế Full App (lên đến 25 màn hình), Prototype tương tác, Design System đầy đủ.</div>
                                <div class="package-meta">
                                    <div class="mb-2"><i class="fa fa-clock-o"></i> 14 ngày giao hàng</div>
                                    <div><i class="fa fa-refresh"></i> Không giới hạn</div>
                                </div>
                                <div class="package-features">
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Source File</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> High Resolution</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Prototype</div>
                                    <div class="feature-item checked"><i class="fa fa-check"></i> Commercial Use</div>
                                </div>
                                <button class="btn-hire">Chọn gói này</button>
                            </div>
                        </div>

                
                        

                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        function switchPackage(pkg) {
            // Hide all
            document.getElementById('pkg-basic').classList.add('hidden');
            document.getElementById('pkg-standard').classList.add('hidden');
            document.getElementById('pkg-premium').classList.add('hidden');
            
            // Remove active tab
            var tabs = document.querySelectorAll('.package-tab');
            tabs.forEach(t => t.classList.remove('active'));

            // Show selected
            document.getElementById('pkg-' + pkg).classList.remove('hidden');
            
            // Set active tab (simple logic based on order or text, here explicit for demo)
            if(pkg === 'basic') tabs[0].classList.add('active');
            if(pkg === 'standard') tabs[1].classList.add('active');
            if(pkg === 'premium') tabs[2].classList.add('active');
        }
    </script>
</body>
</html>
