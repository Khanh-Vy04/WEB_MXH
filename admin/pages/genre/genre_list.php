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
$sql = "SELECT * FROM genres $where_clause ORDER BY genre_id DESC LIMIT ? OFFSET ?";
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
    <title>Genres List</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .genres-container {
            background: #191C24;
            border-radius: 10px;
            padding: 25px;
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
            color: #fff;
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
            background: #2C3E50;
            border: 1px solid #34495E;
            border-radius: 8px;
            padding: 10px 15px;
            color: #fff;
            width: 300px;
            max-width: 100%;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #3498DB;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .entries-select {
            background: #2C3E50;
            border: 1px solid #34495E;
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        .btn-add {
            background: #27AE60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-add:hover {
            background: #229954;
            color: white;
            text-decoration: none;
        }
        
        .genres-table {
            width: 100%;
            background: #2C3E50;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .genres-table th {
            background: #34495E;
            color: #BDC3C7;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .genres-table td {
            padding: 15px;
            color: #ECF0F1;
            border-bottom: 1px solid #34495E;
        }
        
        .genres-table tr:last-child td {
            border-bottom: none;
        }
        
        .genres-table tr:hover {
            background: #34495E;
        }
        
        .genre-name {
            font-weight: 600;
            color: #3498DB;
            font-size: 1.1rem;
        }
        
        .genre-description {
            color: #BDC3C7;
            line-height: 1.5;
            max-width: 400px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit {
            background: #F39C12;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: #E67E22;
            color: white;
            text-decoration: none;
        }
        
        .btn-delete {
            background: #E74C3C;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-delete:hover {
            background: #C0392B;
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
            color: #BDC3C7;
            font-size: 0.9rem;
        }
        
        .pagination-controls {
            display: flex;
            gap: 5px;
        }
        
        .page-btn {
            background: #2C3E50;
            color: #BDC3C7;
            padding: 8px 12px;
            border: 1px solid #34495E;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .page-btn:hover, .page-btn.active {
            background: #3498DB;
            color: white;
            text-decoration: none;
            border-color: #3498DB;
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
            color: #BDC3C7;
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
                                    <i class="fas fa-music me-3"></i>Quản Lý Dòng Nhạc
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
                                    
                                    <a href="add_genre.php" class="btn-add">
                                        <i class="fas fa-plus me-2"></i>Thêm Dòng Nhạc
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <?php if (count($genres) > 0): ?>
                            <table class="genres-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th style="width: 200px;">Tên Dòng Nhạc</th>
                                        <th>Mô Tả</th>
                                        <th style="width: 150px;">Hành Động</th>
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
                                                    <i class="fas fa-edit me-1"></i>Sửa
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dòng nhạc này?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="genre_id" value="<?php echo $genre['genre_id']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="fas fa-trash me-1"></i>Xóa
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