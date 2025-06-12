<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../includes/session.php';

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
        u.role_id,
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

// Lấy ID admin hiện tại
$current_admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
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
        /* Strong footer positioning fix */
        html, body {
            height: 100%;
            margin: 0;
        }
        
        .container-fluid.position-relative.d-flex.p-0 {
            min-height: 100vh;
            display: flex;
        }
        
        .content {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .content > .container-fluid.pt-4.px-4:first-of-type {
            flex: 1;
        }
        
        /* Target footer specifically */
        .content > .container-fluid.pt-4.px-4:last-of-type {
            margin-top: auto;
        }

        .customer-container {
            background: #F5F5F5;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid #E0E0E0;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            color: #333;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            background: #FFFFFF;
            border: 1px solid #DDD;
            border-radius: 8px;
            padding: 10px 15px;
            color: #333;
            width: 300px;
            max-width: 100%;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #deccca;
            box-shadow: 0 0 0 2px rgba(222, 204, 202, 0.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .entries-select {
            background: #FFFFFF;
            border: 1px solid #DDD;
            color: #333;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .btn-add {
            background: #deccca;
            color: #412d3b;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-add:hover {
            background: #c9b5b0;
            color: #412d3b;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(65, 45, 59, 0.15);
        }
        
        .table-modern {
            width: 100%;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #E0E0E0;
        }
        
        .table-modern th {
            background: #F8F9FA;
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 2px solid #412d3b;
            white-space: nowrap;
        }
        
        .table-modern td {
            padding: 15px;
            color: #333;
            border-bottom: 1px solid #E0E0E0;
            vertical-align: middle;
            background: #FFFFFF;
        }
        
        .table-modern tr:last-child td {
            border-bottom: none;
        }
        
        .table-modern tr:hover {
            background: #F8F9FA;
        }
        
        .customer-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #412d3b, #6c4a57) !important;
            color: white !important;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .user-badge {
            background: linear-gradient(135deg, #deccca, #c4b5b0) !important;
            color: #412d3b !important;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: #FFFFFF;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #DDD;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #deccca;
            color: #412d3b;
            text-decoration: none;
            border-color: #deccca;
        }
        
        .pagination .current {
            background: #deccca;
            color: #412d3b;
            border-color: #deccca;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .header-section, .controls-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .table-modern {
                font-size: 0.9rem;
            }
            
            .table-modern th, .table-modern td {
                padding: 10px;
            }
        }
    </style>
  </head>
  <body>
    <div class="container-fluid position-relative d-flex p-0">
      <!-- Sidebar -->
      vậy hãy tiếp tục so với<?php 
            if (file_exists(__DIR__.'/../../dashboard/sidebar.php')) {
            include __DIR__.'/../../dashboard/sidebar.php'; 
            }
        ?>

      <!-- Content Start -->
      <div class="content">
        <!-- Navbar -->
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>

        <!-- Main content -->
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="customer-container">
                        <!-- Header -->
                        <div class="header-section">
                            <h2 class="page-title">
                                <i class="fas fa-users me-3"></i>Quản Lý Khách Hàng
                            </h2>
                            <button type="button" class="btn-add" onclick="exportCustomers()">
                                <i class="fas fa-file-export me-2"></i>Xuất Excel
                            </button>
                        </div>
                        
                        <!-- Controls -->
                        <div class="controls-section">
                            <form method="GET" style="margin: 0;">
                                <input type="text" 
                                       name="search" 
                                       class="search-box"
                                       placeholder="Tìm kiếm theo ID, tên, email, số điện thoại..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </form>
                            
                            <div class="btn-group">
                                <form method="GET" style="margin: 0;">
                                    <select name="entries" class="entries-select" onchange="this.form.submit()">
                                        <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </form>
                                <small class="text-muted">
                                    Tổng số: <strong><?php echo $totalRows; ?></strong> khách hàng
                                </small>
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
                                        <th style="text-align: center;">
                                            <i class="fas fa-envelope me-2"></i>Liên hệ
                                        </th>
                                        <th style="text-align: center;">
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
                                                    <div class="d-flex align-items-center justify-content-between">
                                                    <?php if (isAdmin($user)): ?>
                                                        <span class="admin-badge">
                                                            <i class="fas fa-crown me-1"></i>ADMIN
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="user-badge">
                                                            <i class="fas fa-user me-1"></i>USER
                                                        </span>
                                                    <?php endif; ?>
                                                        
                                                        <?php if ($user['user_id'] != $current_admin_id): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm ms-2 toggle-role-btn" 
                                                                    data-user-id="<?php echo $user['user_id']; ?>"
                                                                    data-current-role="<?php echo isAdmin($user) ? 'admin' : 'user'; ?>"
                                                                    title="<?php echo isAdmin($user) ? 'Chuyển thành User' : 'Chuyển thành Admin'; ?>"
                                                                    style="<?php echo isAdmin($user) ? 'background: #deccca; border-color: #deccca; color: #412d3b;' : 'background: #412d3b; border-color: #412d3b; color: white;'; ?>">
                                                                <i class="fas fa-exchange-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
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
                                                <td style="text-align: center;">
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
        </div>
        
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
      </div>
    </div>

    <!-- JavaScript -->
    <!-- Custom Modal for confirmations -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="confirmModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận thay đổi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-shield fa-3x" style="color: #412d3b; opacity: 0.7;"></i>
                    </div>
                    <p id="confirmMessage" class="text-center mb-0" style="font-size: 16px; line-height: 1.5;"></p>
                </div>
                <div class="modal-footer" style="border: none; padding: 20px 25px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px;">
                        <i class="fas fa-times me-1"></i>Hủy bỏ
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmButton" style="background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%); border: none; border-radius: 8px; padding: 8px 20px;">
                        <i class="fas fa-check me-1"></i>Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle me-2"></i>Thành công
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle fa-3x" style="color: #28a745; opacity: 0.8;"></i>
                    </div>
                    <p id="successMessage" class="text-center mb-0" style="font-size: 16px; line-height: 1.5;"></p>
                </div>
                <div class="modal-footer" style="border: none; padding: 20px 25px;">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px; width: 100%;">
                        <i class="fas fa-check me-1"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%); color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="errorModalLabel">
                        <i class="fas fa-exclamation-circle me-2"></i>Lỗi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <div class="text-center mb-3">
                        <i class="fas fa-times-circle fa-3x" style="color: #dc3545; opacity: 0.8;"></i>
                    </div>
                    <p id="errorMessage" class="text-center mb-0" style="font-size: 16px; line-height: 1.5;"></p>
                </div>
                <div class="modal-footer" style="border: none; padding: 20px 25px;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" style="border-radius: 8px; padding: 8px 20px; width: 100%;">
                        <i class="fas fa-times me-1"></i>Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

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
        
        // Modal functions
        function showConfirmModal(message, onConfirm) {
            document.getElementById('confirmMessage').textContent = message;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            confirmModal.show();
            
            // Remove existing event listeners
            const newConfirmButton = document.getElementById('confirmButton').cloneNode(true);
            document.getElementById('confirmButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmButton'));
            
            newConfirmButton.addEventListener('click', function() {
                confirmModal.hide();
                onConfirm();
            });
        }
        
        function showSuccessModal(message, onClose = null) {
            document.getElementById('successMessage').textContent = message;
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            if (onClose) {
                document.getElementById('successModal').addEventListener('hidden.bs.modal', onClose, { once: true });
            }
        }
        
        function showErrorModal(message) {
            document.getElementById('errorMessage').textContent = message;
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        }

        // Toggle role functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-role-btn');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const currentRole = this.getAttribute('data-current-role');
                    const newRole = currentRole === 'admin' ? 'user' : 'admin';
                    const buttonElement = this;
                    
                    // Xác nhận trước khi thay đổi với popup
                    const confirmMessage = `Bạn có chắc chắn muốn chuyển tài khoản này thành ${newRole.toUpperCase()}?`;
                    
                    showConfirmModal(confirmMessage, function() {
                        // Disable button và hiển thị loading
                        buttonElement.disabled = true;
                        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        
                        // Gửi AJAX request
                        fetch('/WEB_MXH/admin/pages/customer/toggle_role.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `user_id=${userId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Hiển thị thông báo thành công với popup
                                showSuccessModal(data.message, function() {
                                    // Reload trang để cập nhật giao diện
                                    window.location.reload();
                                });
                            } else {
                                // Hiển thị lỗi với popup
                                showErrorModal(data.message);
                                
                                // Khôi phục button
                                buttonElement.disabled = false;
                                buttonElement.innerHTML = '<i class="fas fa-exchange-alt"></i>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showErrorModal('Đã xảy ra lỗi khi thực hiện thao tác');
                            
                            // Khôi phục button
                            buttonElement.disabled = false;
                            buttonElement.innerHTML = '<i class="fas fa-exchange-alt"></i>';
                        });
                    });
                });
            });
        });
    </script>
  </body>
</html>
