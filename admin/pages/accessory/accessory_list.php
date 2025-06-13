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
    
    <!-- CSS -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        .content { background: #f3f4f6 !important; }
        .container-fluid { background: #f7f8f9; }
        .header-section { 
            background: #fff; 
            border-radius: 18px; 
            padding: 1.1rem 1.5rem 1.2rem 1.5rem; 
            margin-bottom: 1.5rem; 
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1.5px 4px rgba(0, 0, 0, 0.04); 
        }
        .header-section h2 { 
            color: #222; 
            font-size: 1.5rem; 
            font-weight: 600;
            margin-bottom: 0.7rem; 
            margin-left: 0.1rem;
        }
        .header-section p { 
            color: #444; 
            margin-bottom: 0; 
        }
        .stat-card { 
            background: #fff; 
            border: none; 
            border-radius: 16px; 
            padding: 1.2rem 1.5rem; 
            margin-bottom: 1.5rem; 
            text-align: center; 
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); 
            height: 120px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            transition: transform 0.3s; 
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
        }
        .stat-number { 
            font-size: 1.8rem; 
            font-weight: bold; 
            color: #222; 
            margin-bottom: 5px; 
        }
        .stat-label { 
            color: #444; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 500; 
        }
        .stat-card.low-stock .stat-number { 
            color: #991b1b; 
        }
        .table-section { 
            background: #fff; 
            border-radius: 16px; 
            padding: 1.2rem 1.5rem; 
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); 
        }
        .search-bar { 
            display: flex; 
            gap: 1.5rem; 
            margin-bottom: 1.5rem; 
        }
        .search-bar input { 
            flex: 1; 
            padding: 0.6rem 1rem; 
            border: 2px solid #222; 
            border-radius: 10px; 
            font-size: 1rem; 
            color: #444;
            background: #fff; 
            transition: border-color 0.2s; 
        }
        .search-bar input:focus { 
            border-color: #7b61ff; 
            outline: none; 
            box-shadow: 0 0 0 2px #edeaff; 
        }
        .add-btn { 
            background: #deccca; 
            color: #412d3b; 
            border: none; 
            border-radius: 10px; 
            padding: 0.5rem 1.2rem; 
            font-size: 1rem; 
            font-weight: 500; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            transition: all 0.2s ease; 
            text-decoration: none; 
        }
        .add-btn i {
            color: #412d3b;
            font-size: 1.1rem;
        }
        .add-btn:hover { 
            background: #c9b5b0; 
            color: #412d3b; 
            transform: translateY(-1px); 
            box-shadow: 0 4px 8px rgba(65, 45, 59, 0.15); 
        }
        .table { 
            width: 100%; 
            margin-bottom: 0; 
            border-collapse: collapse; 
            table-layout: fixed; 
        }
        .table th { 
            background: #f9fafb; 
            color: #222; 
            font-weight: 700; 
            padding: 1rem; 
            text-align: center; 
            vertical-align: middle; 
            border-bottom: 2px solid #e5e7eb; 
        }
        .table td { 
            padding: 1rem; 
            text-align: center; 
            vertical-align: middle; 
            color: #222; 
            background: #fff;
            border-bottom: 1px solid #e5e7eb; 
        }
        /* Thêm style cho các cột cụ thể */
        .table th:nth-child(1), /* ID */
        .table td:nth-child(1) {
            width: 5%;
        }
        .table th:nth-child(2), /* Hình ảnh */
        .table td:nth-child(2) {
            width: 8%;
        }
        .table th:nth-child(3), /* Tên Accessory */
        .table td:nth-child(3) {
            width: 30%;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table th:nth-child(4), /* Giá */
        .table td:nth-child(4) {
            width: 15%;
        }
        .table th:nth-child(5), /* Tồn kho */
        .table td:nth-child(5) {
            width: 12%;
        }
        .table th:nth-child(6), /* Ngày tạo */
        .table td:nth-child(6) {
            width: 15%;
        }
        .table th:nth-child(7), /* Thao tác */
        .table td:nth-child(7) {
            width: 15%;
        }
        .table tbody tr:hover { 
            background-color: #f9fafb; 
        }
        .accessory-image { 
            width: 44px; 
            height: 44px; 
            object-fit: cover; 
            border-radius: 8px; 
            background: #f3f4f6;
            border: 2px solid #e5e7eb; 
            transition: transform 0.3s; 
        }
        .accessory-image:hover { 
            transform: scale(1.1); 
        }
        .stock-badge { 
            padding: 0.3em 1em; 
            border-radius: 999px; 
            font-size: 0.95em; 
            font-weight: 500; 
            display: inline-block; 
        }
        .stock-high { 
            background: #dcfce7; 
            color: #166534; 
        }
        .stock-medium { 
            background: #fef3c7; 
            color: #92400e; 
        }
        .stock-low { 
            background: #fee2e2; 
            color: #991b1b; 
        }
        .btn-group-sm .btn { 
            padding: 0.5rem; 
            font-size: 0.875rem; 
            border-radius: 8px; 
            transition: all 0.2s; 
        }
        .btn-warning { 
            background: #deccca !important; 
            border-color: #deccca !important; 
            color: #412d3b !important; 
        }
        .btn-warning:hover { 
            background: #c9b5b0 !important; 
            border-color: #c9b5b0 !important; 
            color: #412d3b !important; 
            transform: translateY(-1px); 
            box-shadow: 0 4px 8px rgba(65, 45, 59, 0.15); 
        }
        .btn-danger { 
            background:rgb(255, 87, 87) !important; 
            border-color: rgb(255, 172, 172) !important; 
            color: #fff !important; 
        }
        .btn-danger:hover { 
            background: #b91c1c !important; 
            border-color: #b91c1c !important; 
            color: #fff !important; 
            transform: translateY(-1px); 
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.2); 
        }
        .pagination { 
            margin-top: 1.5rem; 
            justify-content: center; 
            gap: 0.5rem; 
        }
        .page-link { 
            color: #222; 
            border: 2px solid #e5e7eb; 
            border-radius: 10px; 
            padding: 0.5rem 1rem; 
            transition: all 0.2s; 
            font-weight: 500; 
            min-width: 2.5rem;
            text-align: center;
        }
        .page-link:hover { 
            background: #edeaff; 
            border-color: #edeaff; 
            color: #222; 
            transform: translateY(-1px); 
        }
        .page-item.active .page-link { 
            background: #7b61ff; 
            border-color: #7b61ff; 
            color: #fff; 
        }
        .modal-content { 
            border-radius: 16px; 
            border: none; 
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); 
        }
        .modal-header { 
            border-bottom: 1px solid #e5e7eb; 
            padding: 1.2rem 1.5rem; 
            background: #f9fafb; 
            border-radius: 16px 16px 0 0; 
        }
        .modal-body { 
            padding: 1.2rem 1.5rem; 
        }
        .modal-footer { 
            border-top: 1px solid #e5e7eb; 
            padding: 1.2rem 1.5rem; 
            background: #f9fafb; 
            border-radius: 0 0 16px 16px; 
        }
        .alert { 
            border-radius: 16px; 
            border: none; 
            padding: 1.2rem 1.5rem; 
            margin-bottom: 1.5rem; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
        }
        .alert i { 
            font-size: 1.1rem; 
        }
        .alert-success { 
            background: #dcfce7; 
            color: #166534; 
        }
        .alert-danger { 
            background: #fee2e2; 
            color: #991b1b; 
        }
        .empty-state { 
            text-align: center; 
            padding: 2.5rem 1.5rem; 
        }
        .empty-state i { 
            font-size: 3rem; 
            color: #6b7280; 
            margin-bottom: 1rem; 
        }
        .empty-state h5 { 
            color: #222; 
            margin-bottom: 0.7rem; 
        }
        .empty-state p { 
            color: #6b7280; 
            margin-bottom: 1.5rem; 
        }
        @media (max-width: 768px) {
            .search-bar { 
                flex-direction: column; 
            }
            .stat-card { 
                height: 100px; 
                padding: 1rem; 
            }
            .stat-number { 
                font-size: 1.5rem; 
            }
            .stat-label { 
                font-size: 0.7rem; 
            }
            .table-responsive { 
                margin: 0 -15px; 
            }
            .table th, 
            .table td { 
                padding: 0.75rem; 
                font-size: 0.9rem; 
            }
            .btn-group-sm .btn { 
                padding: 0.4rem 0.8rem; 
            }
        }
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
            <div class="header-section">
                <h2><i class="fas fa-headphones me-2"></i>Quản Lý Phụ Kiện</h2>
                <p>Quản lý các phụ kiện âm nhạc và thiết bị audio</p>
                    </div>
            <div class="row g-4 mb-4">
                    <?php
                $stats_sql = "SELECT COUNT(*) as total_accessories, SUM(stock) as total_stock, AVG(price) as avg_price, COUNT(CASE WHEN stock < 10 THEN 1 END) as low_stock FROM accessories";
                    $stats_result = $conn->query($stats_sql);
                    $stats = $stats_result->fetch_assoc();
                    ?>
                <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="stat-number"><?php echo number_format($stats['total_accessories']); ?></div><div class="stat-label">Tổng phụ kiện</div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="stat-number"><?php echo number_format($stats['total_stock']); ?></div><div class="stat-label">Tổng tồn kho</div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="stat-number"><?php echo number_format($stats['avg_price'], 0, '', ','); ?>đ</div><div class="stat-label">Giá trung bình</div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card low-stock"><div class="stat-number"><?php echo $stats['low_stock']; ?></div><div class="stat-label">Sắp hết hàng</div></div></div>
            </div>
            <div class="table-section">
                <div class="search-bar">
                        <form method="GET" class="d-flex flex-grow-1">
                        <input type="text" class="form-control" placeholder="Tìm kiếm phụ kiện..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    <a href="add_accessory.php" class="add-btn"><i class="fas fa-plus"></i> Thêm phụ kiện</a>
                </div>
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                    <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên phụ kiện</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($accessory = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $accessory['accessory_id']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($accessory['image_url']); ?>" alt="<?php echo htmlspecialchars($accessory['accessory_name']); ?>" class="accessory-image" onerror="this.src='https://via.placeholder.com/60x60/ff6b35/ffffff?text=N/A'"></td>
                                <td><strong><?php echo htmlspecialchars($accessory['accessory_name']); ?></strong></td>
                                <td><span class="fw-bold text-warning"><?php echo number_format($accessory['price'], 0, '', ','); ?>đ</span></td>
                                <td><?php $stock = $accessory['stock']; $badge_class = 'stock-high'; $low_stock_tag = ''; if ($stock < 5) { $badge_class = 'stock-low'; $low_stock_tag = ' <small class="text-danger fw-bold">(Sắp hết!)</small>'; } elseif ($stock < 15) $badge_class = 'stock-medium'; ?><span class="stock-badge <?php echo $badge_class; ?>"><?php echo $stock; ?></span><?php echo $low_stock_tag; ?></td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($accessory['created_at'])); ?></small></td>
                                <td><div class="btn-group btn-group-sm"><a href="edit_accessory.php?id=<?php echo $accessory['accessory_id']; ?>" class="btn btn-warning" title="Chỉnh sửa"><i class="fas fa-edit"></i></a><button type="button" class="btn btn-danger" onclick="deleteAccessory(<?php echo $accessory['accessory_id']; ?>, '<?php echo htmlspecialchars($accessory['accessory_name']); ?>')" title="Xóa"><i class="fas fa-trash"></i></button></div></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Phụ kiện pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><i class="fas fa-chevron-left"></i> Trước</a></li><?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?><li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                        <?php if ($page < $total_pages): ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">Sau <i class="fas fa-chevron-right"></i></a></li><?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5"><i class="fas fa-inbox fa-3x mb-3" style="color: #6c757d;"></i><h5 style="color: #333;">Không có phụ kiện nào</h5><p style="color: #6c757d; "><?php if (!empty($search)): ?>Không tìm thấy phụ kiện nào với từ khóa "<?php echo htmlspecialchars($search); ?>"<?php else: ?>Chưa có phụ kiện nào trong hệ thống<?php endif; ?></p><a href="add_accessory.php" class="add-btn"><i class="fas fa-plus"></i> Thêm phụ kiện đầu tiên</a></div>
            <?php endif; ?>
            </div>
        </div>
        <?php 
        if (file_exists(__DIR__.'/../dashboard/footer.php')) {
            include __DIR__.'/../dashboard/footer.php'; 
        }
        ?>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Xác Nhận Xóa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Bạn có chắc chắn muốn xóa phụ kiện <strong id="accessoryName"></strong>?</p><p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><form method="POST" style="display: inline;"><input type="hidden" name="accessory_id" id="deleteAccessoryId"><button type="submit" name="delete" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form></div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    <script>
        function deleteAccessory(id, name) {
            document.getElementById('deleteAccessoryId').value = id;
            document.getElementById('accessoryName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
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
<?php $stmt->close(); $count_stmt->close(); $conn->close(); ?> 