<?php
// Kết nối database và session
require_once '../config/database.php';
require_once 'includes/session.php';

// Tính toán ngày để xác định sản phẩm mới
$current_date = new DateTime();
$one_week_ago = clone $current_date;
$one_week_ago->sub(new DateInterval('P7D'));
$one_month_ago = clone $current_date;
$one_month_ago->sub(new DateInterval('P30D'));

// Query sản phẩm mới trong tuần
$week_sql = "SELECT p.*, a.artist_name, g.genre_name 
             FROM products p 
             LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
             LEFT JOIN artists a ON ap.artist_id = a.artist_id 
             LEFT JOIN genres g ON p.genre_id = g.genre_id 
             WHERE p.created_at >= ? 
             ORDER BY p.created_at DESC";

$week_stmt = $conn->prepare($week_sql);
$week_date = $one_week_ago->format('Y-m-d H:i:s');
$week_stmt->bind_param("s", $week_date);
$week_stmt->execute();
$week_products = $week_stmt->get_result();

// Query sản phẩm mới trong tháng (loại trừ sản phẩm trong tuần)
$month_sql = "SELECT p.*, a.artist_name, g.genre_name 
              FROM products p 
              LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
              LEFT JOIN artists a ON ap.artist_id = a.artist_id 
              LEFT JOIN genres g ON p.genre_id = g.genre_id 
              WHERE p.created_at >= ? AND p.created_at < ? 
              ORDER BY p.created_at DESC";

$month_stmt = $conn->prepare($month_sql);
$month_date = $one_month_ago->format('Y-m-d H:i:s');
$week_date = $one_week_ago->format('Y-m-d H:i:s');
$month_stmt->bind_param("ss", $month_date, $week_date);
$month_stmt->execute();
$month_products = $month_stmt->get_result();

// Đếm số lượng sản phẩm
$week_count = $week_products->num_rows;
$month_count = $month_products->num_rows;
$total_count = $week_count + $month_count;
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!--font-family-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">
    
    <!-- title of site -->
    <title>New Arrivals - AuraDisc</title>

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
    <link rel="stylesheet" href="assets/css/bootsnav.css">	
    
    <!--style.css-->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!--responsive.css-->
    <link rel="stylesheet" href="assets/css/responsive.css">

    <!-- Footer CSS -->
    <link rel="stylesheet" href="assets/css/footer.css">

    <style>
        .new-arrivals-header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 80px 0 40px;
            margin-top: -1px;
        }
        
        .stats-bar {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            margin-top: 30px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .stats-item {
            text-align: center;
            color: white;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section-divider {
            background: #ff6b35;
            color: white;
            padding: 15px 0;
            margin: 40px 0 30px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .product-artist {
            color: #ff6b35;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .product-genre {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ff6b35;
            margin-bottom: 15px;
        }
        
        .product-date {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 15px;
        }
        
        .new-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .week-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-add-cart {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-add-cart:hover {
            background: linear-gradient(135deg, #e55a2b, #e08420);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .btn-view-product {
            display: block;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-view-product:hover {
            background: linear-gradient(135deg, #e55a2b, #e08420);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-products i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .page-breadcrumb {
            background: #f8f9fa;
            padding: 15px 0;
            margin-bottom: 0;
        }
        
        .breadcrumb {
            background: none;
            margin: 0;
            padding: 0;
        }
        
        .breadcrumb > li + li:before {
            color: #ff6b35;
        }
        
        .breadcrumb > .active {
            color: #ff6b35;
        }

        /* Search Container */
        .search-container {
            max-width: 600px;
            margin: 40px auto;
            position: relative;
        }

        .search-box {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 16px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.2);
        }

        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff6b35;
            font-size: 18px;
        }

        /* Product card hidden state */
        .product-card.hidden,
        .col-md-3.hidden {
            display: none;
        }

        /* Filter results message */
        .filter-results {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            color: #666;
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
    <!-- Navigation End -->

    <!-- Breadcrumb -->
    <!-- <div class="page-breadcrumb">
        <div class="container">
            <ol class="breadcrumb">
                <li><a href="index.php">Home</a></li>
                <li class="active">New Arrivals</li>
            </ol>
        </div>
    </div> -->
    
    <!-- Header Section -->
    <div class="new-arrivals-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center">
                        <h1><i class="fa fa-star"></i> New Arrivals</h1>
                        <p class="lead">Khám phá những album và đĩa nhạc mới nhất tại AuraDisc</p>
                        
                        <!-- Stats Bar -->
                        <div class="stats-bar">
                            <div class="row">
                                <div class="col-md-4 col-sm-4">
                                    <div class="stats-item">
                                        <span class="stats-number"><?php echo $week_count; ?></span>
                                        <span class="stats-label">Sản phẩm trong tuần</span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="stats-item">
                                        <span class="stats-number"><?php echo $month_count; ?></span>
                                        <span class="stats-label">Sản phẩm trong tháng</span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="stats-item">
                                        <span class="stats-number"><?php echo $total_count; ?></span>
                                        <span class="stats-label">Tổng sản phẩm mới</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tìm kiếm -->
        <div class="search-container">
            <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm sản phẩm mới...">
            <i class="fa fa-search search-icon"></i>
        </div>

        <!-- Filter results message -->
        <div id="filterResults" class="filter-results" style="display: none;">
            <i class="fa fa-filter"></i> <span id="filterCount">0</span> sản phẩm được tìm thấy
        </div>
        <!-- Sản phẩm mới trong tuần -->
        <?php if ($week_count > 0): ?>
        <div class="section-divider">
            <i class="fa fa-fire"></i> Sản phẩm mới trong tuần
        </div>
        
        <div class="row">
            <?php 
            $week_products->data_seek(0); // Reset pointer
            while ($product = $week_products->fetch_assoc()): 
                $created_date = new DateTime($product['created_at']);
                $days_ago = $current_date->diff($created_date)->days;
            ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="product-card" data-name="<?php echo strtolower($product['product_name']); ?>" data-artist="<?php echo strtolower($product['artist_name'] ?? ''); ?>" data-genre="<?php echo strtolower($product['genre_name'] ?? ''); ?>">
                    <div class="new-badge week-badge">
                        <?php echo $days_ago; ?> ngày trước
                    </div>
                    
                    <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200/ff6b35/ffffff?text=No+Image'; ?>"
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/300x200/ff6b35/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">

                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        
                        <?php if (!empty($product['artist_name'])): ?>
                        <div class="product-artist">
                            <i class="fa fa-user"></i> <?php echo htmlspecialchars($product['artist_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['genre_name'])): ?>
                        <div class="product-genre">
                            <i class="fa fa-music"></i> <?php echo htmlspecialchars($product['genre_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-price">
                            $<?php echo number_format($product['price'], 2); ?>
                        </div>
                        
                        <div class="product-date">
                            <i class="fa fa-calendar"></i> <?php echo $created_date->format('d/m/Y H:i'); ?>
                        </div>
                        
                        <a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="btn-view-product">
                            <i class="fa fa-eye"></i> Xem sản phẩm
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        
        <!-- Sản phẩm mới trong tháng -->
        <?php if ($month_count > 0): ?>
        <div class="section-divider">
            <i class="fa fa-clock-o"></i> Sản phẩm mới trong tháng
        </div>
        
        <div class="row">
            <?php 
            $month_products->data_seek(0); // Reset pointer
            while ($product = $month_products->fetch_assoc()): 
                $created_date = new DateTime($product['created_at']);
                $days_ago = $current_date->diff($created_date)->days;
            ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="product-card" data-name="<?php echo strtolower($product['product_name']); ?>" data-artist="<?php echo strtolower($product['artist_name'] ?? ''); ?>" data-genre="<?php echo strtolower($product['genre_name'] ?? ''); ?>">
                    <div class="new-badge">
                        <?php echo $days_ago; ?> ngày trước
                    </div>
                    
                    <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200/ff6b35/ffffff?text=No+Image'; ?>"
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/300x200/ff6b35/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">

                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        
                        <?php if (!empty($product['artist_name'])): ?>
                        <div class="product-artist">
                            <i class="fa fa-user"></i> <?php echo htmlspecialchars($product['artist_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['genre_name'])): ?>
                        <div class="product-genre">
                            <i class="fa fa-music"></i> <?php echo htmlspecialchars($product['genre_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-price">
                            $<?php echo number_format($product['price'], 2); ?>
                        </div>
                        
                        <div class="product-date">
                            <i class="fa fa-calendar"></i> <?php echo $created_date->format('d/m/Y H:i'); ?>
                        </div>
                        
                        <a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="btn-view-product">
                            <i class="fa fa-eye"></i> Xem sản phẩm
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        
        <!-- Không có sản phẩm nào -->
        <?php if ($total_count == 0): ?>
        <div class="no-products">
            <i class="fa fa-inbox"></i>
            <h3>Chưa có sản phẩm mới</h3>
            <p>Hiện tại chưa có sản phẩm nào được thêm trong 30 ngày qua.</p>
            <a href="products.php" class="btn-add-cart" style="width: auto; margin-top: 20px;">
                <i class="fa fa-eye"></i> Xem tất cả sản phẩm
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <!-- Search JS -->
    <script src="assets/js/custom-search.js"></script>

    <script>
        // Local search functionality for new arrivals page
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const productCards = document.querySelectorAll('.product-card[data-name]');
            const filterResults = document.getElementById('filterResults');
            const filterCount = document.getElementById('filterCount');
            let visibleCount = 0;

            productCards.forEach(card => {
                const productName = card.getAttribute('data-name') || '';
                const artistName = card.getAttribute('data-artist') || '';
                const genreName = card.getAttribute('data-genre') || '';

                // Search in product name, artist name, and genre name
                const isMatch = productName.includes(searchTerm) ||
                               artistName.includes(searchTerm) ||
                               genreName.includes(searchTerm);

                if (searchTerm === '' || isMatch) {
                    card.parentElement.classList.remove('hidden');
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.parentElement.classList.add('hidden');
                    card.classList.add('hidden');
                }
            });

            // Show/hide filter results message
            if (searchTerm === '') {
                filterResults.style.display = 'none';
            } else {
                filterResults.style.display = 'block';
                filterCount.textContent = visibleCount;
            }

            // Hide section dividers if no products in that section
            updateSectionVisibility();
        });

        function updateSectionVisibility() {
            // Check week section
            const weekSection = document.querySelector('.section-divider:first-of-type');
            const weekProducts = document.querySelectorAll('.week-badge');
            let weekVisible = 0;

            weekProducts.forEach(badge => {
                const card = badge.closest('.product-card');
                if (card && !card.classList.contains('hidden')) {
                    weekVisible++;
                }
            });

            if (weekSection) {
                const weekContainer = weekSection.nextElementSibling;
                if (weekVisible === 0) {
                    weekSection.style.display = 'none';
                    if (weekContainer) weekContainer.style.display = 'none';
                } else {
                    weekSection.style.display = 'block';
                    if (weekContainer) weekContainer.style.display = 'block';
                }
            }

            // Check month section
            const monthSection = document.querySelector('.section-divider:last-of-type');
            const allProducts = document.querySelectorAll('.product-card[data-name]');
            const monthProducts = Array.from(allProducts).filter(card =>
                !card.querySelector('.week-badge')
            );
            let monthVisible = 0;

            monthProducts.forEach(card => {
                if (!card.classList.contains('hidden')) {
                    monthVisible++;
                }
            });

            if (monthSection) {
                const monthContainer = monthSection.nextElementSibling;
                if (monthVisible === 0) {
                    monthSection.style.display = 'none';
                    if (monthContainer) monthContainer.style.display = 'none';
                } else {
                    monthSection.style.display = 'block';
                    if (monthContainer) monthContainer.style.display = 'block';
                }
            }
        }

        // Clear search when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = '';
            }
        });
    </script>
</body>
</html>

<?php
// Đóng kết nối
if (isset($week_stmt)) $week_stmt->close();
if (isset($month_stmt)) $month_stmt->close();
$conn->close();
?> 