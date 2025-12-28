<!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary" style="display: flex; align-items: center;">
                        <img src="/WEB_MXH/user/assets/logo/favicon.png" alt="UniWork" style="width: 40px; height: 40px; margin-right: 10px;">
                        UniWork
                    </h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="ms-3">
                        <h6 class="mb-0">FREELANCER</h6>
                        <span class="text-muted">Freelancer</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="nav-item nav-link <?php if(isset($currentPage) && $currentPage == 'dashboard') echo 'active'; ?>">
                        <i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    
                    <!-- Dự án của tôi (Old Product Management) -->
                    <a href="/WEB_MXH/admin/pages/product/product_list/product_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'product') === 0) echo 'active'; ?>">
                        <i class="fa fa-briefcase me-2"></i>Dự án của tôi
                    </a>

                    <!-- Quản lý dịch vụ (New) -->
                    <a href="/WEB_MXH/admin/pages/projects/project_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'projects') === 0) echo 'active'; ?>">
                        <i class="fa fa-tasks me-2"></i>Quản lý dịch vụ
                    </a>

                    <!-- Quản lý đánh giá, khiếu nại (New) -->
                    <a href="/WEB_MXH/admin/pages/reviews/review_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'reviews') === 0) echo 'active'; ?>">
                         <i class="fa fa-star me-2"></i>Đánh giá & Khiếu nại
                    </a>

                    <!-- Chat Support (Kept as not explicitly asked to remove, but maybe user wants it gone too? User said remove "Khách hàng". Chat is distinct. I'll keep for now) -->
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
        // JavaScript để maintain dropdown state - Simplified as dropdowns are removed but kept for future use if needed
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Existing script can remain or be simplified. I'll remove it since no dropdowns exist now to avoid errors or clean it up)
            // Actually, if I remove dropdowns from HTML, the script selectors won't find anything, so it's harmless but redundant.
            // I'll leave a minimal version.
        });
        </script>
