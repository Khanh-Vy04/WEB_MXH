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
                <li class="search">
                    <a href="#"><span class="lnr lnr-magnifier"></span></a>
                </li><!--/.search-->
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
                                <a href="/WEB_MXH/index.php" class="user-menu-link">
                                    <i class="fa fa-sign-in"></i>
                                    <span>Đăng nhập</span>
                                </a>
                            </li>
                            <li class="user-menu-item">
                                <a href="/WEB_MXH/index.php#register" class="user-menu-link">
                                    <i class="fa fa-user-plus"></i>
                                    <span>Đăng ký</span>
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

        /* Cart Dropdown Styles - Force Override */
        .cart-dropdown {
            position: relative !important;
        }
        
        .cart-dropdown .dropdown-menu {
            min-width: 350px !important;
            max-height: 400px !important;
            overflow-y: auto !important;
            display: none !important;
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            z-index: 9999 !important;
            background: white !important;
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
            margin-top: 5px !important;
        }
        
        /* Force show on hover */
        .cart-dropdown:hover .dropdown-menu {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Also force show with class */
        .cart-dropdown.show .dropdown-menu,
        .cart-dropdown .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .cart-header {
            background: #ff6b35;
            color: white;
            padding: 15px 20px;
            margin: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
        }

        .cart-loading, .cart-empty {
            text-align: center;
            padding: 30px 20px;
            color: #666;
        }

        .loading-spinner i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }

        .empty-cart i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
            display: block;
        }

        .cart-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-item-image {
            width: 50px;
            height: 50px;
            border-radius: 6px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 4px;
            line-height: 1.3;
        }

        .cart-item-qty {
            font-size: 12px;
            color: #666;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
            line-height: 1.3;
        }

        .cart-item-details {
            font-size: 13px;
            color: #666;
        }

        .cart-item-price {
            font-weight: 600;
            color: #ff6b35;
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .cart-item-remove:hover {
            background: #f8f9fa;
            color: #dc3545;
        }

        .cart-total {
            background: #f8f9fa;
            padding: 20px;
            border-top: 2px solid #ff6b35;
        }

        .total-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-text {
            font-weight: 600;
            color: #333;
        }

        .btn-cart {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .btn-cart:hover {
            background: #e55a2b;
            color: white;
            text-decoration: none;
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
                        <a href="/WEB_MXH/index.php" class="btn" style="
                            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
                            color: white;
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
                        <div class="cart-item-content">
                            <img src="${imageUrl}" 
                                 alt="${item.item_name}" class="cart-item-image"
                                 onerror="this.src='<?php echo $base_path; ?>assets/images/default-product.jpg'">
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.item_name}</div>
                                <div class="cart-item-qty">Số lượng: ${item.quantity}</div>
                            </div>
                        </div>
                    </li>
                `;
            });

            $('#cart-items').html(cartHTML);
            $('#total-amount').text('$' + parseFloat(totalAmount).toFixed(2));
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