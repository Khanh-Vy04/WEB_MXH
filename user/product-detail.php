<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy thông tin user nếu đã đăng nhập
$current_user = getCurrentUser();
$is_logged_in = isLoggedIn();

// Lấy tham số từ URL
$type = isset($_GET['type']) ? $_GET['type'] : 'product'; // product hoặc accessory
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$product_data = null;
$artist_info = null;
$reviews = [];
$related_products = [];

if ($type === 'product') {
    // Lấy thông tin sản phẩm
    $sql = "SELECT p.*, g.genre_name 
            FROM products p 
            LEFT JOIN genres g ON p.genre_id = g.genre_id 
            WHERE p.product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product_data = $result->fetch_assoc();
        
        // Lấy thông tin nghệ sĩ
        $artist_sql = "SELECT a.* FROM artists a 
                       JOIN artist_products ap ON a.artist_id = ap.artist_id 
                       WHERE ap.product_id = ?";
        $artist_stmt = $conn->prepare($artist_sql);
        $artist_stmt->bind_param("i", $id);
        $artist_stmt->execute();
        $artist_result = $artist_stmt->get_result();
        if ($artist_result->num_rows > 0) {
            $artist_info = $artist_result->fetch_assoc();
        }
        
        // Lấy đánh giá
        $review_sql = "SELECT r.*, u.username, u.full_name 
                       FROM reviews r 
                       JOIN users u ON r.buyer_id = u.user_id 
                       WHERE r.product_id = ? 
                       ORDER BY r.created_at DESC LIMIT 5";
        $review_stmt = $conn->prepare($review_sql);
        $review_stmt->bind_param("i", $id);
        $review_stmt->execute();
        $review_result = $review_stmt->get_result();
        while ($row = $review_result->fetch_assoc()) {
            $reviews[] = $row;
        }
        
        // Lấy sản phẩm liên quan
        $related_sql = "SELECT p.* FROM products p 
                        LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
                        WHERE p.product_id != ? 
                        AND (ap.artist_id IN (
                            SELECT artist_id FROM artist_products WHERE product_id = ?
                        ) OR p.genre_id = ?) 
                        LIMIT 4";
        $related_stmt = $conn->prepare($related_sql);
        $related_stmt->bind_param("iii", $id, $id, $product_data['genre_id']);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        while ($row = $related_result->fetch_assoc()) {
            $related_products[] = $row;
        }
    }
} else {
    // Lấy thông tin accessory
    $sql = "SELECT * FROM accessories WHERE accessory_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product_data = $result->fetch_assoc();
        // Chuyển đổi tên trường để thống nhất
        $product_data['product_name'] = $product_data['accessory_name'];
        $product_data['product_id'] = $product_data['accessory_id'];
        
        // Lấy accessories liên quan
        $related_sql = "SELECT * FROM accessories WHERE accessory_id != ? LIMIT 4";
        $related_stmt = $conn->prepare($related_sql);
        $related_stmt->bind_param("i", $id);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        while ($row = $related_result->fetch_assoc()) {
            $row['product_name'] = $row['accessory_name'];
            $row['product_id'] = $row['accessory_id'];
            $related_products[] = $row;
        }
    }
}

if (!$product_data) {
    header("Location: index.php");
    exit();
}

// Tính trung bình rating
$avg_rating = 0;
$total_reviews = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $total_reviews = count($reviews);
    $avg_rating = $total_rating / $total_reviews;
}
?>

<!doctype html>
<html class="no-js" lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?php echo htmlspecialchars($product_data['product_name']); ?> - AuraDisc</title>
    
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
    
    <style>
        /* Product Detail Styles */
        .product-detail-section {
            padding: 140px 0 120px; /* Tăng padding-bottom để có khoảng cách với newsletter */
            background: #f8f9fa;
            margin-top: 20px;
        }
        
        .product-image-container {
            position: relative;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .product-main-image:hover {
            transform: scale(1.05);
        }
        
        .product-info-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .product-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .product-artist {
            color: #412d3b;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .product-artist i {
            color: #412d3b;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: 900;
            color: #412d3b;
            margin-bottom: 20px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .rating-text {
            color: #666;
            font-size: 0.9rem;
        }
        
        .product-stock {
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .in-stock {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn-add-cart {
            flex: 1;
            min-width: 200px;
            background: #deccca;
            color: #412d3b;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(222, 204, 202, 0.4);
            background: #412d3b;
            color: #deccca;
        }
        
        .btn-wishlist {
            background: #deccca;
            color: #412d3b;
            border: 2px solid #deccca;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 150px;
        }
        
        .btn-wishlist:hover {
            background: #412d3b;
            color: #deccca;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(222, 204, 202, 0.4);
            border-color: #412d3b;
        }
        
        .btn-wishlist.active {
            background: #412d3b;
            color: #deccca;
            border-color: #412d3b;
        }
        
        /* Remove hover effects for active state */
        .btn-wishlist.active:hover {
            background: #412d3b;
            color: #deccca;
            border-color: #412d3b;
            transform: none;
            box-shadow: none;
        }
        
        .product-meta {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 1.25rem;
        }
        
        .meta-label {
            color: #666;
            font-weight: 600;
        }
        
        .meta-value {
            color: #333;
        }
        
        /* Reviews Section */
        .reviews-section {
            margin-top: 50px;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .review-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-user {
            font-weight: 600;
            color: #333;
        }
        
        .review-date {
            color: #666;
            font-size: 0.85rem;
        }
        
        .review-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .review-comment {
            color: #666;
            line-height: 1.5;
        }
        
        /* Related Products */
        .related-section {
            margin-top: 50px;
        }
        
        .related-product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .related-product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .related-product-info {
            padding: 20px;
            text-align: center;
        }
        
        .related-product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .related-product-price {
            color: #412d3b;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .product-title {
                font-size: 1.8rem;
            }
            
            .product-price {
                font-size: 1.6rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .btn-add-cart,
            .btn-wishlist {
                min-width: 100%;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        /* Artist Info Box */
        .artist-info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
        }
        
        .artist-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .artist-details {
            color: #412d3b;
        }
        
        .artist-details h4 {
            margin: 0;
            color: #412d3b;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .artist-bio {
            color: #412d3b;
            font-size: 1.25rem;
            line-height: 1.6;
            margin: 0;
        }
        
        /* Quantity Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .quantity-label {
            font-weight: 600;
            color: #333;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #deccca;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: #deccca;
            color: #412d3b;
        }
        
        .quantity-btn:active {
            background: #deccca;
            color: #412d3b;
            transform: translateY(1px);
        }
        
        .quantity-input {
            border: none;
            padding: 10px 15px;
            text-align: center;
            width: 60px;
            font-weight: 600;
            background: white;
            font-size: 1.1rem;
            color: #412d3b;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transform: translateX(400px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .toast-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toast-content i {
            font-size: 1.2rem;
        }
        
        .toast-message {
            flex: 1;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .toast {
                right: 10px;
                left: 10px;
                max-width: none;
                transform: translateY(-100px);
            }
            
            .toast.show {
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

    <!-- Product Detail Section Start -->
    <section class="product-detail-section">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-6">
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($product_data['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product_data['product_name']); ?>"
                             class="product-main-image"
                             onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <div class="product-info-container">
                        <h1 class="product-title"><?php echo htmlspecialchars($product_data['product_name']); ?></h1>
                        
                        <?php if ($artist_info && $type === 'product'): ?>
                        <div class="artist-info-box">
                            <div class="artist-details">
                                <div class="artist-bio"><?php echo nl2br(htmlspecialchars($product_data['description'])); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($product_data['price'])): ?>
                        <div class="product-price">
                            <?php echo number_format($product_data['price']); ?>₫
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($reviews)): ?>
                        <div class="product-rating">
                            <div class="rating-stars">
                                <?php 
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= round($avg_rating) ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
                                }
                                ?>
                            </div>
                            <span class="rating-text">
                                <?php echo number_format($avg_rating, 1); ?>/5 (<?php echo $total_reviews; ?> đánh giá)
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($product_data['stock'])): ?>
                        <div class="product-stock <?php echo $product_data['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <i class="fa <?php echo $product_data['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                            <?php if ($product_data['stock'] > 0): ?>
                                Còn hàng (<?php echo $product_data['stock']; ?> <?php echo $type === 'product' ? 'sản phẩm' : 'phụ kiện'; ?>)
                            <?php else: ?>
                                Hết hàng
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($product_data['stock']) && $product_data['stock'] > 0): ?>
                        <div class="quantity-selector">
                            <span class="quantity-label">Số lượng:</span>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?php echo $product_data['stock']; ?>">
                                <button type="button" class="quantity-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-actions">
                            <?php if ($type === 'product' && isset($product_data['stock']) && $product_data['stock'] > 0): ?>
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $id; ?>, 'product')">
                                <i class="fa fa-shopping-cart"></i>
                                Thêm vào giỏ hàng
                            </button>
                            <?php elseif ($type === 'accessory' && isset($product_data['stock']) && $product_data['stock'] > 0): ?>
                            <button class="btn-add-cart" onclick="addToCart(<?php echo $id; ?>, 'accessory')">
                                <i class="fa fa-shopping-cart"></i>
                                Thêm vào giỏ hàng
                            </button>
                            <?php endif; ?>
                            
                            <button class="btn-wishlist" onclick="addToWishlist(<?php echo $id; ?>, '<?php echo $type; ?>')">
                                <i class="fa fa-heart-o"></i>
                                Yêu thích
                            </button>
                        </div>
                        
                        <div class="product-meta">
                            <?php if ($type === 'product' && isset($product_data['genre_name'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Thể loại:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($product_data['genre_name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="meta-item">
                                <span class="meta-label">Loại sản phẩm:</span>
                                <span class="meta-value"><?php echo $type === 'product' ? 'Album nhạc' : 'Phụ kiện'; ?></span>
                            </div>
                            
                            <?php if (isset($product_data['created_at'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Ngày thêm:</span>
                                <span class="meta-value"><?php echo date('d/m/Y', strtotime($product_data['created_at'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <?php if (!empty($reviews)): ?>
            <div class="reviews-section">
                <h3 class="section-title">
                    <i class="fa fa-star"></i>
                    Đánh giá từ khách hàng
                </h3>
                
                <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="review-user"><?php echo htmlspecialchars($review['full_name']); ?></span>
                        <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    
                    <div class="review-rating">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $review['rating'] ? '<i class="fa fa-star"></i>' : '<i class="fa fa-star-o"></i>';
                        }
                        ?>
                    </div>
                    
                    <div class="review-comment">
                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <div class="related-section">
                <h3 class="section-title">
                    <i class="fa fa-music"></i>
                    Sản phẩm liên quan
                </h3>
                
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                    <div class="col-md-3 col-sm-6">
                        <a href="product-detail.php?type=<?php echo $type; ?>&id=<?php echo $related['product_id']; ?>" class="text-decoration-none">
                            <div class="related-product-card">
                                <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['product_name']); ?>"
                                     class="related-product-image"
                                     onerror="this.src='https://via.placeholder.com/200x200?text=No+Image'">
                                <div class="related-product-info">
                                    <h4 class="related-product-name"><?php echo htmlspecialchars($related['product_name']); ?></h4>
                                    <?php if (isset($related['price'])): ?>
                                    <div class="related-product-price"><?php echo number_format($related['price']); ?>₫</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Thêm khoảng trống cuối trang -->
            <div style="height: 60px;"></div>
        </div>
    </section>
    <!-- Product Detail Section End -->

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include 'includes/chat-widget.php'; ?>

    <!-- Include all js compiled plugins -->
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
        // Quantity Controls
        function increaseQuantity() {
            var quantityInput = document.getElementById('quantity');
            var currentValue = parseInt(quantityInput.value);
            var maxValue = parseInt(quantityInput.max);
            
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
            }
        }
        
        function decreaseQuantity() {
            var quantityInput = document.getElementById('quantity');
            var currentValue = parseInt(quantityInput.value);
            var minValue = parseInt(quantityInput.min);
            
            if (currentValue > minValue) {
                quantityInput.value = currentValue - 1;
            }
        }
        
        // Add to Cart Function
        function addToCart(productId, type) {
            // Kiểm tra đăng nhập
            <?php if (!isLoggedIn()): ?>
            alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
            return;
            <?php endif; ?>
            
            var quantity = 1;
            if (document.getElementById('quantity')) {
                quantity = parseInt(document.getElementById('quantity').value);
            }
            
            // Validate quantity
            if (quantity <= 0) {
                alert('Số lượng phải lớn hơn 0!');
                return;
            }
            
            // Get the add to cart button and show loading state
            var addBtn = document.querySelector('.btn-add-cart');
            var originalText = addBtn.innerHTML;
            addBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang thêm...';
            addBtn.disabled = true;
            
            $.ajax({
                url: 'ajax/cart_handler.php',
                type: 'POST',
                data: {
                    action: 'add_to_cart',
                    item_type: type,
                    item_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update cart badge if it exists
                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge(response.total_items);
                        } else if (document.getElementById('cart-badge')) {
                            document.getElementById('cart-badge').textContent = response.total_items;
                        }
                        
                        // Show success message
                        showSuccessMessage(response.message);
                        
                        // Reset quantity to 1
                        if (document.getElementById('quantity')) {
                            document.getElementById('quantity').value = 1;
                        }
                    } else {
                        showErrorMessage(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showErrorMessage('Có lỗi xảy ra khi thêm vào giỏ hàng. Vui lòng thử lại!');
                },
                complete: function() {
                    // Reset button state
                    addBtn.innerHTML = originalText;
                    addBtn.disabled = false;
                }
            });
        }
        
        // Add to Wishlist Function
        function addToWishlist(productId, type) {
            // Kiểm tra đăng nhập
            <?php if (!isLoggedIn()): ?>
            alert('Vui lòng đăng nhập để sử dụng chức năng yêu thích!');
            return;
            <?php endif; ?>
            
            // Get the wishlist button and show loading state
            var wishlistBtn = document.querySelector('.btn-wishlist');
            var originalText = wishlistBtn.innerHTML;
            wishlistBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
            wishlistBtn.disabled = true;
            
            $.ajax({
                url: 'ajax/wishlist_handler.php',
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    item_type: type,
                    item_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update button state to "added"
                        wishlistBtn.innerHTML = '<i class="fa fa-heart"></i> Đã yêu thích';
                        wishlistBtn.classList.add('active');
                        wishlistBtn.onclick = function() { removeFromWishlist(productId, type); };
                        
                        // Update wishlist badge if it exists
                        if (typeof updateWishlistBadge === 'function') {
                            updateWishlistBadge(response.total_items);
                        } else if (document.getElementById('wishlist-badge')) {
                            document.getElementById('wishlist-badge').textContent = response.total_items;
                        }
                        
                        // Show success message
                        showSuccessMessage(response.message);
                    } else {
                        if (response.already_exists) {
                            // Already in wishlist - change button state
                            wishlistBtn.innerHTML = '<i class="fa fa-heart"></i> Đã yêu thích';
                            wishlistBtn.classList.add('active');
                            wishlistBtn.onclick = function() { removeFromWishlist(productId, type); };
                        }
                        showErrorMessage(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showErrorMessage('Có lỗi xảy ra khi thêm vào danh sách yêu thích. Vui lòng thử lại!');
                },
                complete: function() {
                    // Reset button if not successfully added
                    if (wishlistBtn.innerHTML.includes('fa-spinner')) {
                        wishlistBtn.innerHTML = originalText;
                    }
                    wishlistBtn.disabled = false;
                }
            });
        }
        
        // Remove from Wishlist Function
        function removeFromWishlist(productId, type) {
            var wishlistBtn = document.querySelector('.btn-wishlist');
            var originalText = wishlistBtn.innerHTML;
            wishlistBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
            wishlistBtn.disabled = true;
            
            $.ajax({
                url: 'ajax/wishlist_handler.php',
                type: 'POST',
                data: {
                    action: 'remove_from_wishlist',
                    item_type: type,
                    item_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Reset button to "add" state
                        wishlistBtn.innerHTML = '<i class="fa fa-heart-o"></i> Yêu thích';
                        wishlistBtn.classList.remove('active');
                        wishlistBtn.onclick = function() { addToWishlist(productId, type); };
                        
                        // Update wishlist badge if it exists
                        if (typeof updateWishlistBadge === 'function') {
                            updateWishlistBadge(response.total_items);
                        } else if (document.getElementById('wishlist-badge')) {
                            document.getElementById('wishlist-badge').textContent = response.total_items;
                        }
                        
                        // Show success message
                        showSuccessMessage(response.message);
                    } else {
                        showErrorMessage(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showErrorMessage('Có lỗi xảy ra khi xóa khỏi danh sách yêu thích. Vui lòng thử lại!');
                },
                complete: function() {
                    // Reset button if not successfully removed
                    if (wishlistBtn.innerHTML.includes('fa-spinner')) {
                        wishlistBtn.innerHTML = originalText;
                    }
                    wishlistBtn.disabled = false;
                }
            });
        }
        
        // Check if item is already in wishlist when page loads
        function checkWishlistStatus() {
            <?php if (isLoggedIn()): ?>
            $.ajax({
                url: 'ajax/wishlist_handler.php',
                type: 'POST',
                data: {
                    action: 'check_wishlist_status',
                    item_type: '<?php echo $type; ?>',
                    item_id: <?php echo $id; ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.in_wishlist) {
                        var wishlistBtn = document.querySelector('.btn-wishlist');
                        if (wishlistBtn) {
                            wishlistBtn.innerHTML = '<i class="fa fa-heart"></i> Đã yêu thích';
                            wishlistBtn.classList.add('active');
                            wishlistBtn.onclick = function() { removeFromWishlist(<?php echo $id; ?>, '<?php echo $type; ?>'); };
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Could not check wishlist status:', error);
                }
            });
            <?php endif; ?>
        }
        
        // Success and Error Message Functions
        function showSuccessMessage(message) {
            // Create and show success toast
            var toast = createToast(message, 'success');
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(function() {
                toast.classList.add('show');
            }, 100);
            
            // Remove toast after 3 seconds
            setTimeout(function() {
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        function showErrorMessage(message) {
            // Create and show error toast
            var toast = createToast(message, 'error');
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(function() {
                toast.classList.add('show');
            }, 100);
            
            // Remove toast after 4 seconds
            setTimeout(function() {
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 4000);
        }
        
        function createToast(message, type) {
            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span class="toast-message">${message}</span>
                </div>
            `;
            return toast;
        }
        
        // Image Error Handling và Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(function(img) {
                img.addEventListener('error', function() {
                    this.src = 'https://via.placeholder.com/400x400?text=No+Image';
                });
            });
            
            // Check wishlist status when page loads
            checkWishlistStatus();
        });
    </script>
</body>
</html> 