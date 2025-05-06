<?php $currentPage = 'order_detail'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <!-- Bootstrap CSS -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Order Detail CSS -->
    <link rel="stylesheet" href="order_detail.css">
</head>
<body>
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
                <a href="/WEB_MXH/admin/pages/product/product_list/product_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'product') === 0) echo 'active'; ?>">
                    <i class="fa fa-shopping-basket me-2"></i>Product List</a>
                <a href="/WEB_MXH/admin/pages/order/order_detail/order_detail.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'order') === 0) echo 'active'; ?>">
                    <i class="fa fa-receipt me-2"></i>Order List</a>
                <a href="#" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'customer') === 0) echo 'active'; ?>" ><i class="fa fa-user-astronaut me-2"></i>Customer List</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && strpos($currentPage, 'support') === 0) echo 'active'; ?>" data-bs-toggle="dropdown"><i class="fa fa-people-carry me-2"></i>Customer Support</a>
                    <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && strpos($currentPage, 'support') === 0) echo 'show'; ?>">
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'feedbacks') echo 'active'; ?>">Feedbacks</a>
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'messages') echo 'active'; ?>">Messages</a>                        
                    </div>
                </div>
                <a href="#" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'refund') === 0) echo 'active'; ?>"><i class="fa fa-hand-holding-usd me-2"></i>Refund List</a>
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
    <div class="content">
    <div class="container">
        <header class="header">
            <div class="header-actions">
                <button class="btn-delete">Huỷ đơn</button>
                <div class="user-avatar">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                </div>
            </div>
        </header>
        <main class="main-content">
            <section class="order-info">
                <div class="order-title">
                    <h2>Đơn hàng #10001 <span class="badge paid">Đã thanh toán</span> <span class="badge ready">Sẵn sàng giao</span></h2>
                    <div class="order-date">Ngày đặt: 12/06/2024, 14:30</div>
                </div>
                <div class="order-details">
                    <h3>Chi tiết sản phẩm</h3>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="https://via.placeholder.com/40x40?text=Vinyl1" class="product-img">
                                    <div>
                                        <strong>Đĩa than: The Beatles - Abbey Road</strong><br>
                                        Định dạng: Vinyl, 180g
                                    </div>
                                </td>
                                <td>650.000đ</td>
                                <td>1</td>
                                <td>650.000đ</td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://via.placeholder.com/40x40?text=Vinyl2" class="product-img">
                                    <div>
                                        <strong>Đĩa than: Pink Floyd - The Wall</strong><br>
                                        Định dạng: Vinyl, 2LP
                                    </div>
                                </td>
                                <td>800.000đ</td>
                                <td>2</td>
                                <td>1.600.000đ</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="order-summary">
                        <div>Tạm tính: <span>2.250.000đ</span></div>
                        <div>Giảm giá: <span>0đ</span></div>
                        <div>Thuế: <span>0đ</span></div>
                        <div class="total">Tổng cộng: <span>2.250.000đ</span></div>
                    </div>
                </div>
            </section>
            <aside class="order-side">
                <div class="card customer-details">
                    <h4>Khách hàng</h4>
                    <div class="user-info">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                        <div>
                            <div><strong>Nguyễn Văn A</strong></div>
                            <div>Mã KH: #C1001</div>
                            <div>Đơn đã mua: 5</div>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div>Email: nguyenvana@email.com</div>
                        <div>Điện thoại: 0901 234 567</div>
                    </div>
                </div>
                <div class="card shipping-address">
                    <h4>Địa chỉ giao hàng</h4>
                    <div>123 Đường Lê Lợi, Quận 1, TP.HCM</div>
                </div>
                <div class="card billing-address">
                    <h4>Địa chỉ thanh toán</h4>
                    <div>123 Đường Lê Lợi, Quận 1, TP.HCM</div>
                </div>
                <div class="card payment-method">
                    <h4>Phương thức thanh toán</h4>
                    <div>Thẻ Visa **** 1234</div>
                </div>
            </aside>
        </main>
        <section class="shipping-activity">
            <h3>Hoạt động giao hàng</h3>
            <ul class="activity-list">
                <li>
                    <span class="dot active"></span>
                    Đơn hàng đã được đặt thành công <span class="activity-time">12/06/2024 14:30</span>
                </li>
                <li>
                    <span class="dot"></span>
                    Đang chuẩn bị hàng <span class="activity-time">12/06/2024 15:00</span>
                </li>
                <li>
                    <span class="dot"></span>
                    Đã bàn giao cho đơn vị vận chuyển <span class="activity-time">13/06/2024 09:00</span>
                </li>
                <li>
                    <span class="dot"></span>
                    Đang giao hàng <span class="activity-time">13/06/2024 15:00</span>
                </li>
                <li>
                    <span class="dot"></span>
                    Đã giao hàng thành công <span class="activity-time">14/06/2024 10:00</span>
                </li>
            </ul>
        </section>
    </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dashboard JS (nếu có) -->
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html>
