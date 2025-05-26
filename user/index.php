<?php
// Kết nối database để lấy banner
require_once '../config/database.php';

// Lấy danh sách banner active
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
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                margin-bottom: 30px;
                transition: all 0.3s ease;
            }
            
            .artist-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            
            .artist-image {
                width: 100%;
                height: 250px;
                object-fit: cover;
            }
            
            .artist-info {
                padding: 20px;
                text-align: center;
            }
            
            .artist-name {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 10px;
                color: #333;
            }
            
            .artist-bio {
                color: #666;
                font-size: 0.9rem;
                line-height: 1.6;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .genre-card {
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
                border-radius: 15px;
                padding: 25px;
                color: white;
                margin-bottom: 30px;
                transition: all 0.3s ease;
            }
            
            .genre-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            
            .genre-name {
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 15px;
            }
            
            .genre-description {
                font-size: 0.9rem;
                opacity: 0.9;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
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
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                margin-bottom: 30px;
                transition: all 0.3s ease;
            }
            
            .product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            
            .product-image {
                width: 100%;
                height: 250px;
                object-fit: cover;
            }
            
            .product-info {
                padding: 20px;
                text-align: center;
            }
            
            .product-name {
                font-size: 1.3rem;
                font-weight: 600;
                margin-bottom: 10px;
                color: #333;
            }
            
            .product-price {
                color: #ff6b35;
                font-size: 1.2rem;
                font-weight: 700;
                margin-bottom: 15px;
            }
            
            .btn-view-more {
                display: inline-block;
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
                color: white;
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: 500;
                margin-top: 30px;
                transition: all 0.3s ease;
            }
            
            .btn-view-more:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
                color: white;
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
					<h2>Sản Phẩm Mới</h2>
					<p>Khám phá những album mới nhất từ các nghệ sĩ hàng đầu</p>
				</div>
				
					<div class="row">
					<?php foreach ($new_products as $product): ?>
					<div class="col-md-3 col-sm-6">
						<div class="product-card">
							<img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
								 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
								 class="product-image"
								 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
							<div class="product-info">
								<h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
								<div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
								<button class="btn-cart welcome-add-cart">
									<span class="lnr lnr-cart"></span> Thêm vào giỏ
								</button>
									</div>
									</div>
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
					<h2>Nghệ Sĩ Nổi Bật</h2>
					<p>Khám phá những nghệ sĩ tài năng và các tác phẩm âm nhạc độc đáo của họ</p>
				</div>
					<div class="row">
					<?php foreach ($artists as $artist): ?>
					<div class="col-md-3 col-sm-6">
						<div class="artist-card">
							<img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
								 alt="<?php echo htmlspecialchars($artist['artist_name']); ?>"
								 class="artist-image"
								 onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
							<div class="artist-info">
								<h3 class="artist-name"><?php echo htmlspecialchars($artist['artist_name']); ?></h3>
								<p class="artist-bio"><?php echo htmlspecialchars($artist['bio']); ?></p>
								</div>
							</div>
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
					<h2>Dòng Nhạc</h2>
					<p>Khám phá các dòng nhạc đa dạng và phong phú</p>
				</div>
					<div class="row">
					<?php foreach ($genres as $genre): ?>
					<div class="col-md-3 col-sm-6">
						<div class="genre-card">
							<h3 class="genre-name"><?php echo htmlspecialchars($genre['genre_name']); ?></h3>
							<p class="genre-description"><?php echo htmlspecialchars($genre['description']); ?></p>
								</div>
								</div>
					<?php endforeach; ?>
							</div>
				<div class="text-center">
					<a href="genres.php" class="btn-view-more">Xem tất cả dòng nhạc</a>
						</div>
								</div>
		</section><!--/.genres-->
		<!--genres end -->

		<!-- clients strat -->
		<section id="clients"  class="clients">
			<div class="container">
				<div class="owl-carousel owl-theme" id="client">
						<div class="item">
							<a href="#">
								<img src="assets/images/clients/c1.png" alt="brand-image" />
							</a>
						</div><!--/.item-->
						<div class="item">
							<a href="#">
								<img src="assets/images/clients/c2.png" alt="brand-image" />
							</a>
						</div><!--/.item-->
						<div class="item">
							<a href="#">
								<img src="assets/images/clients/c3.png" alt="brand-image" />
							</a>
						</div><!--/.item-->
						<div class="item">
							<a href="#">
								<img src="assets/images/clients/c4.png" alt="brand-image" />
							</a>
						</div><!--/.item-->
						<div class="item">
							<a href="#">
								<img src="assets/images/clients/c5.png" alt="brand-image" />
							</a>
						</div><!--/.item-->
					</div><!--/.owl-carousel-->

			</div><!--/.container-->

		</section><!--/.clients-->	
		<!-- clients end -->

		<!--newsletter strat -->
		<section id="newsletter"  class="newsletter">
			<div class="container">
				<div class="hm-footer-details">
					<div class="row">
						<div class=" col-md-3 col-sm-6 col-xs-12">
							<div class="hm-footer-widget">
								<div class="hm-foot-title">
									<h4>information</h4>
								</div><!--/.hm-foot-title-->
								<div class="hm-foot-menu">
									<ul>
										<li><a href="#">about us</a></li><!--/li-->
										<li><a href="#">contact us</a></li><!--/li-->
										<li><a href="#">news</a></li><!--/li-->
										<li><a href="#">store</a></li><!--/li-->
									</ul><!--/ul-->
								</div><!--/.hm-foot-menu-->
							</div><!--/.hm-footer-widget-->
						</div><!--/.col-->
						<div class=" col-md-3 col-sm-6 col-xs-12">
							<div class="hm-footer-widget">
								<div class="hm-foot-title">
									<h4>collections</h4>
								</div><!--/.hm-foot-title-->
								<div class="hm-foot-menu">
									<ul>
										<li><a href="#">wooden chair</a></li><!--/li-->
										<li><a href="#">royal cloth sofa</a></li><!--/li-->
										<li><a href="#">accent chair</a></li><!--/li-->
										<li><a href="#">bed</a></li><!--/li-->
										<li><a href="#">hanging lamp</a></li><!--/li-->
									</ul><!--/ul-->
								</div><!--/.hm-foot-menu-->
							</div><!--/.hm-footer-widget-->
						</div><!--/.col-->
						<div class=" col-md-3 col-sm-6 col-xs-12">
							<div class="hm-footer-widget">
								<div class="hm-foot-title">
									<h4>my accounts</h4>
								</div><!--/.hm-foot-title-->
								<div class="hm-foot-menu">
									<ul>
										<li><a href="#">my account</a></li><!--/li-->
										<li><a href="#">wishlist</a></li><!--/li-->
										<li><a href="#">Community</a></li><!--/li-->
										<li><a href="#">order history</a></li><!--/li-->
										<li><a href="#">my cart</a></li><!--/li-->
									</ul><!--/ul-->
								</div><!--/.hm-foot-menu-->
							</div><!--/.hm-footer-widget-->
						</div><!--/.col-->
						<div class=" col-md-3 col-sm-6  col-xs-12">
							<div class="hm-footer-widget">
								<div class="hm-foot-title">
									<h4>newsletter</h4>
								</div><!--/.hm-foot-title-->
								<div class="hm-foot-para">
									<p>
										Subscribe  to get latest news,update and information.
									</p>
								</div><!--/.hm-foot-para-->
								<div class="hm-foot-email">
									<div class="foot-email-box">
										<input type="text" class="form-control" placeholder="Enter Email Here....">
									</div><!--/.foot-email-box-->
									<div class="foot-email-subscribe">
										<span><i class="fa fa-location-arrow"></i></span>
									</div><!--/.foot-email-icon-->
								</div><!--/.hm-foot-email-->
							</div><!--/.hm-footer-widget-->
						</div><!--/.col-->
					</div><!--/.row-->
				</div><!--/.hm-footer-details-->

			</div><!--/.container-->

		</section><!--/newsletter-->	
		<!--newsletter end -->

		<!--footer start-->
		<footer id="footer"  class="footer">
			<div class="container">
				<div class="hm-footer-copyright text-center">
					<div class="footer-social">
						<a href="#"><i class="fa fa-facebook"></i></a>	
						<a href="#"><i class="fa fa-instagram"></i></a>
						<a href="#"><i class="fa fa-linkedin"></i></a>
						<a href="#"><i class="fa fa-pinterest"></i></a>
						<a href="#"><i class="fa fa-behance"></i></a>	
					</div>
					<p>
						&copy;copyright. designed and developed by <a href="https://www.themesine.com/">themesine</a>
					</p><!--/p-->
				</div><!--/.text-center-->
			</div><!--/.container-->

			<div id="scroll-Top">
				<div class="return-to-top">
					<i class="fa fa-angle-up " id="scroll-top" data-toggle="tooltip" data-placement="top" title="" data-original-title="Back to Top" aria-hidden="true"></i>
				</div>
				
			</div><!--/.scroll-Top-->
			
        </footer><!--/.footer-->
		<!--footer end-->
		
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
        
        <!-- Banner Slider CSS -->
        <style>
        /* Navigation adjustments when no hero section */
        .top-area {
            position: relative;
            z-index: 999;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Index page navigation styling - white text with glow effect */
        .navbar-nav > li > a {
            color: #fff !important;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 
                         0 0 20px rgba(255, 255, 255, 0.6),
                         0 0 30px rgba(255, 255, 255, 0.4);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .navbar-nav > li > a:hover {
            color: #ff6b35 !important;
            text-shadow: 0 0 15px rgba(255, 107, 53, 0.8),
                         0 0 25px rgba(255, 107, 53, 0.6),
                         0 0 35px rgba(255, 107, 53, 0.4);
            transform: translateY(-2px);
        }
        
        /* Navigation icons styling */
        .navbar-nav > li > a i {
            color: #fff !important;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.8),
                         0 0 16px rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }
        
        .navbar-nav > li > a:hover i {
            color: #ff6b35 !important;
            text-shadow: 0 0 12px rgba(255, 107, 53, 0.8),
                         0 0 20px rgba(255, 107, 53, 0.6);
            transform: scale(1.1);
        }
        
        /* Brand logo styling */
        .navbar-brand {
            color: #fff !important;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.8),
                         0 0 25px rgba(255, 255, 255, 0.6);
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: #ff6b35 !important;
            text-shadow: 0 0 20px rgba(255, 107, 53, 0.8),
                         0 0 30px rgba(255, 107, 53, 0.6);
        }
        
        /* Navigation background with gradient overlay */
        .top-area {
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.9), rgba(247, 147, 30, 0.8)) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        
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
            background: rgba(255, 107, 53, 0.95) !important;
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        
        .dropdown-menu > li > a {
            color: #fff !important;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }
        
        .dropdown-menu > li > a:hover {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.8);
        }
        
        /* Search box styling */
        .navbar-form input {
            background: rgba(255, 255, 255, 0.2) !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
            placeholder-color: rgba(255, 255, 255, 0.7);
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
        @keyframes navGlow {
            0% { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); }
            50% { text-shadow: 0 0 20px rgba(255, 255, 255, 1), 0 0 30px rgba(255, 255, 255, 0.8); }
            100% { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); }
        }
        
        .navbar-nav > li > a {
            animation: navGlow 3s ease-in-out infinite;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar-nav > li > a {
                text-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
            }
            
            .top-area {
                background: linear-gradient(135deg, rgba(255, 107, 53, 0.95), rgba(247, 147, 30, 0.9)) !important;
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
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.8), rgba(247, 147, 30, 0.6));
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
            color: #ff6b35;
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
            color: #ff6b35;
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
            background: rgba(255, 107, 53, 0.8);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        #bannerCarousel .carousel-control:hover span {
            background: rgba(255, 107, 53, 1);
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
            background: #ff6b35;
            border-color: #ff6b35;
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
        
    </body>
	
</html>