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
                $statusText = $newStatus == 1 ? 'activated' : 'deactivated';
                echo "<script>alert('Artist $statusText successfully!'); window.location.href = 'artist_list.php';</script>";
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
            'status' => $row['status'] == 1 ? 'active' : 'inactive', // Chuyển đổi từ 1/0 sang active/inactive
            'status_value' => $row['status'] // Giữ giá trị gốc để xử lý
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
$rowsPerPage = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalRows = count($filteredArtists);
$totalPages = ceil($totalRows / $rowsPerPage);
$start = ($page - 1) * $rowsPerPage;
$currentPageArtists = array_slice($filteredArtists, $start, $rowsPerPage);

function getUrlWithParams($params) {
    $current = $_GET;
    foreach ($params as $key => $value) {
        $current[$key] = $value;
    }
    return '?' . http_build_query($current);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Artist List</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="artist_list.css" />
    <style>
        .artist-title {
            color: #222 !important;
            font-size: 1.55rem !important;
            font-weight: bold !important;
            margin-bottom: 1.2rem !important;
            letter-spacing: 0.5px;
        }
        .artist-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .artist-action-bar .search-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0;
            flex-wrap: wrap;
        }
        .artist-action-bar .filter-btn,
        .artist-action-bar .add-btn {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600;
            padding: 8px 18px;
            font-size: 1rem;
            transition: background 0.2s, color 0.2s;
            box-shadow: none;
        }
        .artist-action-bar .filter-btn:hover,
        .artist-action-bar .add-btn:hover {
            background: #c9b5b0 !important;
            color: #412d3b !important;
        }
        @media (max-width: 900px) {
            .artist-action-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.7rem;
            }
            .artist-action-bar .search-form {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
        }
    </style>
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
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4 artist-title">Artist Management</h6>
                            
                            <!-- Artist Action Bar -->
                            <div class="artist-action-bar">
                                <form method="GET" class="search-form">
                                    <input type="text" name="search" placeholder="Search Artist" value="<?php echo htmlspecialchars($search); ?>" />
                                    <select name="status">
                                        <option value="">All Status</option>
                                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Filter</button>
                                </form>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="pagination-dropdown">
                                        <select class="pagination-select" onchange="window.location.href='<?php echo getUrlWithParams(['rows' => '']); ?>' + this.value">
                                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                        </select>
                                    </div>
                                    <a href="/WEB_MXH/admin/pages/artist/add_artist.php" class="add-btn"><i class="fas fa-plus"></i> Add New Artist</a>
                                </div>
                            </div>

                            <!-- Artist Table -->
                            <div class="table-responsive">
                                <script>console.log('Rendering artist table...');</script>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><input type="checkbox" id="select-all-artists"></th>
                                            <th scope="col">ARTIST</th>
                                            <th scope="col">PRODUCTS</th>
                                            <th scope="col">STATUS</th>
                                            <th scope="col">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        echo "<script>console.log('Rendering " . count($currentPageArtists) . " artists');</script>";
                                        foreach ($currentPageArtists as $index => $artist): 
                                            echo "<script>console.log('Rendering artist " . ($index + 1) . ": " . addslashes($artist['artist_name']) . "');</script>";
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" class="artist-checkbox" name="selected_artists[]" value="<?php echo $artist['artist_id']; ?>"></td>
                                            <td class="artist-cell">
                                                <img src="<?php echo $artist['image_url']; ?>" class="artist-img-thumb" alt="artist" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                                <div class="artist-info">
                                                    <div style="font-weight:600; color:#222;"><?php echo htmlspecialchars($artist['artist_name']); ?></div>
                                                    <div style="font-size:0.9em;color:#888;"><?php echo substr(htmlspecialchars($artist['bio']), 0, 100) . '...'; ?></div>
                                                </div>
                                            </td>
                                            <td><?php echo $artist['product_count']; ?> products</td>
                                            <td>
                                                <?php if($artist['status']=='active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="edit_artist.php?id=<?php echo $artist['artist_id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <a href="?action=toggle_status&id=<?php echo $artist['artist_id']; ?>" 
                                                       class="btn btn-sm <?php echo $artist['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                                       title="<?php echo $artist['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>"
                                                       onclick="return confirm('Bạn có chắc chắn muốn <?php echo $artist['status'] == 'active' ? 'vô hiệu hóa' : 'kích hoạt'; ?> nghệ sĩ này?')">
                                                        <i class="fas <?php echo $artist['status'] == 'active' ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                    </a>
                                                    <a href="delete_artist.php?id=<?php echo $artist['artist_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Bạn có chắc chắn muốn xóa nghệ sĩ này?')"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <script>console.log('Artist table rendered successfully');</script>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    Showing <?php echo $start + 1; ?> to <?php echo min($start + $rowsPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
                                </div>
                                <div class="pagination-controls">
                                    <div class="pagination-buttons">
                                        <?php if ($page > 1): ?>
                                            <a href="<?php echo getUrlWithParams(['page' => $page - 1]); ?>" class="btn btn-sm btn-outline-primary">Previous</a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <a href="<?php echo getUrlWithParams(['page' => $i]); ?>" 
                                               class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="<?php echo getUrlWithParams(['page' => $page + 1]); ?>" class="btn btn-sm btn-outline-primary">Next</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('select-all-artists');
        var checkboxes = document.querySelectorAll('.artist-checkbox');
        if(selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
    </script>
    <script>console.log('All scripts loaded, artist page should be ready');</script>
</body>
</html> 