<?php
// Xác định trang hiện tại để highlight menu
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Xác định base path cho assets và links
$base_path = '';
if ($current_dir == 'user') {
    $base_path = ''; // Từ thư mục user thì không cần ../
} elseif ($current_dir == 'genre') {
    $base_path = '../'; // Từ genre thì cần ../
} else {
    $base_path = ''; // Default
}

// Debug base path
error_log("Current dir: $current_dir, Base path: '$base_path'");
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
                
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="lnr lnr-user"></span>
                    </a>
                    <ul class="dropdown-menu user-menu s-cate">
                        <?php if (isLoggedIn()): ?>
                        <li class="user-menu-item">
                            <a href="<?php echo $base_path; ?>profile.php" class="user-menu-link">
                                <i class="fa fa-user"></i>
                                <span>Xem thông tin cá nhân</span>
                            </a>
                        </li>
                        <li class="user-menu-item">
                            <a href="/WEB_MXH/logout.php" class="user-menu-link logout-link">
                                <i class="fa fa-sign-out"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                        <?php else: ?>
                            <li class="user-menu-item">
                                <a href="/WEB_MXH/login.php" class="user-menu-link">
                                    <i class="fa fa-sign-in"></i>
                                    <span>Đăng nhập/Đăng ký
                                        
                                    </span>
                                </a>
                            </li>
                           
                        <?php endif; ?>
                    </ul>
                </li><!--/.user dropdown-->
            </ul>
        </div><!--/.attr-nav-->
        <!-- End Atribute Navigation -->

        <!-- Start Header Navigation -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php" style="display: flex; align-items: center; gap: 10px;">
                <img src="<?php echo $base_path; ?>assets/logo/favicon.png" class="logo" alt="" style="height: 35px; width: auto;">
                <span>UniWork</span>
            </a>
        </div><!--/.navbar-header-->
        <!-- End Header Navigation -->

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse menu-ui-design" id="navbar-menu">
            <ul class="nav navbar-nav navbar-center" data-in="fadeInDown" data-out="fadeOutUp">
                <li class="<?php echo ($current_page == 'index') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>index.php">Home</a>
                </li>
                
                <li class="<?php echo ($current_page == 'genres' || $current_dir == 'genre') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>genre/genres.php">Dịch vụ</a>
                </li>

                <li class="<?php echo ($current_page == 'about') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>about.php">Giới thiệu</a>
                </li>
            </ul><!--/.nav -->
        </div><!-- /.navbar-collapse -->
    </div><!--/.container-->
</nav><!--/nav-->
<!-- End Navigation -->

<!-- CSS cho navbar -->
<style>
/* Custom styles for Navbar */
nav.navbar.bootsnav {
  background-color: #412d3b !important;
  border-bottom: none !important;
}

nav.navbar.bootsnav ul.nav > li > a {
  color: #ffffff !important;
}

nav.navbar.bootsnav ul.nav li.active > a {
  color: #deccca !important; /* Màu highlight cho trang đang active */
}

nav.navbar.bootsnav .navbar-brand {
  color: #deccca !important;
  font-weight: 700;
  font-size: 24px;
}

.attr-nav > ul > li > a {
  color: #ffffff !important;
}

nav.navbar.bootsnav .navbar-toggle {
  background-color: transparent !important;
  border: 1px solid #deccca !important;
}

nav.navbar.bootsnav .navbar-toggle i {
  color: #deccca !important;
}

/* Ensure dropdown menus have readable text */
.user-menu-link,
.dropdown-item {
  color: #333 !important;
}

.user-menu-link:hover,
.dropdown-item:hover {
  background-color: #deccca!important;
  color: #412d3b !important;
}

/* Giữ lại các style cần thiết cho dropdown menu */
.user-menu {
    min-width: 240px; /* Tăng width để chứa text dài */
    width: max-content; /* Tự động điều chỉnh theo nội dung */
    max-width: 280px; /* Giới hạn width tối đa */
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
    white-space: nowrap; /* Ngăn text xuống dòng */
    overflow: hidden; /* Ẩn text tràn nếu có */
    text-overflow: ellipsis; /* Hiển thị "..." nếu text quá dài */
}

.user-menu-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.logout-link:hover {
    background: #deccca;
    color: #412d3b;
}

.logout-link:hover i {
    color: #412d3b;
}

/* Responsive cho dropdown user menu */
@media (max-width: 768px) {
    .user-menu {
        min-width: 220px; /* Giảm width trên mobile */
        max-width: 250px;
        right: 0; /* Đảm bảo dropdown align phải */
        left: auto;
    }
    
    .user-menu-link {
        padding: 14px 18px; /* Tăng padding cho touch-friendly */
        font-size: 14px;
    }
    
    .user-menu-link span {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .user-menu {
        min-width: 200px;
        max-width: 220px;
    }
    
    .user-menu-link {
        padding: 12px 16px;
        font-size: 13px;
    }
    
    .user-menu-link span {
        font-size: 13px;
    }
}
</style>

<!-- JS Logic for Freelancer Registration -->
<script>
    // Hàm hiển thị modal đăng nhập
    function showLoginModal(message = 'Vui lòng đăng nhập để sử dụng tính năng này') {
        // Remove existing modal if any
        closeLoginModal();
        
        const modalHTML = `
        <div id="loginRequiredModal" class="login-required-modal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        ">
            <div class="login-modal-content" style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                max-width: 400px;
                width: 90%;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            ">
                <h3 style="margin-bottom: 20px; color: #333;">Cần Đăng Nhập</h3>
                <p style="margin-bottom: 25px; color: #666;">${message}</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <a href="/WEB_MXH/login.php" class="btn" style="
                        background: linear-gradient(135deg, #412D3B 0%, #deccca 100%);
                        color: #412D3B;
                        padding: 12px 20px;
                        border-radius: 25px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    ">Đăng Nhập</a>
                    <button onclick="closeLoginModal()" style="
                        background: #6c757d;
                        color: white;
                        padding: 12px 20px;
                        border: none;
                        border-radius: 25px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">Đóng</button>
                </div>
            </div>
        </div>`;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Đóng modal khi click outside
        document.addEventListener('click', function(e) {
            if (e.target.id === 'loginRequiredModal') {
                closeLoginModal();
            }
        });
    }
    
    function closeLoginModal() {
        const modal = document.getElementById('loginRequiredModal');
        if (modal) {
            modal.remove();
        }
    }

    // Freelancer Registration Logic
    const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    const userRole = <?php echo isLoggedIn() ? (getCurrentUser()['role_id'] ?? 'null') : 'null'; ?>;

    function checkFreelancerRegistration() {
        if (!isLoggedIn) {
            showLoginModal('Bạn cần đăng nhập để có thể đăng ký làm freelancer của UniWork');
            return;
        }

        if (userRole == 2) {
            window.location.href = '<?php echo $base_path; ?>register_freelancer.php';
        } else if (userRole == 1 || userRole == 3) {
            alert('Tài khoản của bạn không hợp lệ để đăng ký freelancer (Bạn đã là Admin/Freelancer)');
        } else {
             alert('Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }
</script>
