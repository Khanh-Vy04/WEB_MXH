<?php
// Kết nối database
require_once '../../config/database.php';

// Lấy danh sách nghệ sĩ từ database với số lượng sản phẩm
$sql = "SELECT a.*, COUNT(ap.product_id) as product_count 
        FROM artists a 
        LEFT JOIN artist_products ap ON a.artist_id = ap.artist_id 
        WHERE a.status = 1 
        GROUP BY a.artist_id 
        ORDER BY a.artist_name";

$result = $conn->query($sql);
$artists = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $artists[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Nghệ Sĩ - AuraDisc</title>

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../assets/css/font-awesome.min.css" rel="stylesheet">
    <!-- Linear Icons -->
    <link rel="stylesheet" href="../assets/css/linearicons.css">
    <!-- Bootsnav CSS -->
    <link href="../assets/css/bootsnav.css" rel="stylesheet">
    <!-- Main CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- Responsive CSS -->
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Common CSS -->
    <link rel="stylesheet" href="../includes/common.css">
    
<style>
        .artist-card {
            background: white;
            border-radius: 20px;
    overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .artist-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

        .artist-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
}

        .artist-card:hover .artist-image {
            transform: scale(1.05);
}

        .artist-info {
            padding: 25px;
            text-align: center;
        }
        
        .artist-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .product-count {
            color: #666;
            font-size: 1rem;
            margin-bottom: 15px;
}

        .view-products-btn {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
}

        .view-products-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            color: white;
    text-decoration: none;
        }
        
        .artist-card.hidden {
            display: none;
}
</style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fa fa-microphone"></i> Nghệ Sĩ</h1>
                <p>Khám phá các nghệ sĩ tài năng và album của họ</p>
            </div>
        </div>

        <div class="container">
            <!-- Thống kê -->
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($artists); ?></span>
                    <div class="stat-label">Nghệ sĩ</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($artists, 'product_count')); ?></span>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>

            <!-- Tìm kiếm -->
            <div class="search-container">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm nghệ sĩ...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách nghệ sĩ -->
            <?php if (count($artists) > 0): ?>
            <div class="grid-container grid-3" id="artistsGrid">
                <?php foreach ($artists as $artist): ?>
                <div class="artist-card fade-in" data-name="<?php echo strtolower($artist['artist_name']); ?>">
                    <img src="<?php echo htmlspecialchars($artist['image_url']); ?>" 
                         class="artist-image" 
                         alt="<?php echo htmlspecialchars($artist['artist_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/280x250/667eea/ffffff?text=<?php echo urlencode($artist['artist_name']); ?>'">
                    
                    <div class="artist-info">
                        <h3 class="artist-name"><?php echo htmlspecialchars($artist['artist_name']); ?></h3>
                        <p class="product-count">
                            <i class="fa fa-music"></i>
                            <?php echo $artist['product_count']; ?> sản phẩm
                        </p>
                        
                        <?php if ($artist['product_count'] > 0): ?>
                        <a href="../products.php?artist_id=<?php echo $artist['artist_id']; ?>" class="view-products-btn">
                            <i class="fa fa-eye"></i> Xem sản phẩm
                        </a>
                        <?php else: ?>
                        <span class="text-muted">Chưa có sản phẩm</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fa fa-music"></i>
                <p>Hiện tại chưa có nghệ sĩ nào.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../assets/js/jquery.js"></script>
    <!-- Bootstrap JS -->
    <script src="../assets/js/bootstrap.min.js"></script>
    <!-- Bootsnav JS -->
    <script src="../assets/js/bootsnav.js"></script>
    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Tìm kiếm nghệ sĩ
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const artistCards = document.querySelectorAll('.artist-card');
            
            artistCards.forEach(card => {
                const artistName = card.getAttribute('data-name');
                if (artistName.includes(searchTerm)) {
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