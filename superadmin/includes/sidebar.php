<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-secondary navbar-dark">
        <a href="../../dashboard.php" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary"><i class="fa fa-user-shield me-2"></i>SuperAdmin</h3>
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="../../img/user.jpg" alt="" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">Super Admin</h6>
                <span class="text-muted">System Owner</span>
            </div>
        </div>
        <div class="navbar-nav w-100">
            <a href="../../dashboard.php" class="nav-item nav-link <?php echo (isset($currentPage) && $currentPage == 'dashboard') ? 'active' : ''; ?>"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
            
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle <?php echo (isset($currentPage) && $currentPage == 'users') ? 'active' : ''; ?>" data-bs-toggle="dropdown"><i class="fa fa-users-cog me-2"></i>Quản lý User</a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="../users/index.php?role=1" class="dropdown-item">Freelancers (Admin)</a>
                    <a href="../users/index.php?role=2" class="dropdown-item">End Users</a>
                    <a href="../users/index.php" class="dropdown-item">Tất cả Users</a>
                </div>
            </div>
            
            <a href="../banners/index.php" class="nav-item nav-link <?php echo (isset($currentPage) && $currentPage == 'banner') ? 'active' : ''; ?>"><i class="fa fa-images me-2"></i>Quản lý Banner</a>
            <a href="#" class="nav-item nav-link"><i class="fa fa-layer-group me-2"></i>Quản lý Nội dung</a>
            <a href="#" class="nav-item nav-link"><i class="fa fa-cogs me-2"></i>Cấu hình hệ thống</a>
            <a href="/WEB_MXH/logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Đăng xuất</a>
        </div>
    </nav>
</div>
<!-- Sidebar End -->