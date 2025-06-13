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

    <!-- Footer CSS -->
    <link rel="stylesheet" href="assets/css/footer.css">

    

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

        .accessory-card {
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
        
        .accessory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .accessory-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .accessory-card:hover .accessory-image {
            transform: scale(1.03);
        }

        .accessory-info {
            padding: 20px;
            text-align: center;
        }
        
        .accessory-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            min-height: 30px;
            line-height: 1.2;
        }
        
        .accessory-price {
            font-size: 1.15rem;
            color: #412D3B;
            font-weight: 700;
            margin-bottom: 0px;
        }
        
        .accessory-description {
            /* Removed this block entirely */
        }
        
        .stock-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #deccca;
            color: #412D3B;
        }
        
        .stock-high { background: #deccca; color: #412D3B ; }
        .stock-medium { background: #deccca; color: #412D3B ;}
        .stock-low { background: #deccca; color: #412D3B }
        
        .btn-add-to-cart,
        .btn-view-product {
            background: #412D3B;
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
        
        .btn-add-to-cart:hover,
        .btn-view-product:hover {
            background: #deccca;
            color: #412d3b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(65, 45, 59, 0.4);
            text-decoration: none;
        }
        
        .btn-add-to-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .page-header {
            background: #412D3B;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: white;
        }

        .page-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
        }

        .page-header i {
            margin-right: 10px;
            color: #deccca;
        }

        /* Custom styles for Navbar */
        /* nav.navbar.bootsnav {
            background-color: #412D3B !important;
            border-bottom: none !important;
        }

        nav.navbar.bootsnav ul.nav > li > a {
            color: #FFFFFF !important;
        }

        nav.navbar.bootsnav ul.nav li.active > a {
            color: #deccca !important; /* Màu highlight cho trang Accessories đang active */
        }

        nav.navbar.bootsnav .navbar-brand {
            color: #deccca !important;
        }

        .attr-nav > ul > li > a {
            color: #FFFFFF !important;
        }

        .attr-nav > ul > li > a span.badge {
            background-color: #deccca !important; /* Màu badge giỏ hàng */
            color: #FFFFFF !important;
        }

        .attr-nav > ul > li.dropdown ul.dropdown-menu {
            background-color: #fff !important;
        }

        .user-menu-link, .dropdown-item {
            color: #333 !important;
        }

        .user-menu-link:hover, .dropdown-item:hover {
            background-color: #f8f9fa !important;
            color: #412D3B !important;
        }
    </style>
</head>

<body>
    <!-- Include Navigation -->
    <?php include './includes/navigation.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <!-- <div class="page-header">
            <div class="container">
                <h1><i class="fa fa-headphones"></i> Phụ Kiện Âm Nhạc</h1>
                <p>Khám phá bộ sưu tập phụ kiện âm nhạc chất lượng cao của chúng tôi</p>
            </div>
        </div> -->

        <div class="container">
            <!-- Thống kê -->
            <!-- <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($accessories); ?></span>
                    <div class="stat-label">Phụ kiện</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo array_sum(array_column($accessories, 'stock')); ?></span>
                    <div class="stat-label">Tồn kho</div>
                </div>
            </div> -->

            <!-- Tìm kiếm -->
            <div class="search-container">
                <input type="text" class="search-box" id="searchInput" placeholder="Tìm kiếm phụ kiện...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <!-- Danh sách phụ kiện -->
            <?php if (count($accessories) > 0): ?>
            <div class="grid-container" id="accessoriesGrid">
                <?php foreach ($accessories as $accessory): ?>
                <a href="product-detail.php?type=accessory&id=<?php echo $accessory['accessory_id']; ?>" class="accessory-card fade-in" data-name="<?php echo strtolower($accessory['accessory_name']); ?>">
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
                        
                        <?php if ($stock <= 0): ?>
                            <button class="btn btn-add-to-cart" disabled>
                                <i class="fa fa-times"></i> Hết hàng
                            </button>
                        <?php endif; ?>
                    </div>
                </a>
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

    <!-- Search JS -->
    <script src="assets/js/custom-search.js"></script>

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

<?php
$conn->close();
?>