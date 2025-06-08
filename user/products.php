<?php
// Kết nối database
require_once '../config/database.php';

// Lấy artist_id hoặc genre_id từ URL
$artist_id = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;

$artist = null;
$genre = null;
$products = [];

if ($artist_id > 0) {
    // Lấy thông tin nghệ sĩ
    $artist_sql = "SELECT * FROM artists WHERE artist_id = ? AND status = 1";
    $artist_stmt = $conn->prepare($artist_sql);
    $artist_stmt->bind_param("i", $artist_id);
    $artist_stmt->execute();
    $artist_result = $artist_stmt->get_result();

    if ($artist_result->num_rows == 0) {
        header('Location: Artists/Artists.php');
        exit;
    }

    $artist = $artist_result->fetch_assoc();

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

    if ($products_result->num_rows > 0) {
        while($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} elseif ($genre_id > 0) {
    // Lấy thông tin thể loại
    $genre_sql = "SELECT * FROM genres WHERE genre_id = ?";
    $genre_stmt = $conn->prepare($genre_sql);
    $genre_stmt->bind_param("i", $genre_id);
    $genre_stmt->execute();
    $genre_result = $genre_stmt->get_result();

    if ($genre_result->num_rows == 0) {
        header('Location: genre/genres.php');
        exit;
    }

    $genre = $genre_result->fetch_assoc();

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

    if ($products_result->num_rows > 0) {
        while($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    // Nếu không có artist_id hoặc genre_id, redirect về trang chủ
    header('Location: index.php');
    exit;
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
        .artist-header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .artist-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255,255,255,0.3);
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .artist-name {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .artist-bio {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 25px;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .product-genre {
            color: #ff6b35;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .stock-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .out-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-bar {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            text-align: center;
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .stats-item {
            display: inline-block;
            margin: 0 30px;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .product-actions {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-view-product {
            display: block;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .btn-view-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-view-product i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .artist-name {
                font-size: 2rem;
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
    <!-- Include Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Artist/Genre Header -->
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
                <?php endif; ?>
            </div>
        </div>

        <div class="container">
            <!-- Thống kê -->
            <div class="stats-bar">
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
            </div>

            <!-- Danh sách sản phẩm -->
            <?php if (count($products) > 0): ?>
            <h2 class="text-center mb-40">
                <i class="fa fa-music"></i> Sản phẩm của <?php echo htmlspecialchars($artist['artist_name']); ?>
            </h2>
            
            <div class="grid-container grid-2">
                <?php foreach ($products as $product): ?>
                <div class="product-card fade-in">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         class="product-image" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/300x250/667eea/ffffff?text=<?php echo urlencode($product['product_name']); ?>'">
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        
                        <?php if (!empty($product['genre_name'])): ?>
                        <div class="product-genre">
                            <i class="fa fa-music"></i> <?php echo htmlspecialchars($product['genre_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($genre && !empty($product['artist_name'])): ?>
                        <div class="product-artist" style="color: #ff6b35; font-size: 0.9rem; font-weight: 500; margin-bottom: 10px;">
                            <i class="fa fa-user"></i> <?php echo htmlspecialchars($product['artist_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <div class="product-footer">
                            <div class="product-price">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                            </div>
                            
                            <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                <?php if ($product['stock'] > 0): ?>
                                    <i class="fa fa-check-circle"></i> Còn hàng (<?php echo $product['stock']; ?>)
                                <?php else: ?>
                                    <i class="fa fa-times-circle"></i> Hết hàng
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" class="btn-view-product">
                                <i class="fa fa-eye"></i> Xem sản phẩm
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fa fa-music"></i>
                <p><?php echo htmlspecialchars($artist['artist_name']); ?> chưa có sản phẩm nào.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>
    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Bootsnav JS -->
    <script src="assets/js/bootsnav.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Hiệu ứng xuất hiện từ từ cho sản phẩm
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('visible');
                }, index * 150);
            });
        });
    </script>
</body>
</html> 