<?php
session_start();
require_once '../../../config/database.php';

// Set current page for sidebar navigation
$currentPage = 'accessory';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = "WHERE accessory_name LIKE ? OR description LIKE ?";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param];
    $types = 'ss';
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM accessories $search_condition";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get accessories data
$sql = "SELECT * FROM accessories $search_condition ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['accessory_id'])) {
    $accessory_id = (int)$_POST['accessory_id'];
    $delete_sql = "DELETE FROM accessories WHERE accessory_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $accessory_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Xóa accessory thành công!";
        // Refresh page to show updated list
        header("Location: accessory_list.php?page=$page" . (!empty($search) ? "&search=" . urlencode($search) : ""));
        exit();
    } else {
        $error_message = "Lỗi khi xóa accessory: " . $delete_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Accessories - AuraDisc Admin</title>
    
    <!-- CSS giống các trang khác -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        .accessory-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .stock-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-high { background: #28a745; color: white; }
        .stock-medium { background: #ffc107; color: #000; }
        .stock-low { background: #dc3545; color: white; }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php 
    if (file_exists(__DIR__.'/../dashboard/sidebar.php')) {
        include __DIR__.'/../dashboard/sidebar.php'; 
    }
    ?>
    
    <div class="content">
        <?php 
        if (file_exists(__DIR__.'/../dashboard/navbar.php')) {
            include __DIR__.'/../dashboard/navbar.php'; 
        }
        ?>
        
        <div class="container-fluid pt-4 px-4">
            <!-- Header -->
            <div class="bg-secondary rounded h-100 p-4 mb-4">
                <h2 class="mb-2"><i class="fas fa-headphones me-2"></i>Quản Lý Accessories</h2>
                <p class="mb-0">Quản lý các phụ kiện âm nhạc và thiết bị audio</p>
            </div>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
            <?php
            $stats_sql = "SELECT 
                COUNT(*) as total_accessories,
                SUM(stock) as total_stock,
                AVG(price) as avg_price,
                COUNT(CASE WHEN stock < 10 THEN 1 END) as low_stock
                FROM accessories";
            $stats_result = $conn->query($stats_sql);
            $stats = $stats_result->fetch_assoc();
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_accessories']); ?></div>
                <div class="stat-label">Tổng Accessories</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_stock']); ?></div>
                <div class="stat-label">Tổng Tồn Kho</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($stats['avg_price'], 2); ?></div>
                <div class="stat-label">Giá Trung Bình</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo $stats['low_stock']; ?></div>
                <div class="stat-label">Sắp Hết Hàng</div>
            </div>
        </div>
        
        <!-- Search and Add Button -->
        <div class="search-bar">
            <div class="row">
                <div class="col-md-8">
                    <form method="GET" class="d-flex">
                        <input type="text" class="form-control me-2" placeholder="Tìm kiếm accessories..." 
                               name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="accessory_list.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add_accessory.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Accessory
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Accessories Table -->
        <div class="accessories-table">
            <h5 class="mb-3">
                <i class="fas fa-list"></i> Danh Sách Accessories 
                <span class="badge bg-secondary"><?php echo $total_records; ?> items</span>
            </h5>
            
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình Ảnh</th>
                            <th>Tên Accessory</th>
                            <th>Giá</th>
                            <th>Tồn Kho</th>
                            <th>Ngày Tạo</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($accessory = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $accessory['accessory_id']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($accessory['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($accessory['accessory_name']); ?>"
                                     class="accessory-image"
                                     onerror="this.src='https://via.placeholder.com/60x60/ff6b35/ffffff?text=N/A'">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($accessory['accessory_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars(substr($accessory['description'], 0, 50)) . '...'; ?>
                                </small>
                            </td>
                            <td>
                                <span class="fw-bold text-warning">$<?php echo number_format($accessory['price'], 2); ?></span>
                            </td>
                            <td>
                                <?php
                                $stock = $accessory['stock'];
                                $badge_class = 'stock-high';
                                if ($stock < 5) $badge_class = 'stock-low';
                                elseif ($stock < 15) $badge_class = 'stock-medium';
                                ?>
                                <span class="stock-badge <?php echo $badge_class; ?>">
                                    <?php echo $stock; ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('d/m/Y H:i', strtotime($accessory['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_accessory.php?id=<?php echo $accessory['accessory_id']; ?>" 
                                       class="btn btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="deleteAccessory(<?php echo $accessory['accessory_id']; ?>, '<?php echo htmlspecialchars($accessory['accessory_name']); ?>')"
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Accessories pagination">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                            Sau <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5>Không có accessories nào</h5>
                <p class="text-muted">
                    <?php if (!empty($search)): ?>
                        Không tìm thấy accessories nào với từ khóa "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Chưa có accessories nào trong hệ thống
                    <?php endif; ?>
                </p>
                <a href="add_accessory.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm Accessory Đầu Tiên
                </a>
            </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>
    
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác Nhận Xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa accessory <strong id="accessoryName"></strong>?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="accessory_id" id="deleteAccessoryId">
                    <button type="submit" name="delete" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
    
<script src="/WEB_MXH/admin/pages/dashboard/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    <script>
        function deleteAccessory(id, name) {
            document.getElementById('deleteAccessoryId').value = id;
            document.getElementById('accessoryName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

<?php
$stmt->close();
$count_stmt->close();
$conn->close();
?> 