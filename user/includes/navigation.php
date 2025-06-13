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

// Debug base path
error_log("Current dir: $current_dir, Base path: '$base_path'");

// Lấy số lượng items trong giỏ hàng nếu user đã login
$cart_count = 0;
if (isLoggedIn()) {
    $user = getCurrentUser();
    $cart_sql = "SELECT SUM(quantity) as total_items FROM shopping_cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user['user_id']);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    $cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
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
                <li class="dropdown cart-dropdown">
                    <a href="<?php echo $base_path; ?>cart.php" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="lnr lnr-cart"></span>
                        <span class="badge badge-bg-1" id="cart-badge"><?php echo $cart_count; ?></span>
                    </a>
                    <ul class="dropdown-menu cart-list s-cate" id="cart-dropdown-menu">
                        <li class="cart-header">
                            <h6>Giỏ hàng của bạn</h6>
                        </li>
                        <li id="cart-loading" class="cart-loading" style="display: none;">
                            <div class="loading-spinner">
                                <i class="fa fa-spinner fa-spin"></i>
                                <span>Đang tải...</span>
                            </div>
                        </li>
                        <li id="cart-empty" class="cart-empty" style="display: none;">
                            <div class="empty-cart">
                                <i class="lnr lnr-cart"></i>
                                <p>Giỏ hàng trống</p>
                            </div>
                        </li>
                        <div id="cart-items">
                            <!-- Cart items will be loaded here -->
                        </div>
                        <li class="cart-total" id="cart-total" style="display: none;">
                            <div class="total-info">
                                <span class="total-text">Tổng cộng: <span id="total-amount">$0.00</span></span>
                                <button class="btn-cart" onclick="window.location.href='<?php echo $base_path; ?>cart.php'">Xem giỏ hàng</button>
                            </div>
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
                <li class="<?php echo ($current_page == 'index') ? 'active' : ''; ?>">
                    <a href="<?php echo $base_path; ?>index.php">Home</a>
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
}

.attr-nav > ul > li > a {
  color: #ffffff !important;
}

.attr-nav > ul > li > a span.badge {
  background-color: #deccca !important; /* Màu badge giỏ hàng */
  color: #412d3b !important;
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

/* Giữ lại các style cần thiết cho dropdown menu và cart */
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

#cart-badge {
    background: #deccca;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Cart Dropdown Styles */
.cart-dropdown .dropdown-menu {
    min-width: 380px !important; /* Tăng width để chứa sản phẩm */
    max-width: 420px !important;
    max-height: 500px !important; /* Giới hạn height */
    overflow-y: auto !important; /* Scroll nếu quá nhiều sản phẩm */
    padding: 0 !important;
    border: none !important;
    border-radius: 15px !important;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
    background: white !important;
    right: 0 !important;
    left: auto !important;
}

.cart-header {
    background: linear-gradient(135deg, #412d3b, #deccca) !important;
    color: white !important;
    padding: 15px 20px !important;
    border-radius: 15px 15px 0 0 !important;
    margin: 0 !important;
    border-bottom: none !important;
}

.cart-header h6 {
    margin: 0 !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    text-align: center !important;
    color: white !important;
}

.cart-loading, .cart-empty {
    padding: 30px 20px !important;
    text-align: center !important;
    color: #666 !important;
    border-bottom: none !important;
}

.cart-loading .loading-spinner, .cart-empty .empty-cart {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 10px !important;
}

.cart-loading i {
    font-size: 24px !important;
    color: #412d3b !important;
}

.cart-empty i {
    font-size: 36px !important;
    color: #ddd !important;
    margin-bottom: 10px !important;
}

#cart-items {
    max-height: 300px !important;
    overflow-y: auto !important;
    padding: 0 !important;
}

.cart-item {
    display: flex !important;
    align-items: center !important;
    padding: 15px 20px !important;
    border-bottom: 1px solid #f0f0f0 !important;
    transition: background 0.3s ease !important;
    gap: 12px !important;
}

.cart-item:hover {
    background: #f8f9fa !important;
}

.cart-item:last-child {
    border-bottom: none !important;
}

.cart-item-image {
    width: 50px !important;
    height: 50px !important;
    object-fit: cover !important;
    border-radius: 8px !important;
    flex-shrink: 0 !important;
}

.cart-item-details {
    flex: 1 !important;
    min-width: 0 !important; /* Cho phép text truncate */
    display: flex !important;
    flex-direction: column !important;
    gap: 3px !important;
}

.cart-item-name {
    font-weight: 600 !important;
    color: #333 !important;
    font-size: 14px !important;
    line-height: 1.3 !important;
    margin: 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.cart-item-type {
    font-size: 11px !important;
    color: #666 !important;
    text-transform: capitalize !important;
    margin: 0 !important;
}

.cart-item-quantity {
    background: #f0f0f0 !important;
    color: #412d3b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    padding: 4px 8px !important;
    border-radius: 12px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    text-align: center !important;
    min-width: 50px !important;
}

.cart-total {
    background: #f8f9fa !important;
    padding: 20px !important;
    border-top: 2px solid #dee2e6 !important;
    border-radius: 0 0 15px 15px !important;
    margin: 0 !important;
}

.total-info {
    text-align: center !important;
}

.total-text {
    display: block !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    color: #333 !important;
    margin-bottom: 15px !important;
}

#total-amount {
    color: #412d3b !important;
    font-size: 18px !important;
    font-weight: 700 !important;
}

.btn-cart {
    background: #deccca !important;
    color: #412d3b !important;
    border: none !important;
    padding: 12px 25px !important;
    border-radius: 25px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    width: 100% !important;
    font-size: 14px !important;
}

.btn-cart:hover {
    background: #412d3b !important;
    color: #deccca !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 20px rgba(65, 45, 59, 0.3) !important;
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
    
    /* Cart dropdown responsive */
    .cart-dropdown .dropdown-menu {
        min-width: 320px !important;
        max-width: 350px !important;
        right: -10px !important;
    }
    
    .cart-item {
        padding: 12px 15px !important;
        gap: 10px !important;
    }
    
    .cart-item-image {
        width: 45px !important;
        height: 45px !important;
    }
    
    .cart-item-name {
        font-size: 13px !important;
    }
    
    .cart-item-type {
        font-size: 10px !important;
    }
    
    .cart-item-quantity {
        font-size: 10px !important;
        padding: 3px 6px !important;
        min-width: 45px !important;
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
    
    /* Cart dropdown mobile */
    .cart-dropdown .dropdown-menu {
        min-width: 280px !important;
        max-width: 300px !important;
        right: -20px !important;
    }
    
    .cart-item {
        padding: 10px 12px !important;
        gap: 8px !important;
    }
    
    .cart-item-image {
        width: 40px !important;
        height: 40px !important;
    }
    
    .cart-item-name {
        font-size: 12px !important;
    }
    
    .cart-item-type {
        font-size: 9px !important;
    }
    
    .cart-item-quantity {
        font-size: 9px !important;
        padding: 2px 5px !important;
        min-width: 40px !important;
    }
    
    .cart-header h6 {
        font-size: 14px !important;
    }
    
    .total-text {
        font-size: 14px !important;
    }
    
    #total-amount {
        font-size: 16px !important;
    }
}
</style>

<!-- Cart JavaScript -->
<script>
    // Check jQuery availability
    function initCartDropdown() {
        console.log('🎯 Navigation: Initializing cart dropdown');
        console.log('🔍 jQuery available:', typeof $ !== 'undefined');
        
        if (typeof $ === 'undefined') {
            console.error('❌ jQuery not available! Using vanilla JS');
            initCartDropdownVanilla();
            return;
        }
        
        console.log('🔍 Found cart dropdown elements:', $('.cart-dropdown').length);
        console.log('🔍 Found dropdown menu:', $('#cart-dropdown-menu').length);
        
        // Simple hover with forced display
        $('.cart-dropdown').hover(
            function() {
                console.log('🖱️ HOVER ENTER detected!');
                
                // Force show dropdown with multiple methods
                $(this).addClass('show');
                $('#cart-dropdown-menu').addClass('show').css('display', 'block');
                
                // Load cart items
                loadCartItems();
            }, 
            function() {
                console.log('🖱️ HOVER LEAVE detected!');
                
                // Hide with delay
                setTimeout(() => {
                    if (!$('.cart-dropdown:hover').length && !$('#cart-dropdown-menu:hover').length) {
                        $('.cart-dropdown').removeClass('show');
                        $('#cart-dropdown-menu').removeClass('show').css('display', 'none');
                    }
                }, 300);
            }
        );
        
        // Handle cart link click
        $('.cart-dropdown .dropdown-toggle').on('click', function(e) {
            console.log('🖱️ Cart icon clicked');
            window.location.href = $(this).attr('href');
        });
    }
    
    // Vanilla JavaScript fallback
    function initCartDropdownVanilla() {
        console.log('🎯 Using vanilla JavaScript for cart dropdown');
        
        const cartDropdown = document.querySelector('.cart-dropdown');
        const dropdownMenu = document.querySelector('#cart-dropdown-menu');
        
        if (!cartDropdown || !dropdownMenu) {
            console.error('❌ Cart elements not found');
            return;
        }
        
        cartDropdown.addEventListener('mouseenter', function() {
            console.log('🖱️ Vanilla: HOVER ENTER');
            this.classList.add('show');
            dropdownMenu.classList.add('show');
            dropdownMenu.style.display = 'block';
            loadCartItems();
        });
        
        cartDropdown.addEventListener('mouseleave', function() {
            console.log('🖱️ Vanilla: HOVER LEAVE');
            setTimeout(() => {
                if (!cartDropdown.matches(':hover') && !dropdownMenu.matches(':hover')) {
                    cartDropdown.classList.remove('show');
                    dropdownMenu.classList.remove('show');
                    dropdownMenu.style.display = 'none';
                }
            }, 300);
        });
    }
    
    // Initialize when ready
    if (typeof $ !== 'undefined') {
        $(document).ready(initCartDropdown);
    } else {
        document.addEventListener('DOMContentLoaded', initCartDropdown);
    }

    // Hàm hiển thị modal đăng nhập
    function showLoginModal(message = 'Vui lòng đăng nhập để sử dụng tính năng này') {
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
    
    // Hàm kiểm tra response có yêu cầu đăng nhập không
    function handleLoginRequired(response) {
        if (response && response.require_login) {
            showLoginModal(response.message);
            return true;
        }
        return false;
    }

    function loadCartItems() {
        console.log('🔄 Navigation: Loading cart items...');
        $('#cart-loading').show();
        $('#cart-empty').hide();
        $('#cart-items').empty();
        $('#cart-total').hide();

        $.ajax({
            url: '<?php echo $base_path; ?>ajax/cart_handler.php',
            type: 'POST',
            data: { action: 'get_cart' },
            dataType: 'json',
            beforeSend: function() {
                console.log('📤 Navigation: Sending cart request...');
            },
            success: function(response) {
                console.log('✅ Navigation: Cart response received:', response);
                $('#cart-loading').hide();
                
                if (response.success) {
                    if (response.cart_items && response.cart_items.length === 0) {
                        console.log('📭 Navigation: Cart is empty');
                        // Hiển thị thông báo phù hợp dựa vào trạng thái đăng nhập
                        if (response.message === 'Chưa đăng nhập') {
                            $('#cart-empty .empty-cart p').text('Vui lòng đăng nhập để xem giỏ hàng');
                        } else {
                            $('#cart-empty .empty-cart p').text('Giỏ hàng trống');
                        }
                        $('#cart-empty').show();
                    } else {
                        console.log('📦 Navigation: Displaying', response.cart_items.length, 'items');
                        displayCartItems(response.cart_items, response.total_amount);
                    }
                    updateCartBadge(response.total_items);
                } else {
                    console.error('❌ Navigation: Cart error:', response.message);
                    $('#cart-empty').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('🚨 Navigation: AJAX error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                $('#cart-loading').hide();
                $('#cart-empty').show();
            }
        });
    }

    function displayCartItems(items, totalAmount) {
        console.log('🎨 Navigation: Displaying cart items:', items);
        let cartHTML = '';
        
        items.forEach(function(item) {
            const imageUrl = item.image_url || '<?php echo $base_path; ?>assets/images/default-product.jpg';
            cartHTML += `
                <li class="cart-item">
                    <img src="${imageUrl}" 
                         alt="${item.item_name}" class="cart-item-image"
                         onerror="this.src='<?php echo $base_path; ?>assets/images/default-product.jpg'">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.item_name || item.name}</div>
                        <div class="cart-item-type">${item.item_type === 'product' ? 'Album nhạc' : 'Phụ kiện'}</div>
                    </div>
                    <div class="cart-item-quantity">SL: ${item.quantity}</div>
                </li>
            `;
        });

        $('#cart-items').html(cartHTML);
        
        // Format giá tiền VND
        const vndAmount = parseFloat(totalAmount);
        $('#total-amount').text(vndAmount.toLocaleString('vi-VN') + '₫');
        $('#cart-total').show();
        console.log('✨ Navigation: Cart display completed');
    }

    function updateCartBadge(count) {
        $('#cart-badge').text(count || 0);
    }

    function removeCartItem(cartId) {
        $.ajax({
            url: '<?php echo $base_path; ?>ajax/cart_handler.php',
            type: 'POST',
            data: { 
                action: 'remove_item',
                cart_id: cartId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    loadCartItems(); // Reload cart
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        });
    }

    // Global function to add to cart (can be called from product pages)
    function addToCart(itemType, itemId, quantity = 1) {
        $.ajax({
            url: '<?php echo $base_path; ?>ajax/cart_handler.php',
            type: 'POST',
            data: {
                action: 'add_to_cart',
                item_type: itemType,
                item_id: itemId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCartBadge(response.total_items);
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            }
        });
    }
</script> 
