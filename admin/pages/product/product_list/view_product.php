<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'product';

// Kết nối database
$config_path = '../../../../config/database.php';
if (!file_exists($config_path)) {
    $config_path = '../../../config/database.php';
    if (!file_exists($config_path)) {
        die("Cannot find database config file");
    }
}

require_once $config_path;

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: product_list.php');
    exit;
}

// Lấy thông tin chi tiết sản phẩm
$sql = "SELECT p.*, a.artist_name, a.bio as artist_bio, a.image_url as artist_image, g.genre_name
        FROM products p 
        LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
        LEFT JOIN artists a ON ap.artist_id = a.artist_id 
        LEFT JOIN genres g ON p.genre_id = g.genre_id
        WHERE p.product_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: product_list.php');
    exit;
}

$product = $result->fetch_assoc();

// Format giá tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format ngày tháng
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Lấy status text
function getStatusText($stock) {
    if ($stock > 50) {
        return '<span class="badge bg-success">Còn nhiều hàng</span>';
    } elseif ($stock > 20) {
        return '<span class="badge bg-warning">Còn ít hàng</span>';
    } elseif ($stock > 0) {
        return '<span class="badge bg-danger">Sắp hết hàng</span>';
    } else {
        return '<span class="badge bg-secondary">Hết hàng</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Chi Tiết Sản Phẩm - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .product-detail-container {
            background: #ebecef;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            max-width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 3px solid #ddd;
        }
        
        .product-info {
            color: #333;
        }
        
        .product-title {
            color: #412d3b;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #412d3b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .info-label {
            color: #666;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .info-value {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .price-display {
            background: linear-gradient(135deg, #412d3b, #2d1e26);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(65, 45, 59, 0.3);
        }
        
        .artist-section {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        
        .artist-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #412d3b;
        }
        
        .artist-name {
            color: #412d3b;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .artist-bio {
            color: #666;
            line-height: 1.6;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .btn-custom {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .btn-primary-custom {
            background: #412d3b;
            border-color: #412d3b;
            color: white;
        }
        
        .btn-primary-custom:hover {
            background: #2d1e26;
            border-color: #2d1e26;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-warning-custom {
            background: #deccca;
            border-color: #deccca;
            color: #412d3b;
        }
        
        .btn-warning-custom:hover {
            background: #c9b5b0;
            border-color: #c9b5b0;
            color: #412d3b;
            transform: translateY(-2px);
        }
        
        .btn-danger-custom {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-danger-custom:hover {
            background: #c82333;
            border-color: #bd2130;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-secondary-custom {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        .btn-secondary-custom:hover {
            background: #5a6268;
            border-color: #545b62;
            color: white;
            transform: translateY(-2px);
        }
        
        .description-box {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        
        .description-text {
            color: #333;
            line-height: 1.8;
            font-size: 1rem;
        }
        
        .stock-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .stock-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }
        
        .stock-high { background-color: #27AE60; }
        .stock-medium { background-color: #F39C12; }
        .stock-low { background-color: #E74C3C; }
        .stock-out { background-color: #95A5A6; }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-detail-container {
                padding: 20px;
                margin: 0 10px;
            }
            
            .product-title {
                font-size: 2rem;
            }
            
            .product-image {
                height: 300px;
            }
            
            .btn-custom {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
        
        @media (min-width: 1200px) {
            .product-detail-container {
                max-width: 95%;
                margin: 0 auto 20px auto;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <?php 
        if (file_exists(__DIR__.'/../../dashboard/sidebar.php')) {
            include __DIR__.'/../../dashboard/sidebar.php'; 
        }
        ?>
        
        <div class="content">
            <?php 
            if (file_exists(__DIR__.'/../../dashboard/navbar.php')) {
                include __DIR__.'/../../dashboard/navbar.php'; 
            }
            ?>
            
            <div class="container-fluid pt-4 px-2">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="product-detail-container">
                            <!-- Header với nút quay lại -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0" style="color: #333; font-weight: bold;">
                                    <i class="fas fa-eye me-2"></i>Chi tiết sản phẩm
                                </h4>
                                <a href="product_list.php" class="btn btn-secondary-custom btn-custom">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                                </a>
                            </div>
                            
                            <div class="row">
                                <!-- Hình ảnh sản phẩm -->
                                <div class="col-md-5">
                                    <div class="text-center">
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                             class="product-image"
                                             onerror="this.src='/WEB_MXH/admin/img/no-image.png'">
                                    </div>
                                </div>
                                
                                <!-- Thông tin sản phẩm -->
                                <div class="col-md-7">
                                    <div class="product-info">
                                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                                        
                                        <div class="price-display">
                                            <i class="fas fa-tag me-2"></i><?php echo formatPrice($product['price']); ?>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-barcode me-2"></i>Mã sản phẩm
                                                    </div>
                                                    <div class="info-value">#<?php echo $product['product_id']; ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-layer-group me-2"></i>Danh mục
                                                    </div>
                                                    <div class="info-value"><?php echo $product['genre_name'] ? htmlspecialchars($product['genre_name']) : 'Chưa phân loại'; ?></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-boxes me-2"></i>Tồn kho
                                                    </div>
                                                    <div class="info-value">
                                                        <div class="stock-indicator">
                                                            <?php 
                                                            $stock = $product['stock'];
                                                            if ($stock > 50) {
                                                                echo '<span class="stock-dot stock-high"></span>';
                                                            } elseif ($stock > 20) {
                                                                echo '<span class="stock-dot stock-medium"></span>';
                                                            } elseif ($stock > 0) {
                                                                echo '<span class="stock-dot stock-low"></span>';
                                                            } else {
                                                                echo '<span class="stock-dot stock-out"></span>';
                                                            }
                                                            echo $stock . ' sản phẩm';
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-info-circle me-2"></i>Trạng thái
                                                    </div>
                                                    <div class="info-value"><?php echo getStatusText($product['stock']); ?></div>
                                                </div>
                                            </div>
                                            
                                            <?php if (isset($product['created_at']) || isset($product['created_a'])): ?>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-calendar-plus me-2"></i>Ngày tạo
                                                    </div>
                                                    <div class="info-value">
                                                        <?php 
                                                        $created_date = isset($product['created_at']) ? $product['created_at'] : $product['created_a'];
                                                        echo formatDate($created_date); 
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($product['updated_at'])): ?>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <div class="info-label">
                                                        <i class="fas fa-calendar-edit me-2"></i>Cập nhật cuối
                                                    </div>
                                                    <div class="info-value"><?php echo formatDate($product['updated_at']); ?></div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mô tả sản phẩm -->
                            <?php if (!empty($product['description'])): ?>
                            <div class="description-box">
                                <div class="info-label mb-3">
                                    <i class="fas fa-align-left me-2"></i>Mô tả sản phẩm
                                </div>
                                <div class="description-text">
                                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Thông tin nghệ sĩ -->
                            <?php if (!empty($product['artist_name'])): ?>
                            <div class="artist-section">
                                <div class="info-label mb-3">
                                    <i class="fas fa-user-music me-2"></i>Thông tin nghệ sĩ
                                </div>
                                <div class="d-flex align-items-start">
                                    <?php if (!empty($product['artist_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['artist_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['artist_name']); ?>"
                                         class="artist-image me-3"
                                         onerror="this.src='/WEB_MXH/admin/img/default-avatar.png'">
                                    <?php endif; ?>
                                    <div>
                                        <div class="artist-name"><?php echo htmlspecialchars($product['artist_name']); ?></div>
                                        <?php if (!empty($product['artist_bio'])): ?>
                                        <div class="artist-bio"><?php echo nl2br(htmlspecialchars($product['artist_bio'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Nút hành động -->
                            <div class="action-buttons">
                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-warning-custom btn-custom">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                </a>
                                <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" 
                                   class="btn btn-danger-custom btn-custom"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash-alt me-2"></i>Xóa
                                </a>
                                <a href="product_list.php" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-list me-2"></i>Danh sách sản phẩm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
            if (file_exists(__DIR__.'/../../dashboard/footer.php')) {
                include __DIR__.'/../../dashboard/footer.php'; 
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>
</html> 