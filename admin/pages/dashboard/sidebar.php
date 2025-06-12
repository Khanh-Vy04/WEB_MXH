<!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-user-edit me-2"></i>AuraDisc</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="ms-3">
                        <h6 class="mb-0">ADMIN</h6>
                        <span class="text-muted">Administrator</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="nav-item nav-link <?php if(isset($currentPage) && $currentPage == 'dashboard') echo 'active'; ?>">
                        <i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && (strpos($currentPage, 'product') === 0 || strpos($currentPage, 'accessory') === 0)) echo 'active'; ?>" data-bs-toggle="dropdown">
                            <i class="fa fa-shopping-bag me-2"></i>Quản lý Sản phẩm
                        </a>
                        <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && (strpos($currentPage, 'product') === 0 || strpos($currentPage, 'accessory') === 0)) echo 'show'; ?>">
                            <a href="/WEB_MXH/admin/pages/product/product_list/product_list.php" class="dropdown-item <?php if(isset($currentPage) && strpos($currentPage, 'product') === 0) echo 'active'; ?>">
                                <i class="fa fa-compact-disc me-2"></i>Albums
                            </a>
                            <a href="/WEB_MXH/admin/pages/accessory/accessory_list.php" class="dropdown-item <?php if(isset($currentPage) && strpos($currentPage, 'accessory') === 0) echo 'active'; ?>">
                                <i class="fa fa-headphones me-2"></i>Phụ kiện
                            </a>
                        </div>
                    </div>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && (strpos($currentPage, 'artist') === 0 || strpos($currentPage, 'genre') === 0)) echo 'active'; ?>" data-bs-toggle="dropdown">
                            <i class="fa fa-music me-2"></i>Nội dung Âm nhạc
                        </a>
                        <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && (strpos($currentPage, 'artist') === 0 || strpos($currentPage, 'genre') === 0)) echo 'show'; ?>">
                            <a href="/WEB_MXH/admin/pages/artist/artist_list.php" class="dropdown-item <?php if(isset($currentPage) && strpos($currentPage, 'artist') === 0) echo 'active'; ?>">
                                <i class="fa fa-microphone me-2"></i>Nghệ sĩ
                            </a>
                            <a href="/WEB_MXH/admin/pages/genre/genre_list.php" class="dropdown-item <?php if(isset($currentPage) && strpos($currentPage, 'genre') === 0) echo 'active'; ?>">
                                <i class="fa fa-tags me-2"></i>Dòng Nhạc
                            </a>
                        </div>
                    </div>
                    
                    <a href="/WEB_MXH/admin/pages/banner/banner_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'banner') === 0) echo 'active'; ?>">
                        <i class="fa fa-images me-2"></i>Quản lý Banner
                    </a>
                    
                    <a href="/WEB_MXH/admin/pages/voucher/voucher_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'voucher') === 0) echo 'active'; ?>">
                        <i class="fa fa-ticket-alt me-2"></i>Voucher
                    </a>
                    
                    <a href="/WEB_MXH/admin/pages/order/order_list/order_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'order') === 0) echo 'active'; ?>">
                        <i class="fa fa-receipt me-2"></i>Đơn hàng
                    </a>
                    
                    <a href="/WEB_MXH/admin/pages/customer/all_customer/all_customer.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'customer') === 0 && $currentPage !== 'messages') echo 'active'; ?>">
                        <i class="fa fa-users me-2"></i>Khách hàng
                    </a>
                    
                    <a href="/WEB_MXH/admin/pages/customer_support/message/message.php" class="nav-item nav-link <?php if(isset($currentPage) && $currentPage == 'messages') echo 'active'; ?>">
                        <i class="fa fa-comments me-2"></i>Chat Support
                    </a>
                    

                    <a href="/WEB_MXH/logout.php" class="nav-item nav-link">
                        <i class="fa fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->
        
        <script>
        // JavaScript để maintain dropdown state
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarDropdowns = document.querySelectorAll('.sidebar .nav-item.dropdown');
            
            sidebarDropdowns.forEach(function(dropdown) {
                const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                
                // Chỉ xử lý dropdown đang active (có class 'show' từ PHP)
                if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                    dropdownToggle.setAttribute('aria-expanded', 'true');
                    
                    // Mark dropdown này là "locked" để không bị đóng
                    dropdown.classList.add('locked-open');
                    
                    // Prevent Bootstrap từ đóng dropdown đang active này
                    dropdownToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        // Giữ nguyên trạng thái mở
                    });
                    
                    // Prevent dropdown đóng khi click vào items bên trong
                    dropdownMenu.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                } else {
                    // Cho phép dropdown khác hoạt động bình thường
                    dropdownToggle.addEventListener('click', function(e) {
                        // Đóng tất cả dropdown khác trước khi mở cái mới
                        sidebarDropdowns.forEach(function(otherDropdown) {
                            if (otherDropdown !== dropdown && !otherDropdown.classList.contains('locked-open')) {
                                const otherMenu = otherDropdown.querySelector('.dropdown-menu');
                                const otherToggle = otherDropdown.querySelector('.dropdown-toggle');
                                if (otherMenu && otherToggle) {
                                    otherMenu.classList.remove('show');
                                    otherToggle.setAttribute('aria-expanded', 'false');
                                }
                            }
                        });
                        
                        // Toggle dropdown hiện tại
                        const isOpen = dropdownMenu.classList.contains('show');
                        if (isOpen) {
                            dropdownMenu.classList.remove('show');
                            dropdownToggle.setAttribute('aria-expanded', 'false');
                        } else {
                            dropdownMenu.classList.add('show');
                            dropdownToggle.setAttribute('aria-expanded', 'true');
                        }
                        
                        e.preventDefault();
                        e.stopPropagation();
                    });
                }
            });
            
            // Đóng dropdown khi click bên ngoài (trừ dropdown locked)
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.sidebar .dropdown')) {
                    sidebarDropdowns.forEach(function(dropdown) {
                        if (!dropdown.classList.contains('locked-open')) {
                            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                            const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
                            if (dropdownMenu && dropdownToggle) {
                                dropdownMenu.classList.remove('show');
                                dropdownToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                }
            });
            
            console.log('Sidebar dropdown states maintained - active dropdown locked, others free');
        });
        </script>