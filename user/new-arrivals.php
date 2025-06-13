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
        .main-content {
            padding-top: 100px;
            padding-bottom: 80px; /* Thêm khoảng cách với newsletter */
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            padding: 40px 0;
        }

        @media (max-width: 1200px) {
            .grid-container {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(1, 1fr);
            }
        }
        
        .product-card {
            background: #f7f8f9; /* Synchronized with accessories.php */
            border-radius: 20px; /* Synchronized with accessories.php */
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); /* Synchronized with accessories.php */
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
            text-align: center; /* Synchronized with accessories.php */
        }
        
        .product-title {
            font-size: 1.25rem; /* Synchronized with accessories.php */
            font-weight: 600;
            color: #333;
            margin-bottom: 8px; /* Synchronized with accessories.php */
            min-height: 30px; /* Synchronized with accessories.php */
            line-height: 1.2; /* Synchronized with accessories.php */
            /* Removed: white-space: nowrap; overflow: hidden; text-overflow: ellipsis; */
        }
        
        .product-artist {
            color: #412D3B; /* Synchronized with accessories.php price/stock color */
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .product-genre {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 1.15rem; /* Synchronized with accessories.php */
            font-weight: bold;
            color: #412D3B; /* Synchronized with accessories.php */
            margin-bottom: 0px; /* Synchronized with accessories.php */
        }
        
        .product-date {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 15px;
        }
        
        .new-badge {
            position: absolute;
            top: 15px; /* Synchronized with accessories.php */
            right: 15px; /* Synchronized with accessories.php */
            padding: 4px 8px; /* Synchronized with accessories.php */
            border-radius: 12px; /* Synchronized with accessories.php */
            font-size: 0.75rem; /* Synchronized with accessories.php */
            font-weight: 600;
            background: #deccca; /* Synchronized with accessories.php */
            color: #412D3B; /* Synchronized with accessories.php */
            box-shadow: none; /* Removed */
        }
        
        .week-badge {
            background: #deccca; /* Synchronized with accessories.php */
            animation: none; /* Removed animation */
        }
        
        /* Removed @keyframes pulse */
        
        .btn-add-cart,
        .btn-view-product { /* Combined and removed styles */
            display: none; /* Hide the button */
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
            border-color: #412D3B; /* Reverted to accessories.php style */
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.2); /* Reverted to accessories.php style */
        }

        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #412D3B; /* Reverted to accessories.php style */
            font-size: 18px;
        }

        /* Product card hidden state */
        .product-card.hidden {
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

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

    <div class="main-content">
        <!-- Header Section -->
        <?php /* Removed Header Section and Stats Bar */ ?>

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
            
            <div class="grid-container" id="weekProductsGrid">
                <?php 
                $week_products->data_seek(0); // Reset pointer
                while ($product = $week_products->fetch_assoc()): 
                    $created_date = new DateTime($product['created_at']);
                    $days_ago = $current_date->diff($created_date)->days;
                ?>
                <a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="product-card fade-in" data-name="<?php echo strtolower($product['product_name']); ?>" data-artist="<?php echo strtolower($product['artist_name'] ?? ''); ?>" data-genre="<?php echo strtolower($product['genre_name'] ?? ''); ?>">
                    <div class="position-relative">
                        <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200/ff6b35/ffffff?text=No+Image'; ?>"
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/300x200/ff6b35/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">

                        <div class="new-badge week-badge">
                            <?php echo $days_ago; ?> ngày trước
                        </div>
                    </div>
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
                        
                        <?php /* Removed product-date */ ?>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
            
            <!-- Sản phẩm mới trong tháng -->
            <?php if ($month_count > 0): ?>
            <div class="section-divider">
                <i class="fa fa-clock-o"></i> Sản phẩm mới trong tháng
            </div>
            
            <div class="grid-container" id="monthProductsGrid">
                <?php 
                $month_products->data_seek(0); // Reset pointer
                while ($product = $month_products->fetch_assoc()): 
                    $created_date = new DateTime($product['created_at']);
                    $days_ago = $current_date->diff($created_date)->days;
                ?>
                <a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="product-card fade-in" data-name="<?php echo strtolower($product['product_name']); ?>" data-artist="<?php echo strtolower($product['artist_name'] ?? ''); ?>" data-genre="<?php echo strtolower($product['genre_name'] ?? ''); ?>">
                    <div class="position-relative">
                        <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200/ff6b35/ffffff?text=No+Image'; ?>"
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/300x200/ff6b35/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">

                        <div class="new-badge">
                            <?php echo $days_ago; ?> ngày trước
                        </div>
                    </div>
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
                        
                        <?php /* Removed product-date */ ?>
                    </div>
                </a>
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
            
            <!-- Thêm khoảng trống cuối trang -->
            <div style="height: 60px;"></div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include 'includes/chat-widget.php'; ?>

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

    <!-- Chat Widget CSS -->
    <link rel="stylesheet" href="includes/chat-widget.css">

    <!-- Chat Widget JS -->
    <script src="includes/chat-widget.js"></script>

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
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
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
            const weekProducts = document.querySelectorAll('#weekProductsGrid .product-card'); // Target within specific grid
            let weekVisible = 0;

            weekProducts.forEach(card => {
                if (!card.classList.contains('hidden')) {
                    weekVisible++;
                }
            });

            if (weekSection) {
                if (weekVisible === 0) {
                    weekSection.style.display = 'none';
                } else {
                    weekSection.style.display = 'block';
                }
            }

            // Check month section
            const monthSection = document.querySelector('.section-divider:last-of-type');
            const monthProducts = document.querySelectorAll('#monthProductsGrid .product-card'); // Target within specific grid
            let monthVisible = 0;

            monthProducts.forEach(card => {
                if (!card.classList.contains('hidden')) {
                    monthVisible++;
                }
            });

            if (monthSection) {
                if (monthVisible === 0) {
                    monthSection.style.display = 'none';
                } else {
                    monthSection.style.display = 'block';
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