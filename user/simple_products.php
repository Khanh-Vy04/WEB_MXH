<?php
// Simple products.php without navigation
require_once '../config/database.php';

// Debug mode
$debug = isset($_GET['debug']) ? true : false;

// Lấy artist_id hoặc genre_id từ URL
$artist_id = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;

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

    if ($artist_result->num_rows > 0) {
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

        while($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $error_message = "Không tìm thấy nghệ sĩ với ID: $artist_id";
    }
} elseif ($genre_id > 0) {
    // Lấy thông tin thể loại
    $genre_sql = "SELECT * FROM genres WHERE genre_id = ?";
    $genre_stmt = $conn->prepare($genre_sql);
    $genre_stmt->bind_param("i", $genre_id);
    $genre_stmt->execute();
    $genre_result = $genre_stmt->get_result();

    if ($genre_result->num_rows > 0) {
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

        while($row = $products_result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $error_message = "Không tìm thấy thể loại với ID: $genre_id";
    }
} else {
    $error_message = "Thiếu tham số artist_id hoặc genre_id";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Products Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .debug {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-left: 4px solid #c62828;
            margin: 20px 0;
        }
        .header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: #f9f9f9;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .no-products {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>🎵 Simple Products Test</h1>
    
    <?php if ($debug): ?>
    <div class="debug">
        <h3>🔍 Debug Info:</h3>
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

    <?php if ($error_message && !$debug): ?>
    <div class="error">
        <h3>❌ Lỗi</h3>
        <p><?php echo $error_message; ?></p>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <?php if ($artist || $genre): ?>
    <div class="header">
        <?php if ($artist): ?>
            <h1><?php echo htmlspecialchars($artist['artist_name']); ?></h1>
            <p><?php echo htmlspecialchars($artist['bio']); ?></p>
        <?php elseif ($genre): ?>
            <h1><?php echo htmlspecialchars($genre['genre_name']); ?></h1>
            <p><?php echo htmlspecialchars($genre['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Products -->
    <div class="container">
        <?php if (count($products) > 0): ?>
            <h2>📀 Sản phẩm (<?php echo count($products); ?>)</h2>
            
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    
                    <?php if (!empty($product['genre_name'])): ?>
                    <p><strong>Thể loại:</strong> <?php echo htmlspecialchars($product['genre_name']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['artist_name'])): ?>
                    <p><strong>Nghệ sĩ:</strong> <?php echo htmlspecialchars($product['artist_name']); ?></p>
                    <?php endif; ?>
                    
                    <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                    <p><strong>Tồn kho:</strong> <?php echo $product['stock']; ?></p>
                    
                    <?php if (!empty($product['description'])): ?>
                    <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['image_url'])): ?>
                    <p><strong>Hình ảnh:</strong> <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product" style="max-width: 100px; height: auto;"></p>
                    <?php endif; ?>
                    
                    <p><a href="product-detail.php?type=product&id=<?php echo $product['product_id']; ?>" style="background: #ff6b35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Xem chi tiết</a></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <h2>❌ Không có sản phẩm</h2>
                <?php if ($artist): ?>
                    <p>Nghệ sĩ "<?php echo htmlspecialchars($artist['artist_name']); ?>" chưa có sản phẩm nào.</p>
                <?php elseif ($genre): ?>
                    <p>Thể loại "<?php echo htmlspecialchars($genre['genre_name']); ?>" chưa có sản phẩm nào.</p>
                <?php else: ?>
                    <p>Không tìm thấy sản phẩm nào.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <hr>
    <h3>🔗 Test Links:</h3>
    <p><a href="simple_products.php?genre_id=1&debug=1">Test genre_id=1</a></p>
    <p><a href="simple_products.php?genre_id=2&debug=1">Test genre_id=2</a></p>
    <p><a href="simple_products.php?artist_id=1&debug=1">Test artist_id=1</a></p>
    <p><a href="simple_products.php?artist_id=4&debug=1">Test artist_id=4</a></p>
    <p><a href="../describe_db.php">🔍 Database Analysis</a></p>
    <p><a href="products.php?genre_id=2&debug=1">🐛 Original products.php</a></p>
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include 'includes/chat-widget.php'; ?>

    <!-- Chat Widget CSS -->
    <link rel="stylesheet" href="includes/chat-widget.css">

    <!-- Chat Widget JS -->
    <script src="includes/chat-widget.js"></script>
</body>
</html>