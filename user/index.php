<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy thông tin user nếu đã đăng nhập (không bắt buộc)
$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();

// Lấy 4 sản phẩm mới nhất
$sql_new_products = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result_new_products = $conn->query($sql_new_products);
$new_products = [];
if ($result_new_products->num_rows > 0) {
    while($row = $result_new_products->fetch_assoc()) {
        $new_products[] = $row;
    }
}

// Lấy 4 dòng nhạc (Genres)
$sql_genres = "SELECT * FROM genres ORDER BY genre_id DESC LIMIT 4";
$result_genres = $conn->query($sql_genres);
$genres = [];
if ($result_genres->num_rows > 0) {
    while($row = $result_genres->fetch_assoc()) {
        $genres[] = $row;
    }
}

// Lấy voucher khả dụng
if ($is_logged_in) {
    $sql_vouchers = "SELECT v.*, 
    CASE WHEN uv.user_voucher_id IS NOT NULL THEN 1 ELSE 0 END as user_owned
    FROM vouchers v 
    LEFT JOIN user_vouchers uv ON v.voucher_id = uv.voucher_id AND uv.user_id = ?
    WHERE v.is_active = 1 
    AND v.start_date <= CURDATE() 
    AND v.end_date >= CURDATE()
    AND (v.usage_limit IS NULL OR v.used_count < v.usage_limit)
    ORDER BY v.created_at DESC";

    $user_id = $current_user['user_id'];
    $stmt_vouchers = $conn->prepare($sql_vouchers);
    $stmt_vouchers->bind_param("i", $user_id);
    $stmt_vouchers->execute();
    $result_vouchers = $stmt_vouchers->get_result();
} else {
    $sql_vouchers = "SELECT *, 0 as user_owned
        FROM vouchers 
        WHERE is_active = 1 
        AND start_date <= CURDATE() 
        AND end_date >= CURDATE()
        AND (usage_limit IS NULL OR used_count < usage_limit)
        ORDER BY created_at DESC";
    $result_vouchers = $conn->query($sql_vouchers);
}

$vouchers = [];
if ($result_vouchers && $result_vouchers->num_rows > 0) {
    while($row = $result_vouchers->fetch_assoc()) {
        $vouchers[] = $row;
    }
}
?>

<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>UniWork - Nền tảng Freelancer cho sinh viên</title>
		<link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		<link rel="stylesheet" href="assets/css/linearicons.css">
        <link rel="stylesheet" href="assets/css/animate.css">
        <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
		<link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/bootsnav.css" >	
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/responsive.css">
        
        <!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <style>
            /* Override text-transform capitalize từ style.css */
            body, body * {
                text-transform: none !important;
            }
            
            /* Giữ lại uppercase cho các nút cần thiết */
            .btn-promo {
                text-transform: uppercase !important;
            }
            
            /* --- Restored CSS for Cards & Vouchers --- */
            .section-padding { padding: 80px 0; }
            .section-title { margin-bottom: 50px; text-align: center; }
            .section-title h2 { font-size: 2.5rem; font-weight: 700; color: #333; margin-bottom: 20px; }
            .section-title p { color: #666; max-width: 700px; margin: 0 auto; font-size: 1.1rem; }

            /* Product Card */
            .products-grid { display: flex; flex-wrap: wrap; margin: -15px; }
            .product-card {
                background: #fff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.05);
                text-decoration: none;
                color: inherit;
                display: block;
                height: 100%;
                margin: 15px;
                flex: 0 0 calc(25% - 30px);
                transition: transform 0.3s;
            }
            .product-card:hover { transform: translateY(-5px); text-decoration: none; color: inherit; }
            .product-image-container { height: 250px; overflow: hidden; position: relative; }
            .product-image { width: 100%; height: 100%; object-fit: cover; }
            .product-info { padding: 20px; }
            .product-name { font-size: 1.1rem; font-weight: 600; color: #333; margin: 0 0 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 44px; }
            .product-price { font-size: 1.2rem; font-weight: 700; color: #412D3B; }

            /* Genre Card */
            .grid-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
            .genre-card {
                background: #f7f8f9;
                border-radius: 20px;
                padding: 30px 20px;
                text-align: center;
                transition: all 0.3s;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .genre-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
            .genre-icon { font-size: 2.5rem; color: #412D3B; margin-bottom: 20px; display: inline-block; width: 70px; height: 70px; line-height: 70px; background: white; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
            .genre-name { font-size: 1.2rem; font-weight: 700; color: #333; margin-bottom: 10px; }
            .genre-description { font-size: 0.95rem; color: #666; line-height: 1.6; }

            /* View More Button */
            .btn-view-more {
                display: inline-block;
                padding: 12px 35px;
                border: 2px solid #412D3B;
                border-radius: 30px;
                color: #412D3B;
                font-weight: 600;
                margin-top: 40px;
                transition: all 0.3s;
                text-decoration: none;
            }
            .btn-view-more:hover { background: #412D3B; color: white; text-decoration: none; }

            /* Voucher Styles */
            .vouchers { position: relative; overflow: hidden; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
            .voucher-card {
                background: white; border-radius: 20px; overflow: hidden;
                box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin: 10px; transition: all 0.3s ease;
                position: relative; border: 3px solid transparent;
            }
            .voucher-card.owned { border-color: #deccca; }
            .voucher-header { background: #412D3B; color: white; padding: 20px; text-align: center; }
            .voucher-value .value { font-size: 2rem; font-weight: 900; }
            .voucher-body { padding: 20px; }
            .voucher-name { font-weight: 700; color: #333; margin-bottom: 5px; }
            .voucher-code { background: #f8f9fa; border: 2px dashed #412D3B; padding: 8px; text-align: center; margin: 15px 0; font-family: monospace; font-weight: 600; color: #412D3B; }
            .btn-claim-voucher, .btn-claimed {
                width: 100%; padding: 10px; border-radius: 25px; border: none; font-weight: 600; margin-top: 10px;
            }
            .btn-claim-voucher { background: #412D3B; color: white; }
            .btn-claimed { background: #deccca; color: #412D3B; cursor: default; }

            /* --- New Hero & Promo CSS --- */
            .hero-area {
                padding: 180px 0 80px;
                background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
                position: relative;
                overflow: hidden;
            }
            .hero-badges { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
            .badge-item {
                background: white; padding: 10px 20px; border-radius: 30px;
                font-size: 0.95rem; color: #555; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                display: flex; align-items: center; gap: 8px; font-weight: 500;
            }
            .badge-item i { color: #28a745; }
            .badge-item:first-child i { color: #ffc107; }
            .hero-content h1 { font-size: 3.5rem; font-weight: 800; color: #2d3436; line-height: 1.2; margin-bottom: 25px; text-transform: none; }
            .hero-desc { font-size: 1.4rem; color: #636e72; max-width: 800px; margin: 0 auto 50px; line-height: 1.8; text-transform: none; }
            .hero-buttons { display: flex; justify-content: center; gap: 20px; margin-bottom: 70px; }
            .btn-hero { padding: 15px 40px; border-radius: 50px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; text-transform: none; }
            .btn-hero.btn-primary { background: linear-gradient(to right,rgb(89, 70, 83),#deccca); border: none; color: white; box-shadow: 0 10px 25px rgba(248, 235, 235, 0.4); }
            .btn-hero.btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(239, 239, 255, 0.5); color: white; }
            .btn-hero.btn-outline { background: white; border: 2px solid #eee; color: #2d3436; }
            .btn-hero.btn-outline:hover { background: #f8f9fa; border-color: #ddd; transform: translateY(-3px); }
            .hero-stats { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; }
            .stat-box { background: white; padding: 30px 50px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); min-width: 220px; text-align: center; transition: transform 0.3s; text-transform: none; }
            .stat-box:hover { transform: translateY(-5px); }
            .stat-box h3 { font-size: 2.2rem; font-weight: 700; color:rgb(228, 229, 255); margin: 0 0 5px 0; text-transform: none; }
            .stat-box p { margin: 0; color: #777; font-weight: 500; text-transform: none; }

            .promo-section { padding: 100px 0; background: white; }
            .promo-badge { background: #eef2ff; color: #412D3B; padding: 10px 20px; border-radius: 30px; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px; text-transform: none; }
            .promo-content h2 { font-size: 2.8rem; font-weight: 800; color: #2d3436; line-height: 1.3; margin-bottom: 30px; text-transform: none; }
            .promo-list { list-style: none; padding: 0; margin-bottom: 40px; }
            .promo-list li { display: flex; gap: 20px; margin-bottom: 25px; color: #636e72; font-size: 1.1rem; line-height: 1.6; text-transform: none; }
            .promo-list li .icon-box { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; }
            .promo-list li:nth-child(1) .icon-box { background: #e6fffa; color: #38b2ac; }
            .promo-list li:nth-child(2) .icon-box { background: #ebf8ff; color: #4299e1; }
            .btn-promo { background: linear-gradient(to right, #412D3B,#deccca); color: white; padding: 16px 50px; border-radius: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border: none; box-shadow: 0 10px 25px rgba(255, 231, 254, 0.4); transition: all 0.3s; font-size: 1rem; }
            .btn-promo:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgb(89, 70, 83); color: white; text-decoration: none; }
            .row-flex { display: flex; align-items: center; flex-wrap: wrap; }

            @media (max-width: 992px) {
                .products-grid .product-card { flex: 0 0 calc(50% - 30px); }
                .grid-container { grid-template-columns: repeat(2, 1fr); }
            }
            @media (max-width: 768px) {
                .hero-content h1 { font-size: 2.5rem; }
                .hero-stats { gap: 15px; }
                .stat-box { width: 100%; min-width: auto; padding: 20px; }
                .row-flex { display: block; }
                .promo-content { margin-top: 40px; text-align: center; }
                .promo-list li { text-align: left; }
                .hero-area { padding-top: 80px; }
                .products-grid .product-card { flex: 0 0 100%; margin: 15px 0; }
                .grid-container { grid-template-columns: 1fr; }
            }
        </style>
    </head>
	
	<body>
		<!-- Navigation Start -->
		<div class="top-area">
			<div class="header-area">
				<?php include 'includes/navigation.php'; ?>
			</div>
		    <div class="clearfix"></div>
		</div>

        <!-- Hero Section -->
        <section id="hero-area" class="hero-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="hero-content">
                            
                            <h1>Việc "ngon" cho sinh viên,<br>giải pháp nhanh cho bạn</h1>
                            <p class="hero-desc">UniWork kết nối những người có nhu cầu cần hỗ trợ với cộng đồng sinh viên tài năng.<br>Từ làm slide, xử lý Excel đến thiết kế ảnh... Mọi việc đều được giải quyết nhanh chóng, hiệu quả.</p>
                            <div class="hero-buttons">
                                <a href="javascript:void(0)" onclick="checkFreelancerRegistration()" class="btn btn-hero btn-primary">Đăng ký ngay <i class="fa fa-arrow-right"></i></a>
                                <a href="about.php" class="btn btn-hero btn-outline"> Về chúng tôi </a>
                            </div>
                            <div class="hero-stats">
                                <div class="stat-box">
                                    <h3>50,000+</h3>
                                    <p>Dự án hoàn thành</p>
                                </div>
                                <div class="stat-box">
                                    <h3>10,000+</h3>
                                    <p>Sinh viên tài năng</p>
                                </div>
                                <div class="stat-box">
                                    <h3>98%</h3>
                                    <p>Khách hàng hài lòng</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

		<!-- Products Section -->
		<section id="new-arrivals" class="section-padding">
			<div class="container">
				<div class="section-title">
					<h2>Khám phá các dịch vụ<br>được yêu thích nhất</h2>
            
				</div>
				<div class="products-grid">
					<?php foreach ($new_products as $product): ?>
					<a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="product-card">
						<div class="product-image-container">
							<img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
								 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
								 class="product-image"
								 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
						</div>
						<div class="product-info">
							<h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
							<div class="product-price"><?php echo number_format($product['price']); ?>₫</div>
						</div>
					</a>
					<?php endforeach; ?>
				</div>
				<div class="text-center">
					<a href="genre/genres.php" class="btn-view-more">Xem tất cả</a>
				</div>
			</div>
		</section>

        <!-- Freelancer Promo Section -->
        <section class="promo-section">
            <div class="container">
                <div class="row row-flex">
                    <div class="col-md-6">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Freelancer Promo" class="img-responsive" style="border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                    </div>
                    <div class="col-md-6">
                        <div class="promo-content" style="padding-left: 30px;">
                            <span class="promo-badge"><i class="fa fa-rocket"></i> Cơ hội kiếm tiền</span>
                            <h2>ĐỪNG BỎ LỠ CƠ HỘI<br>BIẾN KỸ NĂNG THÀNH THU NHẬP</h2>
                            <ul class="promo-list">
                                <li>
                                    <div class="icon-box"><i class="fa fa-clock-o"></i></div>
                                    <span>"Đăng ký mở 'gian hàng' trên UniWork ngay hôm nay để 'đóng gói' và bán các kỹ năng bạn giỏi nhất."</span>
                                </li>
                                <li>
                                    <div class="icon-box"><i class="fa fa-shield"></i></div>
                                    <span>"Chỉ cần tạo hồ sơ, thiết lập các gói dịch vụ của bạn, và bắt đầu nhận những đơn hàng đầu tiên. Nền tảng an toàn, miễn phí 3 tháng đầu cho tài khoản mới."</span>
                                </li>
                            </ul>
                            <a href="javascript:void(0)" onclick="checkFreelancerRegistration()" class="btn-promo">ĐĂNG KÝ FREELANCER</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

		<!-- Genres Section -->
		<section id="genres" class="genres section-padding">
			<div class="container">
				<div class="section-title">
					<h2>Khám phá các dịch vụ của UniWork</h2>
    
				</div>
				<div class="grid-container" id="genresGrid">
					<?php 
					// Icon mapping cho các dịch vụ freelancer
					$genre_icons = [
						// Thiết kế & Đồ họa
						'Thiết kế đồ họa' => 'fa fa-paint-brush',
						'Design' => 'fa fa-paint-brush',
						'Đồ họa' => 'fa fa-image',
						'Photoshop' => 'fa fa-picture-o',
						
						// Lập trình & Công nghệ
						'Lập trình' => 'fa fa-code',
						'Web Development' => 'fa fa-laptop',
						'Mobile App' => 'fa fa-mobile',
						'Công nghệ' => 'fa fa-cogs',
						
						// Viết lách & Nội dung
						'Viết lách' => 'fa fa-pencil',
						'Content Writing' => 'fa fa-file-text-o',
						'Copywriting' => 'fa fa-edit',
						
						// Excel & Data
						'Excel' => 'fa fa-table',
						'Data Entry' => 'fa fa-database',
						'Xử lý dữ liệu' => 'fa fa-file-excel-o',
						
						// Slide & Presentation
						'PowerPoint' => 'fa fa-file-powerpoint-o',
						'Presentation' => 'fa fa-desktop',
						'Slide' => 'fa fa-slideshare',
						
						// Marketing & SEO
						'Marketing' => 'fa fa-bullhorn',
						'SEO' => 'fa fa-line-chart',
						'Quảng cáo' => 'fa fa-megaphone',
						
						// Video & Audio
						'Video' => 'fa fa-video-camera',
						'Chỉnh sửa video' => 'fa fa-film',
						'Audio' => 'fa fa-headphones',
						
						// Dịch thuật
						'Dịch thuật' => 'fa fa-language',
						'Translation' => 'fa fa-globe',
						
						// Mặc định cho các dịch vụ khác
						'default' => 'fa fa-briefcase'
					];
					
					// Danh sách icon mặc định để xoay vòng nếu không tìm thấy
					$default_icons = [
						'fa fa-paint-brush',  // Thiết kế
						'fa fa-code',         // Lập trình
						'fa fa-pencil',       // Viết lách
						'fa fa-table',        // Excel
						'fa fa-file-powerpoint-o', // Slide
						'fa fa-bullhorn',     // Marketing
						'fa fa-video-camera', // Video
						'fa fa-language'      // Dịch thuật
					];
					
					// Mapping tên hiển thị
					$genre_display_names = [
						'đời sống cá nhân' => 'Lập trình & Công nghệ',
						'Đời sống cá nhân' => 'Lập trình & Công nghệ',
						'ĐỜI SỐNG CÁ NHÂN' => 'Lập trình & Công nghệ',
						'Đời sống & Cá nhân' => 'Lập trình & Công nghệ',
						'đời sống & cá nhân' => 'Lập trình & Công nghệ',
						'ĐỜI SỐNG & CÁ NHÂN' => 'Lập trình & Công nghệ'
					];
					
					// Mapping mô tả cho các genre cụ thể
					$genre_descriptions = [
						'đời sống cá nhân' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...',
						'Đời sống cá nhân' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...',
						'ĐỜI SỐNG CÁ NHÂN' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...',
						'Đời sống & Cá nhân' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...',
						'đời sống & cá nhân' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...',
						'ĐỜI SỐNG & CÁ NHÂN' => 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...'
					];
					
					$icon_index = 0;
					foreach ($genres as $genre): 
						// Tìm icon phù hợp
						$icon = 'fa fa-briefcase'; // Mặc định
						$genre_name = $genre['genre_name'];
						
						// Kiểm tra nếu là "đời sống" hoặc "cá nhân" để đổi thành "Lập trình & Công nghệ"
						$is_life_personal = false;
						if (stripos($genre_name, 'đời sống') !== false || 
						    stripos($genre_name, 'cá nhân') !== false ||
						    stripos($genre_name, 'đời sống & cá nhân') !== false ||
						    stripos($genre_name, 'đời sống cá nhân') !== false) {
							$is_life_personal = true;
							$display_name = 'Lập trình & Công nghệ';
							$icon = 'fa fa-code';
							$display_description = isset($genre_descriptions[$genre_name]) 
								? $genre_descriptions[$genre_name] 
								: 'Lập trình web, mobile app, phát triển phần mềm, công nghệ thông tin, AI & Machine Learning...';
						} else {
							// Lấy tên hiển thị (nếu có mapping thì dùng, không thì dùng tên gốc)
							$display_name = isset($genre_display_names[$genre_name]) 
								? $genre_display_names[$genre_name] 
								: $genre_name;
							
							// Lấy mô tả (nếu có mapping thì dùng, không thì dùng mô tả gốc)
							$display_description = isset($genre_descriptions[$genre_name]) 
								? $genre_descriptions[$genre_name] 
								: $genre['description'];
							
							// Kiểm tra trong mapping icon
							if (isset($genre_icons[$genre_name])) {
								$icon = $genre_icons[$genre_name];
							} else {
								// Tìm kiếm không phân biệt hoa thường
								foreach ($genre_icons as $key => $value) {
									if (stripos($genre_name, $key) !== false || stripos($key, $genre_name) !== false) {
										$icon = $value;
										break;
									}
								}
								
								// Nếu vẫn không tìm thấy, dùng icon mặc định xoay vòng
								if ($icon === 'fa fa-briefcase') {
									$icon = $default_icons[$icon_index % count($default_icons)];
									$icon_index++;
								}
							}
						}
					?>
					<div class="genre-card">
						<div class="genre-header">
							<span class="genre-icon"><i class="<?php echo $icon; ?>"></i></span>
							<div class="genre-name"><?php echo htmlspecialchars($display_name); ?></div>
						</div>
						<div class="genre-info">
							<div class="genre-description"><?php echo htmlspecialchars($display_description); ?></div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<div class="text-center">
					<a href="genre/genres.php" class="btn-view-more">Xem tất cả dịch vụ</a>
				</div>
			</div>
		</section>

		<!-- Vouchers Section -->
		<?php if (!empty($vouchers)): ?>
		<section id="vouchers" class="vouchers section-padding">
			<div class="container">
				<div class="section-title">
					<h2>Vouchers</h2>
				</div>
				<div class="voucher-slider-wrapper">
					<div class="owl-carousel owl-theme" id="voucherSlider">
						<?php foreach ($vouchers as $voucher): ?>
						<div class="item">
							<div class="voucher-card <?php echo $voucher['user_owned'] ? 'owned' : ''; ?>" data-voucher-id="<?php echo $voucher['voucher_id']; ?>">
								<div class="voucher-header">
									<div class="voucher-type">
										<?php if ($voucher['discount_type'] == 'percentage'): ?>
											<i class="fa fa-percentage"></i> <span>PHẦN TRĂM</span>
										<?php else: ?>
											<i class="fa fa-dollar"></i> <span>SỐ TIỀN</span>
										<?php endif; ?>
									</div>
									<div class="voucher-value">
										<?php if ($voucher['discount_type'] == 'percentage'): ?>
											<span class="value"><?php echo $voucher['discount_value']; ?>%</span>
										<?php else: ?>
											<span class="value"><?php echo number_format($voucher['discount_value']); ?>₫</span>
										<?php endif; ?>
									</div>
								</div>
								<div class="voucher-body">
									<h4 class="voucher-name"><?php echo htmlspecialchars($voucher['voucher_name']); ?></h4>
									<div class="voucher-code"><i class="fa fa-ticket"></i> <?php echo $voucher['voucher_code']; ?></div>
									<div class="voucher-footer">
										<?php if ($voucher['user_owned']): ?>
											<button class="btn-claimed" disabled><i class="fa fa-check"></i> Đã sở hữu</button>
										<?php else: ?>
											<button class="btn-claim-voucher" data-voucher-id="<?php echo $voucher['voucher_id']; ?>"><i class="fa fa-gift"></i> Lấy voucher</button>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php include 'includes/footer.php'; ?>
		
		<script src="assets/js/jquery.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/bootsnav.js"></script>
        <script src="assets/js/owl.carousel.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
        <script src="assets/js/custom.js"></script>
        
        <script>
            $(document).ready(function() {
                if ($('#voucherSlider').length) {
                    $('#voucherSlider').owlCarousel({
                        items: 3, loop: true, margin: 20, nav: false, dots: true,
                        autoplay: true, autoplayTimeout: 5000,
                        responsive: { 0: { items: 1 }, 768: { items: 2 }, 992: { items: 3 } }
                    });
                }
            });
        </script>
    </body>
</html>
