<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'genre';

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
                $genre_id = (int)$_POST['genre_id'];
                if ($genre_id > 0) {
                    $delete_sql = "DELETE FROM genres WHERE genre_id = ?";
                    $stmt = $conn->prepare($delete_sql);
                    $stmt->bind_param("i", $genre_id);
                    
                    if ($stmt->execute()) {
                        $message = "Xóa dòng nhạc thành công!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi xóa dòng nhạc: " . $conn->error;
                        $messageType = "error";
                    }
                }
                break;
        }
    }
}

// Lấy dữ liệu genres từ database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [5, 10, 25, 50, 100]) ? $rowsPerPage : 10;

// Xây dựng query với search
$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = "WHERE genre_name LIKE ? OR description LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = "ss";
}

// Đếm tổng số records
$count_sql = "SELECT COUNT(*) as total FROM genres $where_clause";
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
$sql = "SELECT * FROM genres $where_clause ORDER BY genre_id ASC LIMIT ? OFFSET ?";
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
$genres = $result->fetch_all(MYSQLI_ASSOC);

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
    <title>Quản Lý Dòng Nhạc</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .genres-container {
            background: #F5F5F5;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid #E0E0E0;
        }
        
        .header-section {
            display: flex;
            justify-content: between;
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
        
        .genres-table {
            width: 100%;
            max-width: 1200px;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #E0E0E0;
            margin: 0 auto;
            table-layout: fixed;
        }
        
        .genres-table th {
            background: #F8F9FA;
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 2px solid #412d3b;
        }
        
        .genres-table th:nth-child(3) {
            text-align: left;
            padding-left: 150px;
        }
        
        .genres-table th:nth-child(4) {
            text-align: left;
            padding-left: 25px;
        }
        
        .genres-table td:nth-child(3) {
            text-align: justify;
        }
        
        .genres-table td:nth-child(4) {
            text-align: left;
            padding-left: 25px;
        }
        
        .genres-table td {
            padding: 15px;
            color: #333;
            border-bottom: 1px solid #E0E0E0;
            background: #FFFFFF;
        }
        
        .genres-table tr:last-child td {
            border-bottom: none;
        }
        
        .genres-table tr:hover {
            background: #F8F9FA;
        }
        
        .genre-name {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }
        
        .genre-description {
            color: #666;
            line-height: 1.5;
            max-width: 400px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit {
            background: #deccca !important;
            border-color: #deccca !important;
            color: #412d3b !important;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: #c9b5b0 !important;
            border-color: #c9b5b0 !important;
            color: #412d3b !important;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(65, 45, 59, 0.15);
        }
        
        .btn-delete {
            background: rgb(255, 87, 87) !important;
            border-color: rgb(255, 172, 172) !important;
            color: #fff !important;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-delete:hover {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.2);
        }
        
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .entries-info {
            color: #666;
            font-size: 0.9rem;
        }
        
        .pagination-controls {
            display: flex;
            gap: 5px;
        }
        
        .page-btn {
            background: #FFFFFF;
            color: #333;
            padding: 8px 12px;
            border: 1px solid #DDD;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .page-btn:hover, .page-btn.active {
            background: #deccca;
            color: #412d3b;
            text-decoration: none;
            border-color: #deccca;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
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
            
            .genres-table {
                font-size: 0.9rem;
            }
            
            .genres-table th, .genres-table td {
                padding: 10px;
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
                        <div class="genres-container">
                            <!-- Header -->
                            <div class="header-section">
                                <h2 class="page-title">
                                    <i class="fas fa-music me-3"></i>Quản lý dòng nhạc
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
                                           placeholder="Tìm kiếm dòng nhạc..." 
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
                                    
                                    <a href="/WEB_MXH/admin/pages/genre/add_genre.php" class="btn-add">
                                        <i class="fas fa-plus me-2"></i>Thêm dòng nhạc
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <?php if (count($genres) > 0): ?>
                            <table class="genres-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th style="width: 200px;">Dòng nhạc</th>
                                        <th style="width: 400px;">Mô tả</th>
                                        <th style="width: 120px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($genres as $genre): ?>
                                    <tr>
                                        <td><?php echo $genre['genre_id']; ?></td>
                                        <td>
                                            <div class="genre-name"><?php echo htmlspecialchars($genre['genre_name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="genre-description">
                                                <?php echo htmlspecialchars($genre['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_genre.php?id=<?php echo $genre['genre_id']; ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dòng nhạc này?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="genre_id" value="<?php echo $genre['genre_id']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="fas fa-music fa-3x mb-3"></i>
                                <p>Không tìm thấy dòng nhạc nào.</p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                            <div class="pagination-section">
                                <div class="entries-info">
                                    Hiển thị <?php echo $start + 1; ?> đến <?php echo $end; ?> trong tổng số <?php echo $totalRows; ?> dòng nhạc
                                </div>
                                
                                <div class="pagination-controls">
                                    <?php if ($currentPageNumber > 1): ?>
                                    <a href="<?php echo getUrlWithParams(['page' => $currentPageNumber - 1]); ?>" class="page-btn">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="<?php echo getUrlWithParams(['page' => $i]); ?>" 
                                       class="page-btn <?php echo $i === $currentPageNumber ? 'active' : ''; ?>">
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