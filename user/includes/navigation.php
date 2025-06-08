<?php
// Xác định trang hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Xác định base path cho assets và links
$base_path = '';
if ($current_dir == 'user') {
    $base_path = '';
} elseif ($current_dir == 'Artists' || $current_dir == 'genre') {
    $base_path = '../';
} else {
    $base_path = '';
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
                        <li class="user-menu-item">
                            <a href="#" class="user-menu-link">
                                <i class="fa fa-ticket"></i>
                                <span>Xem voucher</span>
                            </a>
                        </li>
                        <li class="user-menu-item">
                            <a href="#" class="user-menu-link">
                                <i class="fa fa-file-text"></i>
                                <span>Xem hóa đơn đã mua</span>
                            </a>
                        </li>
                        <li class="user-menu-item">
                            <a href="/WEB_MXH/logout.php" class="user-menu-link logout-link">
                                <i class="fa fa-sign-out"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </li><!--/.user dropdown-->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="lnr lnr-cart"></span>
                        <span class="badge badge-bg-1">2</span>
                    </a>
                    <ul class="dropdown-menu cart-list s-cate">
                        <li class="single-cart-list">
                            <a href="#" class="photo"><img src="<?php echo $base_path; ?>assets/images/collection/arrivals1.png" class="cart-thumb" alt="image" /></a>
                            <div class="cart-list-txt">
                                <h6><a href="#">arm <br> chair</a></h6>
                                <p>1 x - <span class="price">$180.00</span></p>
                            </div><!--/.cart-list-txt-->
                            <div class="cart-close">
                                <span class="lnr lnr-cross"></span>
                            </div><!--/.cart-close-->
                        </li><!--/.single-cart-list -->
                        <li class="single-cart-list">
                            <a href="#" class="photo"><img src="<?php echo $base_path; ?>assets/images/collection/arrivals2.png" class="cart-thumb" alt="image" /></a>
                            <div class="cart-list-txt">
                                <h6><a href="#">single <br> armchair</a></h6>
                                <p>1 x - <span class="price">$180.00</span></p>
                            </div><!--/.cart-list-txt-->
                            <div class="cart-close">
                                <span class="lnr lnr-cross"></span>
                            </div><!--/.cart-close-->
                        </li><!--/.single-cart-list -->
                        <li class="single-cart-list">
                            <a href="#" class="photo"><img src="<?php echo $base_path; ?>assets/images/collection/arrivals3.png" class="cart-thumb" alt="image" /></a>
                            <div class="cart-list-txt">
                                <h6><a href="#">wooden arn <br> chair</a></h6>
                                <p>1 x - <span class="price">$180.00</span></p>
                            </div><!--/.cart-list-txt-->
                            <div class="cart-close">
                                <span class="lnr lnr-cross"></span>
                            </div><!--/.cart-close-->
                        </li><!--/.single-cart-list -->
                        <li class="total">
                            <span>Total: $0.00</span>
                            <button class="btn-cart pull-right" onclick="window.location.href='#'">view cart</button>
                        </li>
                    </ul>
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
</style> 