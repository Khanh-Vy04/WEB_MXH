<?php
$currentPage = 'customer'; // Để sidebar highlight đúng mục
// Mock data khách hàng
$customers = [
    [
        'id' => '1001',
        'name' => 'Nguyen Van A',
        'email' => 'a.nguyen@example.com',
        'avatar' => 'NA',
        'address' => '123 Lê Lợi, Q.1, TP.HCM',
        'orders' => 5,
        'total_spent' => 1500.00
    ],
    [
        'id' => '1002',
        'name' => 'Tran Thi B',
        'email' => 'b.tran@example.com',
        'avatar' => 'TB',
        'address' => '45 Nguyễn Huệ, Q.1, TP.HCM',
        'orders' => 3,
        'total_spent' => 900.00
    ],
    [
        'id' => '1003',
        'name' => 'Le Van C',
        'email' => 'c.le@example.com',
        'avatar' => 'LC',
        'address' => '12 Hai Bà Trưng, Q.3, TP.HCM',
        'orders' => 7,
        'total_spent' => 2100.00
    ],
    [
        'id' => '1004',
        'name' => 'Pham Thi D',
        'email' => 'd.pham@example.com',
        'avatar' => 'PD',
        'address' => '88 Lý Thường Kiệt, Q.10, TP.HCM',
        'orders' => 2,
        'total_spent' => 600.00
    ],
    [
        'id' => '1005',
        'name' => 'Hoang Van E',
        'email' => 'e.hoang@example.com',
        'avatar' => 'HE',
        'address' => '22 Trần Hưng Đạo, Q.5, TP.HCM',
        'orders' => 4,
        'total_spent' => 1200.00
    ],
    [
        'id' => '1006',
        'name' => 'Do Thi F',
        'email' => 'f.do@example.com',
        'avatar' => 'DF',
        'address' => '9 Nguyễn Trãi, Q.1, TP.HCM',
        'orders' => 6,
        'total_spent' => 1800.00
    ],
    [
        'id' => '1007',
        'name' => 'Bui Van G',
        'email' => 'g.bui@example.com',
        'avatar' => 'BG',
        'address' => '77 Cách Mạng Tháng 8, Q.3, TP.HCM',
        'orders' => 1,
        'total_spent' => 300.00
    ],
    [
        'id' => '1008',
        'name' => 'Vo Thi H',
        'email' => 'h.vo@example.com',
        'avatar' => 'VH',
        'address' => '55 Lê Văn Sỹ, Q.Phú Nhuận, TP.HCM',
        'orders' => 8,
        'total_spent' => 2400.00
    ],
    [
        'id' => '1009',
        'name' => 'Dang Van I',
        'email' => 'i.dang@example.com',
        'avatar' => 'DI',
        'address' => '101 Nguyễn Đình Chiểu, Q.3, TP.HCM',
        'orders' => 2,
        'total_spent' => 600.00
    ],
    [
        'id' => '1010',
        'name' => 'Phan Thi K',
        'email' => 'k.phan@example.com',
        'avatar' => 'PK',
        'address' => '33 Nguyễn Thị Minh Khai, Q.1, TP.HCM',
        'orders' => 9,
        'total_spent' => 2700.00
    ],
];

// Xử lý số lượng dòng hiển thị
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 3;
$rowsPerPage = in_array($rowsPerPage, [3, 10, 25, 50, 100]) ? $rowsPerPage : 3;

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $customers = array_filter($customers, function($cus) use ($search) {
        return stripos($cus['id'], $search) !== false ||
               stripos($cus['name'], $search) !== false ||
               stripos($cus['email'], $search) !== false ||
               stripos($cus['address'], $search) !== false;
    });
}

// Xử lý phân trang
$totalRows = count($customers);
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$start = ($currentPageNumber - 1) * $rowsPerPage;
$end = min($start + $rowsPerPage, $totalRows);
$currentPageCustomers = array_slice($customers, $start, $rowsPerPage);

// Tạo URL với các tham số hiện tại
function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer List</title>
    <!-- Favicon -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="all_customer.css" />
  </head>
  <body>
    <div class="container-fluid position-relative d-flex p-0">
      <!-- Sidebar -->
      <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>

      <!-- Content Start -->
      <div class="content">
        <!-- Navbar -->
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>

        <!-- Main content -->
        <div class="container-fluid pt-4 px-4">
            <!-- Search and Filter Bar -->
            <div class="controls-container">
                <form method="GET" class="search-bar">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search Customer" 
                        value="<?php echo htmlspecialchars($search); ?>"
                    />
                </form>
                <div class="right-controls">
                    <form method="GET" class="entries-dropdown">
                        <select name="entries" onchange="this.form.submit()">
                            <option value="3" <?php echo $rowsPerPage == 3 ? 'selected' : ''; ?>>3</option>
                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="action" value="export">
                        <button type="submit" class="export-btn">
                            <i class="fas fa-file-export"></i>
                            Export
                        </button>
                    </form>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="select-all" /></th>
                            <th>CUSTOMER</th>
                            <th>CUSTOMER ID</th>
                            <th>ADDRESS</th>
                            <th>ORDER</th>
                            <th>TOTAL SPENT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currentPageCustomers as $cus): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_customers[]" value="<?php echo $cus['id']; ?>" /></td>
                            <td class="customer-cell">
                                <div class="customer-avatar"><?php echo $cus['avatar']; ?></div>
                                <div class="customer-info">
                                    <div class="customer-name"><?php echo $cus['name']; ?></div>
                                    <div class="customer-email"><?php echo $cus['email']; ?></div>
                                </div>
                            </td>
                            <td>#<?php echo $cus['id']; ?></td>
                            <td><?php echo $cus['address']; ?></td>
                            <td><?php echo $cus['orders']; ?></td>
                            <td>$<?php echo number_format($cus['total_spent'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <span class="entries-info">Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $totalRows; ?> entries</span>
                <div class="pagination-controls">
                    <?php if ($currentPageNumber > 1): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $currentPageNumber - 1]); ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $i]); ?>" class="page-btn <?php echo $i === $currentPageNumber ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($currentPageNumber < $totalPages): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $currentPageNumber + 1]); ?>" class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
      </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/lib/chart/chart.min.js"></script>
    <script src="/WEB_MXH/admin/lib/easing/easing.min.js"></script>
    <script src="/WEB_MXH/admin/lib/waypoints/waypoints.min.js"></script>
    <script src="/WEB_MXH/admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    <script src="../order_list/order_list.js"></script>
  </body>
</html>
