<?php
// Xác định trang hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Xác định base path cho assets và links
$base_path = '';
if ($current_dir == 'user') {
    $base_path = ''; // Từ thư mục user thì không cần ../
} elseif ($current_dir == 'Artists' || $current_dir == 'genre') {
    $base_path = '../'; // Từ Artists hoặc genre thì cần ../
} else {
    $base_path = ''; // Default
}

// Lấy số lượng items trong giỏ hàng nếu user đã login (simplified)
$cart_count = 0;
if (function_exists('isLoggedIn') && isLoggedIn() && isset($conn)) {
    try {
        $user = getCurrentUser();
        $cart_sql = "SELECT SUM(quantity) as total_items FROM shopping_cart WHERE user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user['user_id']);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
    } catch (Exception $e) {
        $cart_count = 0;
    }
}
?>

<!-- Start Navigation -->
<nav class="navbar navbar-default bootsnav navbar-sticky navbar-scrollspy" data-minus-value-desktop="70" data-minus-value-mobile="55" data-speed="1000">

    <!-- Start Top Search -->
    <div class="top-search">
        <div class="container">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search">
                <span class="input-group-addon close-search"><i class="fa fa-times"></i></span>
            </div>
        </div>
    </div>
    <!-- End Top Search -->

    <div class="container">            
        <!-- Start Atribute Navigation -->
        <div class="attr-nav">
            <ul>
                <li class="search">
                    <a href="#"><span class="lnr lnr-magnifier"></span></a>
                </li><!--/.search-->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="lnr lnr-user"></span>
                    </a>
                    <ul class="dropdown-menu user-menu s-cate">
                        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="user-menu-item">
                            <a href="<?php echo $base_path; ?>profile.php" class="user-menu-link">
                                <i class="fa fa-user"></i>
                                <span>Xem thông tin cá nhân</span>
                            </a>
                        </li>
                        <li class="user-menu-item">
                            <a href="../logout.php" class="user-menu-link logout-link">
                                <i class="fa fa-sign-out"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                        <?php else: ?>
                            <li class="user-menu-item">
                                <a href="../login.php" class="user-menu-link">
                                    <i class="fa fa-sign-in"></i>
                                    <span>Đăng nhập/Đăng ký</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li><!--/.user dropdown-->
                <li class="dropdown cart-dropdown">
                    <a href="<?php echo $base_path; ?>cart.php">
                        <span class="lnr lnr-cart"></span>
                        <span class="badge badge-bg-1"><?php echo $cart_count; ?></span>
                    </a>
                </li><!--/.dropdown-->
            </ul>
        </div><!--/.attr-nav-->
        <!-- End Atribute Navigation -->

        <!-- Start Header Navigation -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">AuraDisc</a>
        </div><!--/.navbar-header-->
        <!-- End Header Navigation -->

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse menu-ui-design" id="navbar-menu">
            <ul class="nav navbar-nav navbar-center" data-in="fadeInDown" data-out="fadeOutUp">
                <li class="scroll <?php echo ($current_page == 'index') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>index.php#home">Home</a>
                </li>
                
                <li class="<?php echo ($current_page == 'new-arrivals') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>new-arrivals.php">New Arrivals</a>
                </li>
                
                <li class="<?php echo ($current_page == 'Artists' || $current_dir == 'Artists') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>Artists/Artists.php">Artists</a>
                </li>
                
                <li class="<?php echo ($current_page == 'genres' || $current_dir == 'genre') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>genre/genres.php">Genre</a>
                </li>
                
                <li class="<?php echo ($current_page == 'accessories') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>accessories.php">Accessories</a>
                </li>
            </ul><!--/.nav -->
        </div><!-- /.navbar-collapse -->
    </div><!--/.container-->
</nav><!--/nav-->
<!-- End Navigation -->

<!-- CSS cho user dropdown menu -->
<style>
.user-menu {
    min-width: 200px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    padding: 10px 0;
}

.user-menu-item {
    border-bottom: 1px solid #f0f0f0;
}

.user-menu-item:last-child {
    border-bottom: none;
}

.user-menu-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
}

.user-menu-link:hover {
    background: #f8f9fa;
    color: #ff6b35;
    text-decoration: none;
}

.user-menu-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.logout-link:hover {
    background: #ffe6e1;
    color: #dc3545;
}

.logout-link:hover i {
    color: #dc3545;
}

#cart-badge {
    background: #ff6b35;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style> 