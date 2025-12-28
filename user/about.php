<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy thông tin user nếu đã đăng nhập
$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();
?>

<!doctype html>
<html class="no-js" lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Về UniWork - AuraDisc</title>
    
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootsnav.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            color: #404145;
        }
        
        .section-padding {
            padding: 80px 0;
        }
        
        .bg-light {
            background-color: #f7f9fa;
        }
        
        .text-purple {
            color: #412D3B; /* Theme color */
        }
        
        /* Hero Section */
        .about-hero {
            padding: 150px 0 100px;
            background: linear-gradient(135deg, #f0f7ff 0%, #fff 100%);
            text-align: center;
            margin-top: 20px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #222;
        }
        
        .hero-title span {
            background: linear-gradient(90deg, #412D3B 0%, #9b59b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-desc {
            max-width: 800px;
            margin: 0 auto 40px;
            font-size: 1.2rem;
            line-height: 1.6;
            color: #666;
        }
        
        .btn-hero {
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 1rem;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: #4d61fc; /* Blue from image */
            color: white;
            border: none;
        }
        
        .btn-primary-custom:hover {
            background: #3b4cca;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline-custom {
            background: white;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-outline-custom:hover {
            border-color: #4d61fc;
            color: #4d61fc;
            transform: translateY(-2px);
        }
        
        /* Stats Section */
        .stats-section {
            padding: 60px 0;
            background: white;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #4d61fc;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        /* Mission Section */
        .mission-section {
            background: #fcfcfc;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #222;
        }
        
        .section-desc {
            max-width: 800px;
            margin: 0 auto;
            color: #666;
            font-size: 1.1rem;
        }
        
        .mission-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #eee;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .mission-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transform: translateY(-5px);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            background: #eef2ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: #4d61fc;
            font-size: 1.5rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #222;
        }
        
        .card-text {
            color: #666;
            line-height: 1.6;
        }
        
        /* Values Section */
        .values-section {
            background: linear-gradient(135deg, #4d61fc 0%, #a259ff 100%);
            color: white;
        }
        
        .values-section .section-title,
        .values-section .section-desc {
            color: white;
        }
        
        .value-item {
            text-align: center;
            padding: 20px;
        }
        
        .value-icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .value-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .value-text {
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta-section {
            text-align: center;
            background: white;
        }
        
        .cta-box {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #222;
        }
        
        .cta-text {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .btn-cta-primary {
            background: linear-gradient(90deg, #4d61fc 0%, #a259ff 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            box-shadow: 0 5px 15px rgba(77, 97, 252, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(77, 97, 252, 0.4);
            color: white;
        }
        
        .btn-cta-secondary {
            background: white;
            color: #404145;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.1rem;
            border: 1px solid #ddd;
            margin-left: 15px;
            transition: all 0.3s ease;
        }
        
        .btn-cta-secondary:hover {
            border-color: #404145;
            color: #222;
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

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1 class="hero-title">Về <span>UniWork</span></h1>
            <p class="hero-desc">
                UniWork là nền tảng freelance hàng đầu Việt Nam, kết nối những người có nhu cầu với cộng đồng sinh viên và freelancer tài năng. Chúng tôi tin rằng mọi kỹ năng đều có giá trị và xứng đáng được công nhận.
            </p>
            <div class="hero-actions">
                <a href="genre/genres.php" class="btn btn-hero btn-primary-custom">Khám phá dịch vụ</a>
                <a href="javascript:void(0)" onclick="checkFreelancerRegistration()" class="btn btn-hero btn-outline-custom">Trở thành Freelancer</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="lnr lnr-users"></i></div>
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Freelancer</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="lnr lnr-layers"></i></div>
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">Dự án hoàn thành</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="lnr lnr-star"></i></div>
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Đánh giá 5 sao</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="lnr lnr-chart-bars"></i></div>
                        <div class="stat-number">25%</div>
                        <div class="stat-label">Tăng trưởng hàng tháng</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section section-padding">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sứ mệnh của chúng tôi</h2>
                <p class="section-desc">Chúng tôi tin rằng công nghệ có thể tạo ra những cơ hội bình đẳng cho mọi người. UniWork được xây dựng với mục tiêu tạo ra một nền tảng minh bạch, an toàn và hiệu quả, nơi mà tài năng được công nhận và phát triển.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="mission-card">
                        <div class="card-icon"><i class="lnr lnr-shield"></i></div>
                        <h3 class="card-title">An toàn & Bảo mật</h3>
                        <p class="card-text">Hệ thống thanh toán an toàn, bảo vệ thông tin cá nhân và đảm bảo quyền lợi cho cả hai bên.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="mission-card">
                        <div class="card-icon"><i class="lnr lnr-users"></i></div>
                        <h3 class="card-title">Cộng đồng chất lượng</h3>
                        <p class="card-text">Hơn 10,000 freelancer tài năng được xác minh và đánh giá bởi cộng đồng.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="mission-card">
                        <div class="card-icon"><i class="lnr lnr-magnifier"></i></div>
                        <h3 class="card-title">Dễ dàng tìm kiếm</h3>
                        <p class="card-text">Công cụ tìm kiếm thông minh giúp bạn nhanh chóng tìm được freelancer phù hợp.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="mission-card">
                        <div class="card-icon"><i class="lnr lnr-license"></i></div>
                        <h3 class="card-title">Chất lượng đảm bảo</h3>
                        <p class="card-text">Hệ thống đánh giá minh bạch và chính sách hoàn tiền nếu không hài lòng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section section-padding">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Giá trị cốt lõi</h2>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon-circle"><i class="lnr lnr-heart"></i></div>
                        <h3 class="value-title">Tận tâm</h3>
                        <p class="value-text">Chúng tôi luôn đặt lợi ích của người dùng lên hàng đầu và không ngừng cải thiện dịch vụ.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon-circle"><i class="lnr lnr-checkmark-circle"></i></div>
                        <h3 class="value-title">Minh bạch</h3>
                        <p class="value-text">Mọi giao dịch và đánh giá đều được thực hiện một cách công khai và minh bạch.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon-circle"><i class="lnr lnr-diamond"></i></div>
                        <h3 class="value-title">Chất lượng</h3>
                        <p class="value-text">Chúng tôi cam kết mang đến những dịch vụ và trải nghiệm tốt nhất cho người dùng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section section-padding">
        <div class="container">
            <div class="cta-box">
                <h2 class="cta-title">Sẵn sàng bắt đầu?</h2>
                <p class="cta-text">Tham gia cộng đồng UniWork ngay hôm nay và khám phá những cơ hội tuyệt vời đang chờ đợi bạn.</p>
                <div class="cta-actions">
                    <a href="genre/genres.php" class="btn-cta-primary">Tìm freelancer</a>
                    <a href="javascript:void(0)" onclick="checkFreelancerRegistration()" class="btn-cta-secondary">Đăng ký làm freelancer</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/custom.js"></script>
</body>
</html>

