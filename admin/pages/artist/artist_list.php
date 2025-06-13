<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'artist';

// Kết nối database
$config_path = '../../../config/database.php';
if (!file_exists($config_path)) {
    echo "<script>console.error('Database config file not found at: " . addslashes($config_path) . "');</script>";
    die("Cannot find database config file");
}

try {
    require_once $config_path;
    echo "<script>console.log('Database connection successful');</script>";
} catch (Exception $e) {
    echo "<script>console.error('Database connection failed: " . addslashes($e->getMessage()) . "');</script>";
    die("Database connection failed: " . $e->getMessage());
}

// Xử lý thay đổi status nếu có request
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action == 'toggle_status') {
        // Lấy status hiện tại
        $sql = "SELECT status FROM artists WHERE artist_id = $id";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $newStatus = $row['status'] == 1 ? 0 : 1;
            $sql = "UPDATE artists SET status = $newStatus WHERE artist_id = $id";
            if ($conn->query($sql)) {
                $statusText = $newStatus == 1 ? 'kích hoạt' : 'vô hiệu hóa';
                echo "<script>alert('Nghệ sĩ đã được $statusText thành công!'); window.location.href = 'artist_list.php';</script>";
            }
        }
    }
}

// Lấy dữ liệu nghệ sĩ từ database với số lượng sản phẩm
$sql = "SELECT a.*, COUNT(ap.product_id) as product_count 
        FROM artists a 
        LEFT JOIN artist_products ap ON a.artist_id = ap.artist_id 
        GROUP BY a.artist_id 
        ORDER BY a.artist_name ASC";

echo "<script>console.log('SQL Query: " . addslashes($sql) . "');</script>";

$result = $conn->query($sql);

if (!$result) {
    echo "<script>console.error('SQL Error: " . addslashes($conn->error) . "');</script>";
    die("SQL Error: " . $conn->error);
}

echo "<script>console.log('Query executed successfully. Rows found: " . $result->num_rows . "');</script>";

$artists = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $artists[] = [
            'artist_id' => $row['artist_id'],
            'artist_name' => $row['artist_name'],
            'bio' => $row['bio'],
            'image_url' => $row['image_url'],
            'product_count' => $row['product_count'],
            'status' => $row['status'] == 1 ? 'active' : 'inactive',
            'status_value' => $row['status']
        ];
    }
    echo "<script>console.log('Artists loaded: " . count($artists) . "');</script>";
} else {
    echo "<script>console.log('No artists found in database');</script>";
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Lọc dữ liệu
$filteredArtists = array_filter($artists, function($artist) use ($status, $search) {
    $ok = true;
    if ($status && $artist['status'] !== $status) $ok = false;
    if ($search && stripos($artist['artist_name'], $search) === false && stripos($artist['bio'], $search) === false) $ok = false;
    return $ok;
});

// Phân trang
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [3, 10, 25, 50, 100]) ? $rowsPerPage : 10;
$totalRows = count($filteredArtists);
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$start = ($currentPageNumber - 1) * $rowsPerPage;
$end = min($start + $rowsPerPage, $totalRows);
$currentPageArtists = array_slice($filteredArtists, $start, $rowsPerPage);

function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Nghệ Sĩ - AuraDisc</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="artist_list.css" />
    <script>
        console.log('HTML head loaded');
        console.log('Total artists to display: <?php echo count($currentPageArtists); ?>');
    </script>
</head>
<body>
<script>console.log('Body started loading');</script>
<div class="container-fluid position-relative d-flex p-0">
    <?php 
    echo "<script>console.log('Loading sidebar...');</script>";
    if (file_exists(__DIR__.'/../dashboard/sidebar.php')) {
        include __DIR__.'/../dashboard/sidebar.php'; 
        echo "<script>console.log('Sidebar loaded successfully');</script>";
    } else {
        echo "<script>console.error('Sidebar file not found');</script>";
        echo "<div>Sidebar not found</div>";
    }
    ?>
    <div class="content">
        <?php 
        echo "<script>console.log('Loading navbar...');</script>";
        if (file_exists(__DIR__.'/../dashboard/navbar.php')) {
            include __DIR__.'/../dashboard/navbar.php'; 
            echo "<script>console.log('Navbar loaded successfully');</script>";
        } else {
            echo "<script>console.error('Navbar file not found');</script>";
            echo "<div>Navbar not found</div>";
        }
        ?>
        <div class="container-fluid pt-4 px-4">
            <script>console.log('Main content area started');</script>
            
            <!-- Header Section -->
            <div class="header-section" style="background: #fff; border-radius: 18px; padding: 1.1rem 1.5rem 1.2rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1.5px 4px rgba(0, 0, 0, 0.04);">
                <h2 style="color: #222; font-size: 1.5rem; font-weight: 600; margin-bottom: 0.7rem; margin-left: 0.1rem;">
                    <i class="fas fa-user-music me-2"></i>Quản lý Nghệ Sĩ
                </h2>
                <p style="color: #444; margin-bottom: 0;">Quản lý thông tin nghệ sĩ và ca sĩ</p>
            </div>

            <!-- Statistics Section -->
            <div class="row g-4 mb-4">
                <?php
                $stats_sql = "SELECT COUNT(*) as total_artists, COUNT(CASE WHEN status = 1 THEN 1 END) as active_artists, COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_artists, COUNT(DISTINCT ap.product_id) as total_products FROM artists a LEFT JOIN artist_products ap ON a.artist_id = ap.artist_id";
                $stats_result = $conn->query($stats_sql);
                $stats = $stats_result->fetch_assoc();
                ?>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card" style="background: #fff; border: none; border-radius: 16px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; text-align: center; box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); height: 120px; display: flex; flex-direction: column; justify-content: center; transition: transform 0.3s;">
                        <div class="stat-number" style="font-size: 1.8rem; font-weight: bold; color: #222; margin-bottom: 5px;">
                            <?php echo number_format($stats['total_artists']); ?>
                        </div>
                        <div class="stat-label" style="color: #444; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">
                            Tổng Nghệ sĩ
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card" style="background: #fff; border: none; border-radius: 16px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; text-align: center; box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); height: 120px; display: flex; flex-direction: column; justify-content: center; transition: transform 0.3s;">
                        <div class="stat-number" style="font-size: 1.8rem; font-weight: bold; color: #16a34a; margin-bottom: 5px;">
                            <?php echo number_format($stats['active_artists']); ?>
                        </div>
                        <div class="stat-label" style="color: #444; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">
                            Đang hoạt động
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card" style="background: #fff; border: none; border-radius: 16px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; text-align: center; box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); height: 120px; display: flex; flex-direction: column; justify-content: center; transition: transform 0.3s;">
                        <div class="stat-number" style="font-size: 1.8rem; font-weight: bold; color: #dc2626; margin-bottom: 5px;">
                            <?php echo number_format($stats['inactive_artists']); ?>
                        </div>
                        <div class="stat-label" style="color: #444; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">
                            Ngừng hoạt động
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card" style="background: #fff; border: none; border-radius: 16px; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem; text-align: center; box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08); height: 120px; display: flex; flex-direction: column; justify-content: center; transition: transform 0.3s;">
                        <div class="stat-number" style="font-size: 1.8rem; font-weight: bold; color: #2563eb; margin-bottom: 5px;">
                            <?php echo number_format($stats['total_products']); ?>
                        </div>
                        <div class="stat-label" style="color: #444; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">
                            Tổng sản phẩm
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-section" style="background: #fff; border-radius: 16px; padding: 1.2rem 1.5rem; box-shadow: 0 1px 6px rgba(0, 0, 0, 0.12), 0 1.5px 8px rgba(0, 0, 0, 0.08);">
                <div class="search-bar" style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <form method="GET" class="d-flex flex-grow-1">
                        <input type="text" class="form-control" placeholder="Tìm kiếm nghệ sĩ..." name="search" value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 0.6rem 1rem; border: 2px solid #222; border-radius: 10px; font-size: 1rem; color: #444; background: #fff; transition: border-color 0.2s;">
                    </form>
                    <a href="add_artist.php" class="add-btn" style="background: #deccca; color: #412d3b; border: none; border-radius: 10px; padding: 0.5rem 1.2rem; font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s ease; text-decoration: none;">
                        <i class="fas fa-plus" style="color: #412d3b; font-size: 1.1rem;"></i> Thêm Nghệ Sĩ
                    </a>
                </div>
                <script>console.log('Rendering artist table...');</script>
                <table>
                    <thead>
                        <tr>
                            <th>NGHỆ SĨ</th>
                            <th>TIỂU SỬ</th>
                            <th>SỐ SẢN PHẨM</th>
                            <th>TRẠNG THÁI</th>
                            <th>THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        echo "<script>console.log('Rendering " . count($currentPageArtists) . " artists');</script>";
                        foreach ($currentPageArtists as $index => $artist): 
                            echo "<script>console.log('Rendering artist " . ($index + 1) . ": " . addslashes($artist['artist_name']) . "');</script>";
                        ?>
                        <tr>
                            <td class="artist-cell">
                                <img src="<?php echo $artist['image_url']; ?>" class="artist-img-thumb" alt="artist" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <div style="font-weight:600; color:#222;"><?php echo htmlspecialchars($artist['artist_name']); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($artist['bio'], 0, 100)) . (strlen($artist['bio']) > 100 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge-product-count">
                                    <?php echo $artist['product_count']; ?> Sản phẩm
                                </span>
                            </td>
                            <td>
                                <?php if($artist['status'] == 'active'): ?>
                                    <span class="badge-status-active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge-status-inactive">Ngừng hoạt động</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="edit_artist.php?id=<?php echo $artist['artist_id']; ?>" class="dropdown-item"><i class="fas fa-edit"></i> Chỉnh sửa</a>
                                        <a href="?action=toggle_status&id=<?php echo $artist['artist_id']; ?>" class="dropdown-item <?php echo $artist['status'] == 'active' ? 'text-warning' : 'text-success'; ?>">
                                            <i class="fas fa-<?php echo $artist['status'] == 'active' ? 'pause' : 'play'; ?>"></i> 
                                            <?php echo $artist['status'] == 'active' ? 'Vô Hiệu Hóa' : 'Kích Hoạt'; ?>
                                        </a>
                                        <a href="delete_artist.php?id=<?php echo $artist['artist_id']; ?>" class="dropdown-item text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa nghệ sĩ này?')"><i class="fas fa-trash-alt"></i> Xóa</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <script>console.log('Artist table rendered successfully');</script>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-bar">
                <span class="entries-info">Hiển thị <?php echo $start + 1; ?> đến <?php echo $end; ?> trong tổng số <?php echo $totalRows; ?> nghệ sĩ</span>
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
        <?php 
        echo "<script>console.log('Loading footer...');</script>";
        if (file_exists(__DIR__.'/../dashboard/footer.php')) {
            include __DIR__.'/../dashboard/footer.php'; 
            echo "<script>console.log('Footer loaded successfully');</script>";
        } else {
            echo "<script>console.error('Footer file not found');</script>";
        }
        ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
<script>
// Action dropdown functionality - Exactly like product table
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý click cho dropdown
    document.addEventListener('click', function(e) {
        // Đóng tất cả dropdown trước
        const allDropdowns = document.querySelectorAll('.dropdown-menu');
        allDropdowns.forEach(dropdown => {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.transform = 'translateY(-10px)';
        });
        
        // Kiểm tra nếu click vào action button
        const actionBtn = e.target.closest('.action-btn');
        if (actionBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = actionBtn.nextElementSibling;
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                dropdown.style.display = 'block';
                setTimeout(() => {
                    dropdown.style.opacity = '1';
                    dropdown.style.transform = 'translateY(0)';
                }, 10);
            }
        }
    });
    
    // Đóng dropdown khi click vào dropdown item  
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const dropdown = this.closest('.dropdown-menu');
            if (dropdown && !this.onclick) { // Không đóng nếu có confirm
                dropdown.style.display = 'none';
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-10px)';
            }
        });
    });
});

console.log('Artist list page loaded successfully');
</script>
</body>
</html> 