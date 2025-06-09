<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';

$currentPage = 'customer';

// Xử lý số lượng dòng hiển thị
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [10, 25, 50, 100]) ? $rowsPerPage : 10;

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng query với tìm kiếm
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where_clause .= " AND (u.user_id LIKE ? OR u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param, $search_param];
    $types = "sssss";
}

// Đếm tổng số users
$count_query = "
    SELECT COUNT(*) as total 
    FROM users u 
    {$where_clause}
";

if ($params) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
} else {
    $total_result = $conn->query($count_query);
}

$totalRows = $total_result->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$offset = ($currentPageNumber - 1) * $rowsPerPage;

// Lấy dữ liệu users với thống kê đơn hàng
$users_query = "
    SELECT 
        u.user_id,
        u.username,
        u.full_name,
        u.email,
        u.phone,
        u.gender,
        u.address,
        u.created_at,
        u.balance,
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.final_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.buyer_id
    {$where_clause}
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";

if ($params) {
    $params[] = $rowsPerPage;
    $params[] = $offset;
    $types .= "ii";
    $stmt = $conn->prepare($users_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users_result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($users_query);
    $stmt->bind_param("ii", $rowsPerPage, $offset);
    $stmt->execute();
    $users_result = $stmt->get_result();
}

$users = [];
while ($user = $users_result->fetch_assoc()) {
    $users[] = $user;
}

// Tạo URL với các tham số hiện tại
function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}

// Function để tạo avatar từ tên
function generateAvatar($name) {
    $words = explode(' ', trim($name));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    } else {
        return strtoupper(substr($name, 0, 2));
    }
}

// Function để phân biệt admin/user dựa trên user_id hoặc username
function isAdmin($user) {
    // Admin có thể là user_id = 1 hoặc username chứa 'admin'
    return $user['user_id'] == 1 || stripos($user['username'], 'admin') !== false;
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Danh sách khách hàng - Admin</title>
    <!-- Favicon -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="all_customer.css" />
    
    <style>
        .customer-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .user-badge {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .table-modern {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .table-modern thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }
        
        .table-modern tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
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
            <div class="search-container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="hidden" name="entries" value="<?php echo $rowsPerPage; ?>">
                            <input type="text" 
                                   name="search" 
                                   class="form-control me-2" 
                                   placeholder="Tìm kiếm theo ID, tên, email, số điện thoại..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                        <form method="GET" class="d-flex align-items-center me-3">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <label class="me-2">Hiển thị:</label>
                            <select name="entries" class="form-select" style="width: auto;" onchange="this.form.submit()">
                                <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </form>
                        <button type="button" class="btn btn-success" onclick="exportCustomers()">
                            <i class="fas fa-file-export me-2"></i>
                            Xuất Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="table-modern">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="border-radius: 15px 0 0 0;">
                                <i class="fas fa-user me-2"></i>Khách hàng
                            </th>
                            <th>
                                <i class="fas fa-id-card me-2"></i>ID & Loại
                            </th>
                            <th>
                                <i class="fas fa-envelope me-2"></i>Liên hệ
                            </th>
                            <th>
                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                            </th>
                            <th>
                                <i class="fas fa-shopping-cart me-2"></i>Đơn hàng
                            </th>
                            <th style="border-radius: 0 15px 0 0;">
                                <i class="fas fa-money-bill me-2"></i>Tổng chi tiêu
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Không tìm thấy khách hàng nào</h5>
                                    <p class="text-muted">Hãy thử thay đổi từ khóa tìm kiếm</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar me-3">
                                                <?php echo generateAvatar($user['full_name']); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="text-muted small">@<?php echo htmlspecialchars($user['username']); ?></div>
                                                <?php if ($user['gender']): ?>
                                                    <small class="text-muted"><?php echo $user['gender']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">#<?php echo $user['user_id']; ?></div>
                                        <?php if (isAdmin($user)): ?>
                                            <span class="admin-badge">
                                                <i class="fas fa-crown me-1"></i>ADMIN
                                            </span>
                                        <?php else: ?>
                                            <span class="user-badge">
                                                <i class="fas fa-user me-1"></i>USER
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <?php if ($user['phone']): ?>
                                            <div>
                                                <i class="fas fa-phone text-success me-2"></i>
                                                <small><?php echo htmlspecialchars($user['phone']); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['address']): ?>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($user['address'], 0, 50)) . (strlen($user['address']) > 50 ? '...' : ''); ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Chưa cập nhật</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $user['total_orders']; ?> đơn</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success"><?php echo number_format($user['total_spent'], 0, '.', ','); ?>đ</div>
                                        <?php if ($user['balance'] > 0): ?>
                                            <small class="text-muted">Số dư: <?php echo number_format($user['balance'], 0, '.', ','); ?>đ</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Hiển thị <?php echo ($currentPageNumber - 1) * $rowsPerPage + 1; ?> - 
                        <?php echo min($currentPageNumber * $rowsPerPage, $totalRows); ?> 
                        trong tổng số <?php echo $totalRows; ?> khách hàng
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <!-- Previous -->
                            <li class="page-item <?php echo $currentPageNumber <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo getUrlWithParams(['page' => $currentPageNumber - 1]); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $currentPageNumber - 2);
                            $end_page = min($totalPages, $currentPageNumber + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $currentPageNumber ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo getUrlWithParams(['page' => $i]); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <!-- Next -->
                            <li class="page-item <?php echo $currentPageNumber >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo getUrlWithParams(['page' => $currentPageNumber + 1]); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    
    <script>
        function exportCustomers() {
            // Xuất danh sách khách hàng ra Excel
            const search = '<?php echo addslashes($search); ?>';
            window.location.href = `/WEB_MXH/admin/pages/customer/export_customers.php?search=${encodeURIComponent(search)}`;
        }
        
        // Search form enhancement
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });
        }
    </script>
  </body>
</html>
