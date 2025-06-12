<?php
// Kết nối database
require_once '../config/database.php';

// Include session functions
require_once 'includes/session.php';

// Debug mode
$debug = isset($_GET['debug']) ? true : false;

// Lấy artist_id hoặc genre_id từ URL
$artist_id = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;

if ($debug) {
    echo "Debug: artist_id = $artist_id, genre_id = $genre_id<br>";
}

$artist = null;
$genre = null;
$products = [];
$error_message = '';

if ($artist_id > 0) {
    // Lấy thông tin nghệ sĩ
    $artist_sql = "SELECT * FROM artists WHERE artist_id = ?";
    $artist_stmt = $conn->prepare($artist_sql);
    $artist_stmt->bind_param("i", $artist_id);
    $artist_stmt->execute();
    $artist_result = $artist_stmt->get_result();

    if ($debug) {
        echo "Debug: Found " . $artist_result->num_rows . " artists<br>";
    }

    if ($artist_result->num_rows == 0) {
        $error_message = "Không tìm thấy nghệ sĩ với ID: $artist_id";
        if (!$debug) {
            header('Location: Artists/Artists.php');
            exit;
        }
    } else {
        $artist = $artist_result->fetch_assoc();
        
        if ($debug) {
            echo "Debug: Artist found: " . $artist['artist_name'] . "<br>";
        }

        // Lấy danh sách sản phẩm của nghệ sĩ
        $products_sql = "SELECT p.*, g.genre_name 
                         FROM products p 
                         LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
                         LEFT JOIN genres g ON p.genre_id = g.genre_id
                         WHERE ap.artist_id = ? 
                         ORDER BY p.created_at DESC";

        $products_stmt = $conn->prepare($products_sql);
        $products_stmt->bind_param("i", $artist_id);
        $products_stmt->execute();
        $products_result = $products_stmt->get_result();

        if ($debug) {
            echo "Debug: Found " . $products_result->num_rows . " products for artist<br>";
        }

        if ($products_result->num_rows > 0) {
            while($row = $products_result->fetch_assoc()) {
                $products[] = $row;
            }
        }
    }
} elseif ($genre_id > 0) {
    // Lấy thông tin thể loại
    $genre_sql = "SELECT * FROM genres WHERE genre_id = ?";
    $genre_stmt = $conn->prepare($genre_sql);
    $genre_stmt->bind_param("i", $genre_id);
    $genre_stmt->execute();
    $genre_result = $genre_stmt->get_result();

    if ($debug) {
        echo "Debug: Found " . $genre_result->num_rows . " genres<br>";
    }

    if ($genre_result->num_rows == 0) {
        $error_message = "Không tìm thấy thể loại với ID: $genre_id";
        if (!$debug) {
            header('Location: genre/genres.php');
            exit;
        }
    } else {
        $genre = $genre_result->fetch_assoc();
        
        if ($debug) {
            echo "Debug: Genre found: " . $genre['genre_name'] . "<br>";
        }

        // Lấy danh sách sản phẩm theo thể loại
        $products_sql = "SELECT p.*, g.genre_name, a.artist_name
                         FROM products p 
                         LEFT JOIN genres g ON p.genre_id = g.genre_id
                         LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
                         LEFT JOIN artists a ON ap.artist_id = a.artist_id
                         WHERE p.genre_id = ? 
                         ORDER BY p.created_at DESC";

        $products_stmt = $conn->prepare($products_sql);
        $products_stmt->bind_param("i", $genre_id);
        $products_stmt->execute();
        $products_result = $products_stmt->get_result();

        if ($debug) {
            echo "Debug: Found " . $products_result->num_rows . " products for genre<br>";
        }

        if ($products_result->num_rows > 0) {
            while($row = $products_result->fetch_assoc()) {
                $products[] = $row;
            }
        }
    }
} else {
    // Nếu không có artist_id hoặc genre_id, redirect về trang chủ
    $error_message = "Thiếu tham số artist_id hoặc genre_id";
    if (!$debug) {
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if ($artist): ?>
            Sản phẩm của <?php echo htmlspecialchars($artist['artist_name']); ?> - AuraDisc
        <?php elseif ($genre): ?>
            Sản phẩm thể loại <?php echo htmlspecialchars($genre['genre_name']); ?> - AuraDisc
        <?php endif; ?>
    </title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">
    <!-- Linear Icons -->
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <!-- Bootsnav CSS -->
    <link href="assets/css/bootsnav.css" rel="stylesheet">
    <!-- Main CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Responsive CSS -->
    <link href="assets/css/responsive.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Common CSS -->
    <link rel="stylesheet" href="includes/common.css">
    
    <style>
        .main-content {
            padding-top: 0;
        }
        
        .artist-header {
            background: #deccca;
            color: #412d3b;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
        }
        
        .artist-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="90" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="30" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .artist-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(65, 45, 59, 0.3);
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .artist-name {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #412d3b;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }
        
        .artist-bio {
            font-size: 1.3rem;
            color: #412d3b;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }
        
        .product-card {
            background: #f7f8f9;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            height: 100%;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease forwards;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .product-card:active {
            transform: translateY(0);
            transition: transform 0.1s;
        }
        
        .product-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.03);
        }
        
        .product-info {
            padding: 20px;
            text-align: center;
        }
        
        .product-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }

        .product-card:hover .product-name {
            color: #412d3b;
        }
        
        .product-genre, .product-artist {
            color: #412d3b !important;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }
        
        .product-price {
            font-size: 1.15rem;
            color: #412d3b;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .stock-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .in-stock {
            background: #deccca;
            color: #412d3b;
        }
        
        .out-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-bar {
            background: rgba(65, 45, 59, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            text-align: center;
            color: #412d3b;
            backdrop-filter: blur(10px);
        }
        
        .stats-item {
            display: inline-block;
            margin: 0 30px;
            color: #412d3b;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
            color: #412d3b;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #412d3b;
        }

        /* Search styles from accessories.php */
        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 30px;
        }

        .search-box {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #deccca;
            border-radius: 30px;
            font-size: 1rem;
            color: #412d3b;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #412d3b;
            box-shadow: 0 0 10px rgba(65, 45, 59, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #412d3b;
        }

        .btn-view-product {
            background: #412d3b;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        
        .btn-view-product:hover {
            background: #deccca;
            color: #412d3b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4);
            text-decoration: none;
        }

        .product-card.hidden {
            display: none;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .artist-name {
                font-size: 2.5rem;
            }
            
            .artist-bio {
                font-size: 1.1rem;
            }
            
            .artist-image {
                width: 150px;
                height: 150px;
            }
            
            .stats-item {
                margin: 0 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Include Navigation (Simplified) -->
    <?php include 'includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Artist/Genre Header -->
        <?php if ($artist || $genre || $debug): ?>
        <div class="artist-header">
            <div class="container">
                <?php if ($artist): ?>
                <img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
                     class="artist-image" 
                     alt="<?php echo htmlspecialchars($artist['artist_name']); ?>"
                     onerror="this.src='https://via.placeholder.com/200x200/ff6b35/ffffff?text=<?php echo urlencode($artist['artist_name']); ?>'">
                
                <h1 class="artist-name"><?php echo htmlspecialchars($artist['artist_name']); ?></h1>
                <p class="artist-bio"><?php echo htmlspecialchars($artist['bio']); ?></p>
                
                <?php elseif ($genre): ?>
                <div class="genre-icon" style="font-size: 4rem; margin-bottom: 20px;">
                    <i class="fa fa-music"></i>
                </div>
                <h1 class="artist-name"><?php echo htmlspecialchars($genre['genre_name']); ?></h1>
                <p class="artist-bio"><?php echo htmlspecialchars($genre['description']); ?></p>
                
                <?php elseif ($debug): ?>
                <div class="genre-icon" style="font-size: 4rem; margin-bottom: 20px;">
                    <i class="fa fa-bug"></i>
                </div>
                <h1 class="artist-name">Debug Mode</h1>
                <p class="artist-bio">Đang kiểm tra dữ liệu...</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container">
            <?php if ($debug): ?>
            <div class="alert alert-info">
                <h4>🔍 Debug Info:</h4>
                <p><strong>artist_id:</strong> <?php echo $artist_id; ?></p>
                <p><strong>genre_id:</strong> <?php echo $genre_id; ?></p>
                <p><strong>Artist found:</strong> <?php echo $artist ? '✅ Yes (' . $artist['artist_name'] . ')' : '❌ No'; ?></p>
                <p><strong>Genre found:</strong> <?php echo $genre ? '✅ Yes (' . $genre['genre_name'] . ')' : '❌ No'; ?></p>
                <p><strong>Products count:</strong> <?php echo count($products); ?></p>
                <?php if ($error_message): ?>
                <p><strong>Error:</strong> <span style="color: red;"><?php echo $error_message; ?></span></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Thống kê -->
            <!-- <div class="stats-bar">
                <div class="stats-item">
                    <strong><?php echo count($products); ?></strong>
                    <span>Sản phẩm</span>
                </div>
                <?php if ($artist): ?>
                <div class="stats-item">
                    <strong><?php echo htmlspecialchars($artist['artist_name']); ?></strong>
                    <span>Nghệ sĩ</span>
                </div>
                <?php elseif ($genre): ?>
                <div class="stats-item">
                    <strong><?php echo htmlspecialchars($genre['genre_name']); ?></strong>
                    <span>Thể loại</span>
                </div>
                <?php endif; ?>
            </div> -->

            <!-- Danh sách sản phẩm -->
            <?php if (count($products) > 0): ?>
            <!-- <h2 class="text-center mb-40">
                <i class="fa fa-music"></i> 
                <?php if ($artist): ?>
                    Sản phẩm của <?php echo htmlspecialchars($artist['artist_name']); ?>
                <?php elseif ($genre): ?>
                    Sản phẩm thể loại <?php echo htmlspecialchars($genre['genre_name']); ?>
                <?php endif; ?>
            </h2> -->

            <!-- Search Box -->
            <div class="search-container" style="max-width: 700px; margin: 40px auto;">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm sản phẩm..." style="padding: 18px 25px; font-size: 1.1rem;">
                <i class="fa fa-search search-icon" style="font-size: 1.2rem;"></i>
            </div>
            
            <div class="row">
                <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="product-card" data-name="<?php echo strtolower($product['product_name']); ?>" onclick="window.location.href='product-detail.php?type=product&id=<?php echo $product['product_id']; ?>'" style="cursor: pointer;">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             class="product-image" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x250/667eea/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">
                        
                        <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php if ($product['stock'] > 0): ?>
                                <i class="fa fa-check-circle"></i> Còn hàng (<?php echo $product['stock']; ?>)
                            <?php else: ?>
                                <i class="fa fa-times-circle"></i> Hết hàng
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            
                            <?php if (!empty($product['genre_name'])): ?>
                            <div class="product-genre">
                                <i class="fa fa-music"></i> <?php echo htmlspecialchars($product['genre_name']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($genre && !empty($product['artist_name'])): ?>
                            <div class="product-artist">
                                <i class="fa fa-user"></i> <?php echo htmlspecialchars($product['artist_name']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-price">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fa fa-music"></i>
                <p>
                    <?php if ($artist): ?>
                        <?php echo htmlspecialchars($artist['artist_name']); ?> chưa có sản phẩm nào.
                    <?php elseif ($genre): ?>
                        Thể loại <?php echo htmlspecialchars($genre['genre_name']); ?> chưa có sản phẩm nào.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include 'includes/chat-widget.php'; ?>

    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Bootsnav JS -->
    <script src="assets/js/bootsnav.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Search JS -->
    <script src="assets/js/custom-search.js"></script>

    <!-- Chat Widget CSS -->
    <link rel="stylesheet" href="includes/chat-widget.css">

    <!-- Chat Widget JS -->
    <script src="includes/chat-widget.js"></script>

    <script>
        <?php if ($debug): ?>
        // Debug logging
        console.log('🐛 Products.php loaded');
        console.log('🐛 Products count: ' + document.querySelectorAll('.product-card').length);
        <?php endif; ?>
        
        // Tìm kiếm sản phẩm
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const productName = card.getAttribute('data-name');
                if (productName.includes(searchTerm)) {
                    card.closest('.col-md-6').style.display = 'block';
                } else {
                    card.closest('.col-md-6').style.display = 'none';
                }
            });
        });

        // Hiệu ứng xuất hiện từ từ
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html> 