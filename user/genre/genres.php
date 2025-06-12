<?php
// Kết nối database và session
require_once '../../config/database.php';
require_once '../../includes/session.php';

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

        .genre-card {
            background: #f7f8f9; /* Changed from white */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            /* cursor: pointer; removed, as the whole card will be a link */
            position: relative;
            height: 100%;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease forwards;
        }
        
        .genre-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .genre-header {
            background: linear-gradient(135deg, #412D3B 0%,rgb(255, 236, 234) 100%); /* Added gradient */
            color: white; /* Changed to white */
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
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
            color: white; /* Added color for product count */
            position: relative;
            z-index: 2;
        }
        
        .genre-info {
            padding: 25px;
            text-align: center; /* Centered text like accessory-info */
        }
        
        .genre-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: justify; /* Căn đều 2 bên */
        }
        
        .view-products-btn {
            background: #412D3B; /* Updated background */
            color: white; /* Updated color */
            border: none;
            padding: 8px 20px; /* Updated padding */
            border-radius: 20px; /* Updated border-radius */
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        
        .view-products-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4); /* Updated hover shadow */
            color: #412d3b; /* Updated hover text color */
            background: #deccca; /* Updated hover background */
            text-decoration: none;
        }
        
        .genre-card.hidden {
            display: none;
        }
        
        /* Removed genre specific colors */
        /* .genre-card:nth-child(8n+1) .genre-header { background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); } */
        /* ... other nth-child rules ... */

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
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm thể loại nhạc...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách thể loại -->
            <?php if (count($genres) > 0): ?>
            <div class="grid-container" id="genresGrid">
                <?php 
                $genre_icons = [
                    'Rock' => 'fa fa-music',
                    'Pop' => 'fa fa-music',
                    'Jazz' => 'fa fa-music',
                    'Classical' => 'fa fa-music',
                    'Electronic' => 'fa fa-music',
                    'Hip Hop' => 'fa fa-music',
                    'Country' => 'fa fa-music',
                    'R&B' => 'fa fa-music'
                ];
                
                foreach ($genres as $genre): 
                    $icon = isset($genre_icons[$genre['genre_name']]) ? $genre_icons[$genre['genre_name']] : 'fa fa-music';
                ?>
                <a href="../products.php?genre_id=<?php echo $genre['genre_id']; ?>" class="genre-card fade-in" data-name="<?php echo strtolower($genre['genre_name']); ?>">
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
                    </div>
                </a>
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

    <!-- Search JS -->
    <script src="../assets/js/custom-search.js"></script>

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