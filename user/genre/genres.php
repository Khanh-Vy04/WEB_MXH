<?php
// Kết nối database
require_once '../../config/database.php';

// Lấy danh sách genres từ database
$sql = "SELECT g.*, COUNT(p.product_id) as product_count 
        FROM genres g 
        LEFT JOIN products p ON g.genre_id = p.genre_id 
        GROUP BY g.genre_id 
        ORDER BY g.genre_name";

$result = $conn->query($sql);
$genres = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $genres[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thể Loại Nhạc - AuraDisc</title>
    
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
        .genre-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            height: 100%;
        }
        
        .genre-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        
        .genre-header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .genre-header:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .genre-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .genre-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .genre-count {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .genre-info {
            padding: 25px;
        }
        
        .genre-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
            width: 100%;
            text-align: center;
        }
        
        .view-products-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .genre-card.hidden {
            display: none;
        }
        
        /* Genre specific colors */
        .genre-card:nth-child(8n+1) .genre-header { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); }
        .genre-card:nth-child(8n+2) .genre-header { background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%); }
        .genre-card:nth-child(8n+3) .genre-header { background: linear-gradient(135deg, #ffa726 0%, #ff8c42 100%); }
        .genre-card:nth-child(8n+4) .genre-header { background: linear-gradient(135deg, #ffb74d 0%, #ffa726 100%); }
        .genre-card:nth-child(8n+5) .genre-header { background: linear-gradient(135deg, #ffcc80 0%, #ffb74d 100%); }
        .genre-card:nth-child(8n+6) .genre-header { background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%); }
        .genre-card:nth-child(8n+7) .genre-header { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); }
        .genre-card:nth-child(8n+8) .genre-header { background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%); }
    </style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fa fa-tags"></i> Thể Loại Nhạc</h1>
                <p>Khám phá các thể loại nhạc đa dạng và phong phú</p>
            </div>
        </div>

        <div class="container">
            <!-- Thống kê -->
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($genres); ?></span>
                    <div class="stat-label">Thể loại</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($genres, 'product_count')); ?></span>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>

            <!-- Tìm kiếm -->
            <div class="search-container">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm thể loại nhạc...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách thể loại -->
            <?php if (count($genres) > 0): ?>
            <div class="grid-container grid-3" id="genresGrid">
                <?php 
                $genre_icons = [
                    'Rock' => 'fa fa-music',
                    'Pop' => 'fa fa-star',
                    'Jazz' => 'fa fa-music',
                    'Classical' => 'fa fa-music',
                    'Electronic' => 'fa fa-headphones',
                    'Hip Hop' => 'fa fa-microphone',
                    'Country' => 'fa fa-music',
                    'R&B' => 'fa fa-heart'
                ];
                
                foreach ($genres as $genre): 
                    $icon = isset($genre_icons[$genre['genre_name']]) ? $genre_icons[$genre['genre_name']] : 'fa fa-music';
                ?>
                <div class="genre-card fade-in" data-name="<?php echo strtolower($genre['genre_name']); ?>">
                    <div class="genre-header">
                        <div class="genre-icon">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="genre-name"><?php echo htmlspecialchars($genre['genre_name']); ?></h3>
                        <p class="genre-count">
                            <i class="fa fa-music"></i>
                            <?php echo $genre['product_count']; ?> sản phẩm
                        </p>
                    </div>
                    
                    <div class="genre-info">
                        <p class="genre-description">
                            <?php echo htmlspecialchars($genre['description']); ?>
                        </p>
                        
                        <?php if ($genre['product_count'] > 0): ?>
                        <a href="../products.php?genre_id=<?php echo $genre['genre_id']; ?>" class="view-products-btn">
                            <i class="fa fa-eye"></i> Xem sản phẩm
                        </a>
                        <?php else: ?>
                        <div class="text-center text-muted">Chưa có sản phẩm</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fa fa-tags"></i>
                <p>Hiện tại chưa có thể loại nhạc nào.</p>
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
        // Tìm kiếm thể loại
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const genreCards = document.querySelectorAll('.genre-card');
            
            genreCards.forEach(card => {
                const genreName = card.getAttribute('data-name');
                if (genreName.includes(searchTerm)) {
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