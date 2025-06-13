<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'banner';

// Kết nối database
$config_path = '../../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file at: " . $config_path);
}

require_once $config_path;

$message = '';
$messageType = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $banner_id = (int)$_POST['banner_id'];
                if ($banner_id > 0) {
                    $delete_sql = "DELETE FROM banners WHERE banner_id = ?";
                    $stmt = $conn->prepare($delete_sql);
                    $stmt->bind_param("i", $banner_id);
                    
                    if ($stmt->execute()) {
                        // Redirect to avoid form resubmission
                        $redirect_url = $_SERVER['PHP_SELF'];
                        if (!empty($_GET)) {
                            $redirect_url .= '?' . http_build_query($_GET);
                        }
                        header("Location: $redirect_url");
                        exit();
                    } else {
                        $message = "Lỗi khi xóa banner: " . $conn->error;
                        $messageType = "error";
                    }
                }
                break;
                
            case 'toggle_status':
                $banner_id = (int)$_POST['banner_id'];
                if ($banner_id > 0) {
                    // Lấy status hiện tại
                    $sql = "SELECT is_active FROM banners WHERE banner_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $banner_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        $newStatus = $row['is_active'] == 1 ? 0 : 1;
                        $update_sql = "UPDATE banners SET is_active = ? WHERE banner_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ii", $newStatus, $banner_id);
                        
                        if ($update_stmt->execute()) {
                            // Redirect to avoid form resubmission
                            $redirect_url = $_SERVER['PHP_SELF'];
                            if (!empty($_GET)) {
                                $redirect_url .= '?' . http_build_query($_GET);
                            }
                            header("Location: $redirect_url");
                            exit();
                        } else {
                            $message = "Lỗi khi cập nhật trạng thái: " . $conn->error;
                            $messageType = "error";
                        }
                    }
                }
                break;
        }
    }
}

// Lấy dữ liệu banners từ database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [5, 10, 25, 50, 100]) ? $rowsPerPage : 10;

// Xây dựng query với search
$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = "WHERE banner_title LIKE ? OR banner_description LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = "ss";
}

// Đếm tổng số records
$count_sql = "SELECT COUNT(*) as total FROM banners $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
} else {
    $count_result = $conn->query($count_sql);
}
$totalRows = $count_result->fetch_assoc()['total'];

// Tính phân trang
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$offset = ($currentPageNumber - 1) * $rowsPerPage;

// Lấy dữ liệu với phân trang
$sql = "SELECT * FROM banners $where_clause ORDER BY display_order ASC, banner_id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $params[] = $rowsPerPage;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $rowsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$banners = $result->fetch_all(MYSQLI_ASSOC);

// Function để tạo URL với params
function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}

$start = $offset;
$end = min($start + $rowsPerPage, $totalRows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quản Lý Banner</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
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
        
        .banner-container {
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
        
        .banner-table {
            width: 100%;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #E0E0E0;
        }
        
        .banner-table th {
            background: #F8F9FA;
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 2px solid #412d3b;
            white-space: nowrap;
        }
        
        .banner-table td {
            padding: 15px;
            color: #333;
            border-bottom: 1px solid #E0E0E0;
            vertical-align: middle;
            background: #FFFFFF;
        }
        
        .banner-table tr:last-child td {
            border-bottom: none;
        }
        
        .banner-table tr:hover {
            background: #F8F9FA;
        }
        
        .banner-preview {
            width: 120px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #DDD;
        }
        
        .banner-title {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .banner-description {
            color: #BDC3C7;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .bg-success {
            background-color:rgb(171, 208, 180) !important;
            color: white;
        }
        
        .bg-danger {
            background-color:rgb(225, 179, 184) !important;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 12px;
            border: 1px solid;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background-color: rgb(182, 195, 208);
            border-color: rgb(182, 195, 208);
            color: white;
        }
        
        .btn-primary:hover {
            background-color:rgb(182, 195, 208);
            border-color: #rgb(182, 195, 208);
            color: white;
            text-decoration: none;
        }
        
        .btn-warning {
            background-color: #deccca;
            border-color:  #deccca;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #deccca;
            border-color:  #deccca;
            color: #212529;
        }
        
        .btn-success {
            background-color:rgb(168, 193, 174);
            border-color:rgb(167, 214, 178);
            color: white;
        }
        
        .btn-success:hover {
            background-color:rgb(168, 193, 174);
            border-color:rgb(167, 214, 178);
            color: white;
        }
        
        .btn-danger {
            background-color: #412d3b;
            border-color:  #412d3b;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #412d3b;
            border-color: #412d3b;
            color: white;
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
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #deccca;
            color: #412d3b;
            border: 1px solid #deccca ;
        }
        
        .alert-error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
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
            
            .banner-table {
                font-size: 0.9rem;
            }
            
            .banner-table th, .banner-table td {
                padding: 10px;
            }
            
            .banner-preview {
                width: 80px;
                height: 40px;
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
                <div class="row g-4">
                    <div class="col-12">
                        <div class="banner-container">
                            <!-- Header -->
                            <div class="header-section">
                                <h2 class="page-title">
                                    <i class="fas fa-images me-3"></i>Quản Lý Banner
                                </h2>
                            </div>
                            
                            <!-- Alert Messages -->
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $messageType; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Controls -->
                            <div class="controls-section">
                                <form method="GET" style="margin: 0;">
                                    <input type="text" 
                                           name="search" 
                                           class="search-box"
                                           placeholder="Tìm kiếm banner..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </form>
                                
                                <div class="btn-group">
                                    <form method="GET" style="margin: 0;">
                                        <select name="entries" class="entries-select" onchange="this.form.submit()">
                                            <option value="5" <?php echo $rowsPerPage == 5 ? 'selected' : ''; ?>>5</option>
                                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                            <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                                        </select>
                                    </form>
                                    
                                    <a href="add_banner.php" class="btn-add">
                                        <i class="fas fa-plus me-2"></i>Thêm Banner Mới
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <?php if (count($banners) > 0): ?>
                            <table class="banner-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th style="width: 150px;">Hình Ảnh</th> 
                                        <th style="width: 300px;">Thông Tin</th>
                                        <th style="width: 120px; text-align: left; padding-left: 20px;">Thứ Tự Hiển Thị</th>
                                        <th style="width: 100px;">Trạng Thái</th>
                                        <th style="width: 200px;">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td><?php echo $banner['banner_id']; ?></td>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($banner['banner_url']); ?>" 
                                                 class="banner-preview" 
                                                 alt="Banner Preview"
                                                 onerror="this.src='https://via.placeholder.com/120x60/34495E/BDC3C7?text=No+Image'">
                                        </td>
                                        <td>
                                            <div class="banner-title">
                                                <?php echo htmlspecialchars($banner['banner_title'] ?: 'Không có tiêu đề'); ?>
                                            </div>
                                        </td>
                                        <td style="text-align: left; padding-left: 20px;">
                                            <span style="font-weight: 600; color: #3498DB;">
                                                <?php echo $banner['display_order']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($banner['is_active']): ?>
                                                <span class="badge bg-success">Hoạt Động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tạm Dừng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_banner.php?id=<?php echo $banner['banner_id']; ?>" class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái banner này?')">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="banner_id" value="<?php echo $banner['banner_id']; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $banner['is_active'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $banner['is_active'] ? 'Tạm dừng' : 'Kích hoạt'; ?>">
                                                        <i class="fas <?php echo $banner['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa banner này?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="banner_id" value="<?php echo $banner['banner_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <!-- Pagination -->
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    Hiển thị <?php echo $start + 1; ?> đến <?php echo $end; ?> trong tổng số <?php echo $totalRows; ?> banner
                                </div>
                                
                                <?php if ($totalPages > 1): ?>
                                <div class="pagination">
                                    <?php if ($currentPageNumber > 1): ?>
                                        <a href="<?php echo getUrlWithParams(['page' => $currentPageNumber - 1]); ?>">‹ Trước</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $currentPageNumber - 2); $i <= min($totalPages, $currentPageNumber + 2); $i++): ?>
                                        <?php if ($i == $currentPageNumber): ?>
                                            <span class="current"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="<?php echo getUrlWithParams(['page' => $i]); ?>"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPageNumber < $totalPages): ?>
                                        <a href="<?php echo getUrlWithParams(['page' => $currentPageNumber + 1]); ?>">Sau ›</a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>Không tìm thấy banner nào.</p>
                                <a href="add_banner.php" class="btn-add" style="margin-top: 15px;">
                                    <i class="fas fa-plus me-2"></i>Thêm Banner Đầu Tiên
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
            if (file_exists(__DIR__.'/../dashboard/footer.php')) {
                include __DIR__.'/../dashboard/footer.php'; 
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>
</html> 