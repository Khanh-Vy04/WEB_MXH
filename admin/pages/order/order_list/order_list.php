<?php
$currentPage = 'orderlist';
// Mock data - Trong thực tế sẽ lấy từ database
$orders = [
    [
        'id' => '6979',
        'date' => 'Apr 15, 2023, 10:21',
        'customer' => [
            'name' => 'Cristine Easom',
            'email' => 'ceasomw@theguardian.com',
            'avatar' => 'CE'
        ],
        'payment_status' => 'pending',
        'delivery_status' => 'delivered',
        'payment_method' => 'credit card'
    ],
    [
        'id' => '6624',
        'date' => 'Apr 17, 2023, 6:43',
        'customer' => [
            'name' => 'Fayre Screech',
            'email' => 'fscreechs@army.mil',
            'avatar' => 'FS'
        ],
        'payment_status' => 'failed',
        'delivery_status' => 'delivered',
        'payment_method' => 'momo'
    ],
    [
        'id' => '9305',
        'date' => 'Apr 17, 2023, 8:05',
        'customer' => [
            'name' => 'Pauline Pfaffe',
            'email' => 'ppfaffe1i@wikia.com',
            'avatar' => 'PP'
        ],
        'payment_status' => 'cancelled',
        'delivery_status' => 'out-delivery',
        'payment_method' => 'cash on delivery'
    ],
    [
        'id' => '1234',
        'date' => 'Apr 18, 2023, 9:15',
        'customer' => [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'avatar' => 'JD'
        ],
        'payment_status' => 'pending',
        'delivery_status' => 'delivered',
        'payment_method' => 'credit card'
    ],
    [
        'id' => '5678',
        'date' => 'Apr 19, 2023, 11:30',
        'customer' => [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'avatar' => 'AS'
        ],
        'payment_status' => 'failed',
        'delivery_status' => 'delivered',
        'payment_method' => 'momo'
    ],
    [
        'id' => '9012',
        'date' => 'Apr 20, 2023, 14:45',
        'customer' => [
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'avatar' => 'BW'
        ],
        'payment_status' => 'cancelled',
        'delivery_status' => 'out-delivery',
        'payment_method' => 'cash on delivery'
    ],
    [
        'id' => '3456',
        'date' => 'Apr 21, 2023, 16:20',
        'customer' => [
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'avatar' => 'CB'
        ],
        'payment_status' => 'pending',
        'delivery_status' => 'delivered',
        'payment_method' => 'credit card'
    ],
    [
        'id' => '7890',
        'date' => 'Apr 22, 2023, 09:30',
        'customer' => [
            'name' => 'David Lee',
            'email' => 'david@example.com',
            'avatar' => 'DL'
        ],
        'payment_status' => 'failed',
        'delivery_status' => 'delivered',
        'payment_method' => 'momo'
    ],
    [
        'id' => '2345',
        'date' => 'Apr 23, 2023, 11:15',
        'customer' => [
            'name' => 'Emma Watson',
            'email' => 'emma@example.com',
            'avatar' => 'EW'
        ],
        'payment_status' => 'cancelled',
        'delivery_status' => 'out-delivery',
        'payment_method' => 'cash on delivery'
    ]
];

// Xử lý số lượng dòng hiển thị
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 3;
$rowsPerPage = in_array($rowsPerPage, [3, 10, 25, 50, 100]) ? $rowsPerPage : 3;

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $orders = array_filter($orders, function($order) use ($search) {
        return stripos($order['id'], $search) !== false ||
               stripos($order['customer']['name'], $search) !== false ||
               stripos($order['customer']['email'], $search) !== false;
    });
}

// Xử lý phân trang
$totalRows = count($orders);
$totalPages = ceil($totalRows / $rowsPerPage);

// Lấy trang hiện tại từ URL
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // Đảm bảo trang hợp lệ

// Tính toán vị trí bắt đầu và kết thúc
$start = ($currentPage - 1) * $rowsPerPage;
$end = min($start + $rowsPerPage, $totalRows);

// Lấy dữ liệu cho trang hiện tại
$currentPageOrders = array_slice($orders, $start, $rowsPerPage);

// Debug thông tin
error_log("Total Rows: " . $totalRows);
error_log("Rows Per Page: " . $rowsPerPage);
error_log("Total Pages: " . $totalPages);
error_log("Current Page: " . $currentPage);
error_log("Start: " . $start);
error_log("End: " . $end);

// Tạo URL với các tham số hiện tại
function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['order_id'])) {
                    // Xử lý xóa đơn hàng
                    $orderId = $_POST['order_id'];
                    // Thêm code xử lý xóa ở đây
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
                break;
            case 'export':
                // Xử lý export
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="orders.csv"');
                // Thêm code export ở đây
                exit;
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order List</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌐</text></svg>">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="order_list.css" />
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
          <!-- Stats Cards -->
          <div class="stats-container">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="stat-info">
                <h2>56</h2>
                <p>Pending Payment</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="stat-info">
                <h2>12,689</h2>
                <p>Completed</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
              </div>
              <div class="stat-info">
                <h2>124</h2>
                <p>Refunded</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
              </div>
              <div class="stat-info">
                <h2>32</h2>
                <p>Failed</p>
              </div>
            </div>
          </div>

          <!-- Search and Filter Bar -->
          <div class="controls-container">
            <form method="GET" class="search-bar">
              <input 
                type="text" 
                name="search" 
                placeholder="Search Order" 
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

          <!-- Orders Table -->
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" class="select-all" />
                  </th>
                  <th>ORDER</th>
                  <th>DATE</th>
                  <th>CUSTOMERS</th>
                  <th>PAYMENT</th>
                  <th>STATUS</th>
                  <th>METHOD</th>
                  <th>ACTIONS</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($currentPageOrders as $order): ?>
                <tr>
                  <td><input type="checkbox" name="selected_orders[]" value="<?php echo $order['id']; ?>" /></td>
                  <td><a href="#" class="order-link">#<?php echo $order['id']; ?></a></td>
                  <td><?php echo $order['date']; ?></td>
                  <td class="customer-cell">
                    <div class="customer-avatar"><?php echo $order['customer']['avatar']; ?></div>
                    <div class="customer-info">
                      <div class="customer-name"><?php echo $order['customer']['name']; ?></div>
                      <div class="customer-email"><?php echo $order['customer']['email']; ?></div>
                    </div>
                  </td>
                  <td><span class="status-badge <?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                  <td><span class="status-badge <?php echo $order['delivery_status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $order['delivery_status'])); ?></span></td>
                  <td class="payment-method">
                    <?php
                    $paymentIcons = [
                      'credit card' => 'fa-credit-card',
                      'momo' => 'fa-mobile-alt',
                      'cash on delivery' => 'fa-money-bill-wave'
                    ];
                    ?>
                    <i class="fas <?php echo $paymentIcons[$order['payment_method']]; ?>"></i>
                    <span class="payment-text"><?php echo ucwords($order['payment_method']); ?></span>
                  </td>
                  <td>
                    <div class="action-dropdown">
                      <button type="button" class="action-btn">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">
                          <i class="fas fa-eye"></i>
                          View
                        </a>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                          <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                            <i class="fas fa-trash-alt"></i>
                            Delete
                          </button>
                        </form>
                      </div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination">
            <span class="entries-info">Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $totalRows; ?> entries</span>
            <div class="pagination-controls">
              <?php if ($currentPage > 1): ?>
              <a href="<?php echo getUrlWithParams(['page' => $currentPage - 1]); ?>" class="page-btn">
                <i class="fas fa-chevron-left"></i>
              </a>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="<?php echo getUrlWithParams(['page' => $i]); ?>" class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                <?php echo $i; ?>
              </a>
              <?php endfor; ?>

              <?php if ($currentPage < $totalPages): ?>
              <a href="<?php echo getUrlWithParams(['page' => $currentPage + 1]); ?>" class="page-btn">
                <i class="fas fa-chevron-right"></i>
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
      </div>
      <!-- Content End -->
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

    <!-- Custom JavaScript for Order List -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Handle action dropdowns
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
          button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('show');
          });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.matches('.action-btn')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
              dropdown.classList.remove('show');
            });
          }
        });

        // Handle select all checkbox
        const selectAll = document.querySelector('.select-all');
        const checkboxes = document.querySelectorAll('input[name="selected_orders[]"]');
        
        selectAll.addEventListener('change', function() {
          checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
          });
        });

        // Handle navbar dropdowns
        const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
          toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('show');
          });
        });

        // Close navbar dropdowns when clicking outside
        document.addEventListener('click', function(e) {
          if (!e.target.matches('.nav-link.dropdown-toggle')) {
            document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(dropdown => {
              dropdown.classList.remove('show');
            });
          }
        });
      });
    </script>
  </body>
</html> 