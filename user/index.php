<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy thông tin user nếu đã đăng nhập (không bắt buộc)
$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();

// Kết nối database để lấy banner
$banner_sql = "SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC, banner_id ASC";
$banner_result = $conn->query($banner_sql);
$banners = [];
if ($banner_result && $banner_result->num_rows > 0) {
    while ($row = $banner_result->fetch_assoc()) {
        $banners[] = $row;
    }
}

// Lấy 4 sản phẩm mới nhất
$sql_new_products = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$result_new_products = $conn->query($sql_new_products);
$new_products = [];
if ($result_new_products->num_rows > 0) {
    while($row = $result_new_products->fetch_assoc()) {
        $new_products[] = $row;
    }
}

// Lấy 4 nghệ sĩ
$sql_artists = "SELECT * FROM artists WHERE status = 1 ORDER BY artist_id DESC LIMIT 4";
$result_artists = $conn->query($sql_artists);
$artists = [];
if ($result_artists->num_rows > 0) {
    while($row = $result_artists->fetch_assoc()) {
        $artists[] = $row;
    }
}

// Lấy 4 dòng nhạc
$sql_genres = "SELECT * FROM genres ORDER BY genre_id DESC LIMIT 4";
$result_genres = $conn->query($sql_genres);
$genres = [];
if ($result_genres->num_rows > 0) {
    while($row = $result_genres->fetch_assoc()) {
        $genres[] = $row;
    }
}

// Lấy voucher khả dụng (còn hiệu lực và đang active)
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
    // Nếu chưa đăng nhập, chỉ hiển thị voucher không có thông tin sở hữu
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
        <!-- meta data -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

        <!--font-family-->
		<link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
        
        <!-- title of site -->
        <title>AuraDisc</title>

        <!-- For favicon png -->
		<link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
       
        <!--font-awesome.min.css-->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">

        <!--linear icon css-->
		<link rel="stylesheet" href="assets/css/linearicons.css">

		<!--animate.css-->
        <link rel="stylesheet" href="assets/css/animate.css">

        <!--owl.carousel.css-->
        <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
		<link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
		
        <!--bootstrap.min.css-->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- bootsnav -->
		<link rel="stylesheet" href="assets/css/bootsnav.css" >	
        
        <!--style.css-->
        <link rel="stylesheet" href="assets/css/style.css">
        
        <!--responsive.css-->
        <link rel="stylesheet" href="assets/css/responsive.css">
        
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		
        <!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <style>
            .artist-card {
                background: #f7f8f9; /* Synchronized with accessories.php */
                border-radius: 20px; /* Synchronized with accessories.php */
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.08); /* Synchronized with accessories.php */
                margin-bottom: 30px;
                transition: all 0.3s ease;
                cursor: pointer;
                position: relative;
                height: 100%;
                opacity: 0;
                transform: translateY(20px);
                animation: fadeIn 0.5s ease forwards;
                display: flex; /* Make artist-card a flex container */
                flex-direction: column; /* Stack children vertically */
            }
            
            .artist-card:hover {
                transform: translateY(-5px); /* Synchronized with accessories.php */
                box-shadow: 0 15px 40px rgba(0,0,0,0.15); /* Synchronized with accessories.php */
            }
            
            .artist-image {
                width: 100%;
                height: 220px; /* Synchronized with accessories.php */
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .artist-card:hover .artist-image {
                transform: scale(1.03); /* Synchronized with accessories.php */
            }
            
            .artist-info {
                padding: 20px;
                text-align: center;
                flex-grow: 1; /* Allow artist-info to grow and fill available space */
                display: flex; /* Make artist-info a flex container */
                flex-direction: column; /* Stack children vertically */
                justify-content: center; /* Center content vertically */
            }
            
            .artist-name {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 8px;
                color: #333;
                min-height: 30px;
                line-height: 1.2;
                text-align: center;
            }
            
            .artist-bio {
                color: #666;
                font-size: 0.9rem;
                line-height: 1.6;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                height: 70px; /* Fixed height for consistent card height */
                text-align: center;
            }

            .genre-card {
                background: #f7f8f9;
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
                transform: translateY(-5px);
                z-index: 2;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                height: 100%;
                text-decoration: none;
                color: inherit;
            }
            
            .genre-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            }
            
            .genre-name {
                font-size: 1.25rem; /* Synchronized with product/artist names */
                font-weight: 600;
                margin-bottom: 8px; /* Synchronized with product/artist names */
                color: #333; /* Text color for light background */
                min-height: 30px; /* Synchronized with product/artist names */
                line-height: 1.2; /* Synchronized with product/artist names */
            }
            
            .genre-description {
                font-size: 0.9rem;
                opacity: 1; /* Ensure full visibility on light background */
                color: #666; /* Text color for light background */
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                height: 70px; /* Ensures consistent height for 3 lines of text and uniform card size */
                flex-grow: 1; /* Allow description to grow and fill available space */
            }

            .section-title {
                text-align: center;
                margin-bottom: 50px;
                position: relative;
            }
            
            .section-title h2 {
                font-size: 2.5rem;
                font-weight: 700;
                color: #333;
                margin-bottom: 20px;
            }
            
            .section-title p {
                color: #666;
                max-width: 600px;
                margin: 0 auto;
            }

            .section-padding {
                padding: 80px 0;
            }

            .product-card {
                background: #f7f8f9; /* Synchronized with accessories.php */
                border-radius: 20px; /* Synchronized with accessories.php */
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0,0,0,0.08); /* Synchronized with accessories.php */
                margin-bottom: 30px;
                transition: all 0.3s ease;
                position: relative;
                height: 100%;
                opacity: 0;
                transform: translateY(20px);
                animation: fadeIn 0.5s ease forwards;
            }
            
            .product-card:hover {
                transform: translateY(-5px); /* Synchronized with accessories.php */
                box-shadow: 0 15px 40px rgba(0,0,0,0.15); /* Synchronized with accessories.php */
            }
            
            .product-image {
                width: 100%;
                height: 220px; /* Synchronized with accessories.php */
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            
            .product-card:hover .product-image {
                transform: scale(1.03); /* Synchronized with accessories.php */
            }
            
            .product-info {
                padding: 20px;
                text-align: center;
            }
            
            .product-name {
                font-size: 1.25rem; /* Synchronized with accessories.php title size */
                font-weight: 600;
                margin-bottom: 8px; /* Synchronized with accessories.php title margin */
                color: #333;
                min-height: 30px; /* Synchronized with accessories.php */
                line-height: 1.2; /* Synchronized with accessories.php */
            }
            
            .product-price {
                color: #412D3B; /* Synchronized with accessories.php price color */
                font-size: 1.15rem; /* Synchronized with accessories.php price size */
                font-weight: 700;
                margin-bottom: 0px; /* Synchronized with accessories.php price margin */
            }
            
            .btn-view-more {
                display: inline-block;
                background: #412D3B; /* Synchronized with accessories.php button background */
                color: white;
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: 500;
                margin-top: 70px; /* Adjusted to create more space */
                transition: all 0.3s ease;
            }
            
            .btn-view-more:hover {
                transform: none; /* Removed all transformations to prevent shrinking/movement */
                box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4); /* Synchronized with accessories.php button shadow */
                color: #412D3B; /* Changed to dark color for better contrast on light background */
                background: #deccca;
            }

            .btn-view-product {
                display: inline-block;
                background: #412D3B; /* Synchronized with accessories.php button background */
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                text-align: center;
                margin-top: 15px;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            
            .btn-view-product:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4); /* Synchronized with accessories.php button shadow */
                color: #deccca; /* Synchronized with accessories.php button hover color */
                text-decoration: none;
                background: #deccca;
            }

            /* Voucher Styles */
            .vouchers {
                position: relative;
                overflow: hidden;
            }

            .voucher-slider-wrapper {
                position: relative;
                margin: 0 50px;
            }

            .voucher-card {
                background: white;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 8px 25px rgba(0,0,0,0.1);
                margin: 10px;
                transition: all 0.3s ease;
                position: relative;
                border: 3px solid transparent;
                background-clip: padding-box;
            }

            .voucher-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            }

            .voucher-card.owned {
                background: #deccca; /* Synchronized with accessories.php light theme */
                border-color: #deccca;
            }

            .voucher-card.owned:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(222, 204, 202, 0.1) 0%, rgba(222, 204, 202, 0.1) 100%); /* Adjusted opacity */
                z-index: 1;
            }

            .voucher-header {
                background: #412D3B; /* Synchronized with accessories.php dark theme */
                color: white;
                padding: 20px;
                text-align: center;
                position: relative;
            }

            .voucher-card.owned .voucher-header {
                background: #412D3B; /* Synchronized with accessories.php dark theme */
            }

            .voucher-type {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 0.8rem;
                font-weight: 600;
                margin-bottom: 10px;
                opacity: 0.9;
            }

            .voucher-value .value {
                font-size: 2.2rem;
                font-weight: 900;
                display: block;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }

            .voucher-value small {
                font-size: 0.7rem;
                opacity: 0.8;
                display: block;
                margin-top: 5px;
            }

            .voucher-body {
                padding: 25px 20px;
                position: relative;
                z-index: 2;
            }

            .voucher-name {
                font-size: 1.2rem;
                font-weight: 700;
                color: #333;
                margin-bottom: 8px;
                line-height: 1.3;
            }

            .voucher-desc {
                color: #666;
                font-size: 0.9rem;
                line-height: 1.4;
                margin-bottom: 15px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .voucher-code {
                background: #f8f9fa;
                border: 2px dashed #412D3B; /* Synchronized with accessories.php dark theme */
                border-radius: 8px;
                padding: 10px;
                text-align: center;
                margin-bottom: 15px;
                font-family: 'Courier New', monospace;
                font-weight: 600;
                color: #412D3B; /* Synchronized with accessories.php dark theme */
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .voucher-conditions {
                margin-bottom: 15px;
            }

            .condition {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.85rem;
                color: #666;
                margin-bottom: 5px;
            }

            .condition i {
                color: #412D3B; /* Synchronized with accessories.php dark theme */
                width: 14px;
            }

            .voucher-footer {
                padding: 0 20px 20px;
                position: relative;
                z-index: 2;
            }

            .btn-claim-voucher, .btn-claimed {
                width: 100%;
                padding: 12px;
                border: none;
                border-radius: 25px;
                font-weight: 600;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .btn-claim-voucher {
                background: #412D3B; /* Synchronized with accessories.php button background */
                color: white;
                box-shadow: 0 4px 15px rgba(65, 45, 59, 0.3); /* Synchronized with accessories.php button shadow */
            }

            .btn-claim-voucher:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(65, 45, 59, 0.4); /* Synchronized with accessories.php button shadow */
            }

            .btn-claim-voucher:active {
                transform: translateY(0);
            }

            .btn-claimed {
                background: #412D3B !important;
                color: #deccca !important;
                cursor: default;
                opacity: 1;
                border: 2px solid #412D3B;
                font-weight: 600;
                transition: all 0.3s;
            }

            .owned-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                background: #deccca; /* Synchronized with accessories.php light theme */
                color: #412D3B; /* Synchronized with accessories.php dark theme */
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                box-shadow: 0 4px 10px rgba(65, 45, 59, 0.4); /* Synchronized with accessories.php button shadow */
                z-index: 3;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            /* Voucher Navigation */
            .voucher-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 100%;
                display: flex;
                justify-content: space-between;
                pointer-events: none;
                z-index: 4;
            }

            .voucher-prev, .voucher-next {
                background: rgba(65, 45, 59, 0.9); /* Synchronized with accessories.php dark theme */
                color: white;
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                transition: all 0.3s ease;
                pointer-events: all;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }

            .voucher-prev:hover, .voucher-next:hover {
                background: rgba(65, 45, 59, 1); /* Synchronized with accessories.php dark theme */
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            }

            .voucher-prev {
                left: -60px !important; /* hoặc -50px, tuỳ bạn thấy hợp lý */
            }

            .voucher-next {
                right: -60px !important; /* hoặc -50px, tuỳ bạn thấy hợp lý */
            }

            /* Owl Carousel Custom Styles for Vouchers */
            #voucherSlider .owl-dots {
                text-align: center;
                margin-top: 30px;
            }

            #voucherSlider .owl-dot {
                display: inline-block;
                width: 12px;
                height: 12px;
                background: rgba(65, 45, 59, 0.3); /* Synchronized with accessories.php dark theme */
                border-radius: 50%;
                margin: 0 5px;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            #voucherSlider .owl-dot.active {
                background: #412D3B; /* Synchronized with accessories.php dark theme */
                transform: scale(1.3);
            }

            /* Loading Animation */
            .voucher-loading {
                opacity: 0.6;
                pointer-events: none;
            }

            .voucher-loading .btn-claim-voucher {
                background: #ccc;
                cursor: not-allowed;
            }

            /* Success Animation */
            @keyframes claimSuccess {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); background: #28a745; }
                100% { transform: scale(1); background: #28a745; }
            }

            .claim-success {
                animation: claimSuccess 0.6s ease;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .voucher-slider-wrapper {
                    margin: 0 20px;
                }
                
                .voucher-prev {
                    left: -100px !important;
                }
                
                .voucher-next {
                    right: -30px !important;
                }
                
                .voucher-prev, .voucher-next {
                    width: 40px;
                    height: 40px;
                    font-size: 1rem;
                }
                
                .voucher-value .value {
                    font-size: 1.8rem;
                }
                
                .voucher-name {
                    font-size: 1.1rem;
                }
            }

            /* Ensure equal height for cards in grids */
            .products-grid, .artists-grid, .genres-grid {
                display: flex;
                flex-wrap: wrap;
                align-items: stretch; /* Stretches items to fit the height of the tallest item in the row */
            }
            
            .products-grid > .col-md-3, .products-grid > .col-sm-6,
            .artists-grid > .col-md-3, .artists-grid > .col-sm-6,
            .genres-grid > .col-md-3, .genres-grid > .col-sm-6 {
                display: flex; /* Makes the column a flex container for its direct child (the card) */
            }

            .product-card, .artist-card, .genre-card {
                /* ... existing code ... */
                height: 100%; /* Ensures the card itself stretches to the full height of its flex container */
            }

            .genres-grid { /* New style for the row containing genre cards */
                display: flex;
                flex-wrap: wrap;
                align-items: stretch; /* Ensures all items in the row stretch to the same height */
            }
            
            .genre-card {
                background: #f7f8f9;
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
                transform: translateY(-5px);
                z-index: 2;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                height: 100%;
                text-decoration: none;
                color: inherit;
            }
            
            .genre-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            }
            
            .genre-name {
                font-size: 1.25rem; /* Synchronized with product/artist names */
                font-weight: 600;
                margin-bottom: 8px; /* Synchronized with product/artist names */
                color: #333; /* Text color for light background */
                min-height: 30px; /* Synchronized with product/artist names */
                line-height: 1.2; /* Synchronized with product/artist names */
            }
            
            .genre-description {
                font-size: 0.9rem;
                opacity: 1; /* Ensure full visibility on light background */
                color: #666; /* Text color for light background */
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                height: 70px; /* Ensures consistent height for 3 lines of text and uniform card size */
                flex-grow: 1; /* Allow description to grow and fill available space */
            }

            /* --- START: Genres Section CSS from genres.php (Đồng bộ UI genre card) --- */
            .grid-container {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 30px;
                padding: 40px 0;
            }
            @media (max-width: 1200px) {
                .grid-container { grid-template-columns: repeat(3, 1fr);}
            }
            @media (max-width: 992px) {
                .grid-container { grid-template-columns: repeat(2, 1fr);}
            }
            @media (max-width: 768px) {
                .grid-container { grid-template-columns: repeat(1, 1fr);}
            }
            .genre-card {
                background: #f7f8f9;
                border-radius: 24px;
                overflow: hidden;
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
                transform: translateY(-5px);
                z-index: 2;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                height: 100%;
                text-decoration: none;
                color: inherit;
            }
            .genre-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            }
            .genre-header {
                background: linear-gradient(120deg, #412D3B 0%, #e9dedb 100%);
                color: #fff;
                padding: 40px 0 20px 0;
                text-align: center;
                border-top-left-radius: 24px;
                border-top-right-radius: 24px;
                position: relative;
            }
            .genre-icon {
                font-size: 3rem;
                margin-bottom: 10px;
                display: block;
            }
            .genre-name {
                font-size: 1.35rem;
                font-weight: 700;
                margin-bottom: 8px;
                color: #444;
                letter-spacing: 0.5px;
            }
            .genre-count {
                font-size: 1rem;
                color: #fff;
                opacity: 0.85;
                margin-bottom: 0;
            }
            .genre-info {
                padding: 24px 20px 28px 20px;
                flex: 1;
                display: flex;
                align-items: flex-start;
            }
            .genre-description {
                color: #444;
                font-size: 1rem;
                line-height: 1.6;
                text-align: left;
                margin: 0;
                word-break: break-word;
            }
            @keyframes fadeIn {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            /* --- END: Genres Section CSS from genres.php --- */

            #banner-slider {
                margin-top: 78px;
            }
            @media (max-width: 768px) {
                #banner-slider {
                    margin-top: 60px;
                }
            }

            .genre-card .genre-icon {
                transition: transform 0.3s;
            }
            .genre-card:hover .genre-icon {
                transform: scale(1.03);
            }
        </style>

    </head>
	
	<body>
		<!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->
		
		<!-- Navigation Start -->
		<div class="top-area">
			<div class="header-area">
				<?php include 'includes/navigation.php'; ?>
				</div><!--/.header-area-->
			    <div class="clearfix"></div>
			</div><!-- /.top-area-->
		<!-- Navigation End -->

		<!--banner-slider start -->
		<?php if (!empty($banners)): ?>
		<section id="banner-slider" class="banner-slider">
			<div class="container-fluid">
				<div id="bannerCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
					<!-- Indicators -->
				 <ol class="carousel-indicators">
						<?php foreach ($banners as $index => $banner): ?>
						<li data-target="#bannerCarousel" data-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active"' : ''; ?>></li>
						<?php endforeach; ?>
					</ol>

					<!-- Wrapper for slides -->
				<div class="carousel-inner" role="listbox">
						<?php foreach ($banners as $index => $banner): ?>
						<div class="item <?php echo $index === 0 ? 'active' : ''; ?>">
							<div class="banner-slide">
								<img src="<?php echo htmlspecialchars($banner['banner_url']); ?>" 
								     alt="<?php echo htmlspecialchars($banner['banner_title']); ?>"
								     class="banner-image"
								     onerror="this.src='https://via.placeholder.com/1200x400/ff6b35/ffffff?text=<?php echo urlencode($banner['banner_title']); ?>'">
								
								<?php if (!empty($banner['banner_title']) || !empty($banner['banner_description'])): ?>
								<div class="banner-overlay">
			<div class="container">
										<div class="banner-content">
											<?php if (!empty($banner['banner_title'])): ?>
											<h2 class="banner-title"><?php echo htmlspecialchars($banner['banner_title']); ?></h2>
											<?php endif; ?>
											
											<?php if (!empty($banner['banner_description'])): ?>
											<p class="banner-description"><?php echo htmlspecialchars($banner['banner_description']); ?></p>
											<?php endif; ?>
											
											<div class="banner-actions">
												<a href="products.php" class="btn-banner btn-primary">
													<i class="fa fa-music"></i> Khám Phá Ngay
												</a>
												<a href="Artists/Artists.php" class="btn-banner btn-secondary">
													<i class="fa fa-users"></i> Nghệ Sĩ
												</a>
												</div>
											</div>
										</div>
									</div>
								<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
						</div>

					<!-- Controls -->
					<a class="left carousel-control" href="#bannerCarousel" role="button" data-slide="prev">
						<span class="fa fa-chevron-left" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					</a>
					<a class="right carousel-control" href="#bannerCarousel" role="button" data-slide="next">
						<span class="fa fa-chevron-right" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					</a>
									</div>
									</div>
		</section>
		<?php endif; ?>
		<!--banner-slider end-->

		<!--new-arrivals start -->
		<section id="new-arrivals" class="section-padding">
			<div class="container">
				<div class="section-title">
					<h2>New Arrivals</h2>
				</div>
				
					<div class="row products-grid">
					<?php foreach ($new_products as $product): ?>
					<div class="col-md-3 col-sm-6">
						<a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="product-card">
							<img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
								 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
								 class="product-image"
								 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
							<div class="product-info">
								<h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
								<div class="product-price"><?php echo number_format($product['price']); ?>₫</div>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
					</div>
				
				<div class="text-center">
					<a href="new-arrivals.php" class="btn-view-more">Xem tất cả</a>
						</div>
									</div>
		</section>
		<!--new-arrivals end -->

		<!-- Removed sofa-collection section -->

		<!--artists start -->
		<section id="artists" class="artists section-padding">
			<div class="container">
				<div class="section-title">
					<h2>Featured Artists</h2>
				</div>
					<div class="row artists-grid">
					<?php foreach ($artists as $artist): ?>
					<div class="col-md-3 col-sm-6">
						<a href="Artists/artist-detail.php?id=<?php echo $artist['artist_id']; ?>" class="artist-card">
							<img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
								 alt="<?php echo htmlspecialchars($artist['artist_name']); ?>"
								 class="artist-image"
								 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
							<div class="artist-info">
								<h3 class="artist-name"><?php echo htmlspecialchars($artist['artist_name']); ?></h3>
								<p class="artist-bio"><?php echo htmlspecialchars($artist['bio']); ?></p>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
								</div>
				<div class="text-center">
					<a href="Artists/Artists.php" class="btn-view-more">Xem tất cả nghệ sĩ</a>
							</div>
						</div>
		</section><!--/.artists-->
		<!--feature end -->

		<!--genres start -->
		<section id="genres" class="genres section-padding">
			<div class="container">
				<div class="section-title">
					<h2>Genres</h2>
				</div>
				<div class="grid-container" id="genresGrid">
				<?php 
				$genre_icons = [
					'Rock' => 'fa fa-music',
					'Pop' => 'fa fa-music',
					'Jazz' => 'fa fa-music',
					'Classical' => 'fa fa-music',
					'Electronic' => 'fa fa-music',
					'Hip Hop' => 'fa fa-music',
					'Country' => 'fa fa-music',
					'R&B' => 'fa fa-music'
				];
				foreach ($genres as $genre): 
					$icon = isset($genre_icons[$genre['genre_name']]) ? $genre_icons[$genre['genre_name']] : 'fa fa-music';
				?>
					<a href="genre/genres-detail.php?id=<?php echo $genre['genre_id']; ?>" class="genre-card">
						<div class="genre-header">
							<span class="genre-icon"><i class="<?php echo $icon; ?>"></i></span>
							<div class="genre-name"><?php echo htmlspecialchars($genre['genre_name']); ?></div>
						</div>
						<div class="genre-info">
							<div class="genre-description"><?php echo htmlspecialchars($genre['description']); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
				</div>
				<div class="text-center">
					<a href="genres.php" class="btn-view-more">Xem tất cả dòng nhạc</a>
				</div>
			</div>
		</section><!--/.genres-->
		<!--genres end -->

		<!--vouchers start -->
		<?php if (!empty($vouchers)): ?>
		<section id="vouchers" class="vouchers section-padding" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
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
											<i class="fa fa-percentage"></i>
											<span>PHẦN TRĂM</span>
										<?php else: ?>
											<i class="fa fa-dollar"></i>
											<span>SỐ TIỀN</span>
										<?php endif; ?>
									</div>
									<div class="voucher-value">
										<?php if ($voucher['discount_type'] == 'percentage'): ?>
											<span class="value"><?php echo $voucher['discount_value']; ?>%</span>
											<?php if ($voucher['max_discount_amount']): ?>
												<small>Tối đa <?php echo number_format($voucher['max_discount_amount']); ?>₫</small>
											<?php endif; ?>
										<?php else: ?>
											<span class="value"><?php echo number_format($voucher['discount_value']); ?>₫</span>
										<?php endif; ?>
									</div>
								</div>
								
								<div class="voucher-body">
									<h4 class="voucher-name"><?php echo htmlspecialchars($voucher['voucher_name']); ?></h4>
									<p class="voucher-desc"><?php echo htmlspecialchars($voucher['description']); ?></p>
									
									<div class="voucher-code">
										<i class="fa fa-ticket"></i>
										<span><?php echo $voucher['voucher_code']; ?></span>
									</div>
									
									<div class="voucher-conditions">
										<?php if ($voucher['min_order_amount'] > 0): ?>
											<div class="condition">
												<i class="fa fa-shopping-cart"></i>
												<span>Đơn tối thiểu: <?php echo number_format($voucher['min_order_amount']); ?>₫</span>
											</div>
										<?php endif; ?>
										
										<div class="condition">
											<i class="fa fa-calendar"></i>
											<span>HSD: <?php echo date('d/m/Y', strtotime($voucher['end_date'])); ?></span>
										</div>
									</div>
								</div>
								
								<div class="voucher-footer">
									<?php if ($voucher['user_owned']): ?>
										<button class="btn-claimed" disabled>
											<i class="fa fa-check"></i> Đã sở hữu
										</button>
									<?php else: ?>
										<button class="btn-claim-voucher" data-voucher-id="<?php echo $voucher['voucher_id']; ?>">
											<i class="fa fa-gift"></i> Lấy voucher
										</button>
									<?php endif; ?>
								</div>
								
								<?php if ($voucher['user_owned']): ?>
									<div class="owned-badge">
										<i class="fa fa-crown"></i>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					
					<!-- Custom Navigation -->
					<div class="voucher-nav">
						<button class="voucher-prev"><i class="fa fa-chevron-left"></i></button>
						<button class="voucher-next"><i class="fa fa-chevron-right"></i></button>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>
		<!--vouchers end -->

		<!-- Include Footer -->
		<?php include 'includes/footer.php'; ?>
		
		<!-- Include Chat Widget -->
		<?php include 'includes/chat-widget.php'; ?>
		
		<!-- Include all js compiled plugins (below), or include individual files as needed -->

		<script src="assets/js/jquery.js"></script>
        
        <!--modernizr.min.js-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
		
		<!--bootstrap.min.js-->
        <script src="assets/js/bootstrap.min.js"></script>
		
		<!-- bootsnav js -->
		<script src="assets/js/bootsnav.js"></script>

		<!--owl.carousel.js-->
        <script src="assets/js/owl.carousel.min.js"></script>


		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
		
        
        <!--Custom JS-->
        <script src="assets/js/custom.js"></script>
        
        <!-- Chat Widget CSS -->
        <link rel="stylesheet" href="includes/chat-widget.css">
        
        <!-- Chat Widget JS -->
        <script src="includes/chat-widget.js"></script>
        
        <!-- Banner Slider CSS -->
        <style>
        /* Navigation adjustments when no hero section */
        .top-area {
            position: relative;
            z-index: 999;
            background: #412D3B !important; /* Synchronized with accessories.php dark theme */
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        
        /* Index page navigation styling - white text with glow effect */
        .navbar-nav > li > a {
            color: #FFFFFF !important;
            text-shadow: none; /* Removed glow effect */
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .navbar-nav > li > a:hover {
            color: #deccca !important; /* Synchronized with accessories.php light theme */
            text-shadow: none; /* Removed glow effect */
            transform: translateY(-2px);
        }
        
        /* Navigation icons styling */
        .navbar-nav > li > a i {
            color: #FFFFFF !important;
            text-shadow: none; /* Removed glow effect */
            transition: all 0.3s ease;
        }
        
        .navbar-nav > li > a:hover i {
            color: #deccca !important; /* Synchronized with accessories.php light theme */
            text-shadow: none; /* Removed glow effect */
            transform: scale(1.1);
        }
        
        /* Brand logo styling */
        .navbar-brand {
            color: #FFFFFF !important;
            text-shadow: none; /* Removed glow effect */
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #deccca !important; /* Synchronized with accessories.php light theme */
            text-shadow: none; /* Removed glow effect */
        }
        
        /* Navigation background with gradient overlay */
        /* Removed original top-area gradient, now using solid color */
        
        /* Mobile menu button styling */
        .navbar-toggle {
            border-color: #fff !important;
        }
        
        .navbar-toggle .icon-bar {
            background-color: #fff !important;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }
        
        /* Dropdown menu styling for index page */
        .dropdown-menu {
            background: #412D3B !important; /* Synchronized with accessories.php dark theme */
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        
        .dropdown-menu > li > a {
            color: #fff !important;
            text-shadow: none; /* Removed glow effect */
            transition: all 0.3s ease;
        }
        
        .dropdown-menu > li > a:hover {
            background: rgba(255, 255, 255, 0.2) !important; /* Light transparent overlay */
            color: #fff !important;
            text-shadow: none; /* Removed glow effect */
        }
        
        /* Search box styling */
        .navbar-form input {
            background: rgba(255, 255, 255, 0.2) !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }
        
        .navbar-form input::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        .navbar-form input:focus {
            background: rgba(255, 255, 255, 0.3) !important;
            border-color: #fff !important;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
        
        /* Animation for navigation items */
        /* Removed @keyframes navGlow */
        
        .navbar-nav > li > a {
            animation: none; /* Removed animation */
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-nav > li > a {
                text-shadow: none;
            }
            
            .top-area {
                background: #412D3B !important; /* Synchronized with accessories.php dark theme */
            }
        }
        
        /* Banner Slider Styles */
        .banner-slider {
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        .banner-slider .container-fluid {
            padding: 0;
        }
        
        #bannerCarousel {
            position: relative;
            width: 100%;
        }
        
        .banner-slide {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
        }
        
        .banner-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }
        
        .banner-slide:hover .banner-image {
            transform: scale(1.05);
        }
        
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(65, 45, 59, 0.8), rgba(65, 45, 59, 0.6)); /* Synchronized with accessories.php dark theme */
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .banner-slide:hover .banner-overlay {
            opacity: 1;
        }
        
        .banner-content {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 20px;
        }
        
        .banner-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 0.6s ease;
        }
        
        .banner-description {
            font-size: 1.2rem;
            margin-bottom: 25px;
            line-height: 1.6;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease;
        }
        
        .banner-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease;
        }
        
        .btn-banner {
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-banner.btn-primary {
            background: #fff;
            color: #412D3B; /* Synchronized with accessories.php dark theme */
            border: 2px solid #fff;
        }
        
        .btn-banner.btn-primary:hover {
            background: transparent;
            color: #fff;
            border-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-banner.btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
        }
        
        .btn-banner.btn-secondary:hover {
            background: #fff;
            color: #412D3B; /* Synchronized with accessories.php dark theme */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Carousel Controls */
        #bannerCarousel .carousel-control {
            background: none;
            border: none;
            width: 60px;
            height: 60px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
            transition: all 0.3s ease;
        }
        
        #bannerCarousel .carousel-control:hover {
            opacity: 1;
        }
        
        #bannerCarousel .carousel-control.left {
            left: 20px;
        }
        
        #bannerCarousel .carousel-control.right {
            right: 20px;
        }
        
        #bannerCarousel .carousel-control span {
            font-size: 24px;
            color: #fff;
            background: rgba(65, 45, 59, 0.8); /* Synchronized with accessories.php dark theme */
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        #bannerCarousel .carousel-control:hover span {
            background: rgba(65, 45, 59, 1); /* Synchronized with accessories.php dark theme */
            transform: scale(1.1);
        }
        
        /* Carousel Indicators */
        #bannerCarousel .carousel-indicators {
            bottom: 20px;
        }
        
        #bannerCarousel .carousel-indicators li {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid #fff;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        #bannerCarousel .carousel-indicators li.active {
            background: #412D3B; /* Synchronized with accessories.php dark theme */
            border-color: #412D3B; /* Synchronized with accessories.php dark theme */
            transform: scale(1.2);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .banner-slide {
                height: 300px;
            }
            
            .banner-title {
                font-size: 1.8rem;
            }
            
            .banner-description {
                font-size: 1rem;
            }
            
            .banner-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-banner {
                width: 200px;
                justify-content: center;
            }
            
            #bannerCarousel .carousel-control {
                width: 40px;
                height: 40px;
            }
            
            #bannerCarousel .carousel-control span {
                width: 35px;
                height: 35px;
                font-size: 18px;
            }
            
            #bannerCarousel .carousel-control.left {
                left: 10px;
            }
            
            #bannerCarousel .carousel-control.right {
                right: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .banner-slide {
                height: 250px;
            }
            
            .banner-title {
                font-size: 1.5rem;
            }
            
            .banner-description {
                font-size: 0.9rem;
            }
        }
        </style>
        
        <script>
            console.log("Debug: Trang user/index.php đã load");
            console.log("User đã đăng nhập:", <?php echo isLoggedIn() ? 'true' : 'false'; ?>);
            <?php if ($current_user): ?>
            console.log("Thông tin user:", {
                user_id: "<?php echo $current_user['user_id']; ?>",
                username: "<?php echo $current_user['username']; ?>",
                full_name: "<?php echo $current_user['full_name']; ?>"
            });
            <?php else: ?>
            console.log("Không có thông tin user");
            <?php endif; ?>
            console.log("Session ID:", "<?php echo session_id(); ?>");
            console.log("Session status:", <?php echo session_status(); ?>);
            
            // Voucher Slider và Claim Functions
            $(document).ready(function() {
                // Initialize Voucher Slider
                if ($('#voucherSlider').length) {
                    var voucherSlider = $('#voucherSlider').owlCarousel({
                        items: 3,
                        loop: true,
                        margin: 20,
                        nav: false,
                        dots: true,
                        autoplay: true,
                        autoplayTimeout: 5000,
                        autoplayHoverPause: true,
                        responsive: {
                            0: {
                                items: 1
                            },
                            768: {
                                items: 2
                            },
                            992: {
                                items: 3
                            }
                        }
                    });
                    
                    // Custom Navigation
                    $('.voucher-prev').click(function() {
                        voucherSlider.trigger('prev.owl.carousel');
                    });
                    
                    $('.voucher-next').click(function() {
                        voucherSlider.trigger('next.owl.carousel');
                    });
                }
                
                // Claim Voucher Function
                $('.btn-claim-voucher').click(function() {
                    var button = $(this);
                    var voucherId = button.data('voucher-id');
                    var voucherCard = button.closest('.voucher-card');
                    
                    // Disable button and show loading
                    button.prop('disabled', true);
                    button.html('<i class="fa fa-spinner fa-spin"></i> Đang lấy...');
                    voucherCard.addClass('voucher-loading');
                    
                    $.ajax({
                        url: 'ajax/claim_voucher.php',
                        type: 'POST',
                        data: {
                            voucher_id: voucherId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Success animation
                                button.removeClass('btn-claim-voucher').addClass('btn-claimed claim-success');
                                button.html('<i class="fa fa-check"></i> Đã sở hữu');
                                
                                // Add owned class to card
                                voucherCard.addClass('owned');
                                
                                // Add owned badge
                                if (!voucherCard.find('.owned-badge').length) {
                                    voucherCard.append('<div class="owned-badge"><i class="fa fa-crown"></i></div>');
                                }
                                
                                // Show success message
                                showNotification('success', response.message);
                                
                                // Optional: Show voucher code
                                if (response.voucher_code) {
                                    setTimeout(function() {
                                        showNotification('info', 'Mã voucher của bạn: ' + response.voucher_code);
                                    }, 1500);
                                }
                            } else {
                                // Error handling
                                button.prop('disabled', false);
                                button.html('<i class="fa fa-gift"></i> Lấy voucher');
                                showNotification('error', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            // Network error handling
                            button.prop('disabled', false);
                            button.html('<i class="fa fa-gift"></i> Lấy voucher');
                            showNotification('error', 'Lỗi kết nối. Vui lòng thử lại sau.');
                            console.error('AJAX Error:', error);
                        },
                        complete: function() {
                            voucherCard.removeClass('voucher-loading');
                        }
                    });
                });
                
                // Notification Function
                function showNotification(type, message) {
                    var notificationClass = 'alert-success';
                    var icon = 'fa-check-circle';
                    
                    if (type === 'error') {
                        notificationClass = 'alert-danger';
                        icon = 'fa-exclamation-circle';
                    } else if (type === 'info') {
                        notificationClass = 'alert-info';
                        icon = 'fa-info-circle';
                    }
                    
                    var notification = $('<div class="voucher-notification alert ' + notificationClass + ' alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">' +
                        '<i class="fa ' + icon + ' me-2"></i>' + message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    
                    $('body').append(notification);
                    
                    // Auto remove after 5 seconds
                    setTimeout(function() {
                        notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
                
                // Voucher card hover effects
                $('.voucher-card').hover(
                    function() {
                        $(this).find('.voucher-header').css('transform', 'scale(1.02)');
                    },
                    function() {
                        $(this).find('.voucher-header').css('transform', 'scale(1)');
                    }
                );
            });
        </script>
        
    </body>
	
</html>