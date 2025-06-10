<?php
// Kết nối database và session
require_once '../config/database.php';
require_once 'includes/session.php';

// Lấy danh sách accessories từ database
$sql = "SELECT * FROM accessories WHERE stock > 0 ORDER BY created_at DESC";
$result = $conn->query($sql);
$accessories = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $accessories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phụ Kiện Âm Nhạc - AuraDisc</title>

    <!-- Bootstrap CSS -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
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
        .accessory-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            height: 100%;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease forwards;
        }
        
        .accessory-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        .accessory-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .accessory-card:hover .accessory-image {
            transform: scale(1.05);
        }

        .accessory-info {
            padding: 25px;
            text-align: center;
        }
        
        .accessory-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .accessory-price {
            font-size: 1.3rem;
            color: #ff6b35;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .accessory-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.6;
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-high { background: #28a745; color: white; }
        .stock-medium { background: #ffc107; color: #000; }
        .stock-low { background: #dc3545; color: white; }
        
        .btn-add-to-cart {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        
        .btn-add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-add-to-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-view-product {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        
        .btn-view-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .accessory-card.hidden {
            display: none;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-container {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            padding: 30px;
            border-radius: 15px;
            margin: 0 auto 40px;
            max-width: 800px;
        }

        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
        }

        .stat-label {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include './includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fa fa-headphones"></i> Phụ Kiện Âm Nhạc</h1>
                <p>Khám phá bộ sưu tập phụ kiện âm nhạc chất lượng cao của chúng tôi</p>
            </div>
        </div>

        <div class="container">
            <!-- Thống kê -->
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($accessories); ?></span>
                    <div class="stat-label">Phụ kiện</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($accessories, 'stock')); ?></span>
                    <div class="stat-label">Tồn kho</div>
                </div>
            </div>

            <!-- Tìm kiếm -->
            <div class="search-container">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm phụ kiện...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách phụ kiện -->
            <?php if (count($accessories) > 0): ?>
            <div class="grid-container grid-3" id="accessoriesGrid">
                <?php foreach ($accessories as $accessory): ?>
                <div class="accessory-card fade-in" data-name="<?php echo strtolower($accessory['accessory_name']); ?>">
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($accessory['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($accessory['accessory_name']); ?>"
                             class="accessory-image"
                             onerror="this.src='https://via.placeholder.com/280x250/667eea/ffffff?text=<?php echo urlencode($accessory['accessory_name']); ?>'">
                             
                        <?php
                        $stock = $accessory['stock'];
                        $badge_class = 'stock-high';
                        if ($stock < 5) $badge_class = 'stock-low';
                        elseif ($stock < 15) $badge_class = 'stock-medium';
                        ?>
                        <span class="stock-badge <?php echo $badge_class; ?>">
                            <?php echo $stock; ?> trong kho
                        </span>
                    </div>
                    
                    <div class="accessory-info">
                        <h3 class="accessory-title">
                            <?php echo htmlspecialchars($accessory['accessory_name']); ?>
                        </h3>
                        
                        <div class="accessory-price">
                            $<?php echo number_format($accessory['price'], 2); ?>
                        </div>
                        
                        <p class="accessory-description">
                            <?php echo htmlspecialchars($accessory['description']); ?>
                        </p>
                        
                        <?php if ($stock <= 0): ?>
                            <button class="btn btn-add-to-cart" disabled>
                                <i class="fa fa-times"></i> Hết Hàng
                            </button>
                        <?php else: ?>
                            <a href="product-detail.php?type=accessory&id=<?php echo $accessory['accessory_id']; ?>" class="btn btn-view-product">
                                <i class="fa fa-eye"></i> Xem sản phẩm
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fa fa-headphones"></i>
                <p>Hiện tại chưa có phụ kiện nào.</p>
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
        // Tìm kiếm phụ kiện
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const accessoryCards = document.querySelectorAll('.accessory-card');
            
            accessoryCards.forEach(card => {
                const accessoryName = card.getAttribute('data-name');
                if (accessoryName.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });

        // Hiệu ứng xuất hiện từ từ
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('visible');
                }, index * 100);
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 