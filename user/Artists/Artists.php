<?php
// Kết nối database và session
require_once '../../config/database.php';
require_once '../../includes/session.php';

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
        .main-content {
            padding-top: 40px;
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

        .artist-card {
            background: #f7f8f9; /* Synchronized with accessories.php */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); /* Synchronized with accessories.php */
            transition: all 0.3s ease;
            /* cursor: pointer; removed as card will be a link */
            position: relative;
            height: 100%;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease forwards;
        }
        
        .artist-card:hover {
            transform: translateY(-5px); /* Synchronized with accessories.php */
            box-shadow: 0 15px 40px rgba(0,0,0,0.15); /* Synchronized with accessories.php */
        }

        .artist-image {
            width: 100%;
            height: 220px; /* Synchronized with accessories.php */
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .artist-card:hover .artist-image {
            transform: scale(1.03); /* Synchronized with accessories.php */
        }

        .artist-info {
            padding: 25px;
            text-align: center; /* Synchronized with accessories.php */
        }
        
        .artist-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px; /* Synchronized with accessories.php */
            min-height: 30px; /* Synchronized with accessories.php */
            line-height: 1.2; /* Synchronized with accessories.php */
        }
        
        .product-count {
            color: #412D3B; /* Synchronized with accessories.php (stock badge/price) */
            font-size: 1rem;
            margin-bottom: 0px; /* Synchronized with accessories.php */
        }

        .view-products-btn {
            background: #412D3B; /* Synchronized with accessories.php */
            color: white;
            border: none;
            padding: 8px 20px; /* Synchronized with accessories.php */
            border-radius: 20px; /* Synchronized with accessories.php */
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%; /* Synchronized with accessories.php */
            text-align: center; /* Synchronized with accessories.php */
        }

        .view-products-btn:hover {
            transform: translateY(-2px); /* Synchronized with accessories.php */
            box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4); /* Synchronized with accessories.php */
            color: #412d3b; /* Synchronized with accessories.php */
            background: #deccca; /* Synchronized with accessories.php */
            text-decoration: none;
        }
        
        .artist-card.hidden {
            display: none;
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
    <!-- Include Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <?php /* Removed original Page Header */ ?>

        <div class="container">
            <!-- Thống kê -->
            <?php /* Removed original Stats Container */ ?>

            <!-- Tìm kiếm -->
            <div class="search-container">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm nghệ sĩ...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách nghệ sĩ -->
            <?php if (count($artists) > 0): ?>
            <div class="grid-container" id="artistsGrid">
                <?php foreach ($artists as $artist): ?>
                <a href="../products.php?artist_id=<?php echo $artist['artist_id']; ?>" class="artist-card fade-in" data-name="<?php echo strtolower($artist['artist_name']); ?>">
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
                        
                        <?php /* Removed conditional button/text for product_count */ ?>
                    </div>
                </a>
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

    <!-- Search JS -->
    <script src="../assets/js/custom-search.js"></script>

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

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include '../includes/chat-widget.php'; ?>

    <!-- Chat Widget CSS -->
    <link rel="stylesheet" href="../includes/chat-widget.css">

    <!-- Chat Widget JS -->
    <script src="../includes/chat-widget.js"></script>
</body>
</html>