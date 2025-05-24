<!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-user-edit me-2"></i>AuraDisc</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="ms-3">
                        <h6 class="mb-0">ADMIN</h6>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="nav-item nav-link <?php if(isset($currentPage) && $currentPage == 'dashboard') echo 'active'; ?>">
                        <i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>                    
                    <a href="/WEB_MXH/admin/pages/product/product_list/product_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'product') === 0) echo 'active'; ?>"><i class="fa fa-shopping-basket me-2"></i>Product List</a>
                    <a href="/WEB_MXH/admin/pages/order/order_list/order_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'order') === 0) echo 'active'; ?>"><i class="fa fa-receipt me-2"></i>Order List</a>
                    <a href="/WEB_MXH/admin/pages/customer/all_customer/all_customer.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'customer') === 0) echo 'active'; ?>" ><i class="fa fa-user-astronaut me-2"></i>Customer List</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && (in_array($currentPage, ['feedbacks', 'messages']) || strpos($currentPage, 'support') === 0)) echo 'active'; ?>" data-bs-toggle="dropdown"><i class="fa fa-people-carry me-2"></i>Customer Support</a>
                        <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && (in_array($currentPage, ['feedbacks', 'messages']) || strpos($currentPage, 'support') === 0)) echo 'show'; ?>">
                            <a href="/WEB_MXH/admin/pages/customer_support/feedback/feedback.php" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'feedbacks') echo 'active'; ?>">Feedbacks</a>
                            <a href="/WEB_MXH/admin/pages/customer_support/messages/messages.php" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'messages') echo 'active'; ?>">Messages</a>                        
                        </div>
                    </div>
                    <a href="/WEB_MXH/admin/pages/refund/refund_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'refund') === 0) echo 'active'; ?>"><i class="fa fa-hand-holding-usd me-2"></i>Refund List</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && strpos($currentPage, 'setting') === 0) echo 'active'; ?>" data-bs-toggle="dropdown"><i class="fa fa-users-cog me-2"></i>Setting</a>
                        <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && strpos($currentPage, 'setting') === 0) echo 'show'; ?>">
                            <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'notification') echo 'active'; ?>">Notification</a>
                            <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'voucher') echo 'active'; ?>">Voucher</a>
                        </div>
                    </div>
                    <a href="#" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Log Out</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->