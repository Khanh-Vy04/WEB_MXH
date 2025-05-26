<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'product';

// Debug đường dẫn hiện tại
echo "<script>console.log('Current directory: " . addslashes(__DIR__) . "');</script>";
echo "<script>console.log('Looking for database config at: " . addslashes(__DIR__ . '/../../../../config/database.php') . "');</script>";

// Kết nối database
$config_path = '../../../../config/database.php';
if (!file_exists($config_path)) {
    echo "<script>console.error('Database config file not found at: " . addslashes($config_path) . "');</script>";
    // Thử đường dẫn khác
    $config_path = '../../../config/database.php';
    if (!file_exists($config_path)) {
        echo "<script>console.error('Database config file not found at: " . addslashes($config_path) . "');</script>";
        die("Cannot find database config file");
    }
}

try {
    require_once $config_path;
    echo "<script>console.log('Database connection successful');</script>";
} catch (Exception $e) {
    echo "<script>console.error('Database connection failed: " . addslashes($e->getMessage()) . "');</script>";
    die("Database connection failed: " . $e->getMessage());
}

// Lấy danh sách genres cho filter
$genres_sql = "SELECT DISTINCT genre_name FROM genres ORDER BY genre_name";
$genres_result = $conn->query($genres_sql);
$available_genres = [];
if ($genres_result->num_rows > 0) {
    while($row = $genres_result->fetch_assoc()) {
        $available_genres[] = $row['genre_name'];
    }
}

// Lấy dữ liệu sản phẩm từ database với thông tin nghệ sĩ
// Trước tiên kiểm tra cấu trúc bảng products
$check_sql = "DESCRIBE products";
$check_result = $conn->query($check_sql);
$columns = [];
if ($check_result) {
    while($col = $check_result->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
}
echo "<script>console.log('Available columns in products table: " . implode(', ', $columns) . "');</script>";

// Xây dựng query dựa trên cột có sẵn
$order_by = "p.product_id DESC"; // Mặc định sắp xếp theo ID
if (in_array('created_at', $columns)) {
    $order_by = "p.created_at DESC";
} elseif (in_array('created_a', $columns)) {
    $order_by = "p.created_a DESC";
}

$sql = "SELECT p.*, a.artist_name, g.genre_name 
        FROM products p 
        LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
        LEFT JOIN artists a ON ap.artist_id = a.artist_id 
        LEFT JOIN genres g ON p.genre_id = g.genre_id
        ORDER BY $order_by";

echo "<script>console.log('SQL Query: " . addslashes($sql) . "');</script>";

$result = $conn->query($sql);

if (!$result) {
    echo "<script>console.error('SQL Error: " . addslashes($conn->error) . "');</script>";
    die("SQL Error: " . $conn->error);
}

echo "<script>console.log('Query executed successfully. Rows found: " . $result->num_rows . "');</script>";

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Xử lý created_at dựa trên cột có sẵn
        $created_at = '';
        if (isset($row['created_at'])) {
            $created_at = $row['created_at'];
        } elseif (isset($row['created_a'])) {
            $created_at = $row['created_a'];
        } else {
            $created_at = date('Y-m-d H:i:s'); // Giá trị mặc định
        }
        
        $products[] = [
            'product_id' => $row['product_id'],
            'name' => $row['product_name'],
            'desc' => $row['description'],
            'category' => $row['genre_name'] ?? 'Chưa phân loại',
            'artist' => $row['artist_name'] ?? 'Unknown Artist',
            'stock' => $row['stock'] > 0,
            'price' => $row['price'],
            'qty' => $row['stock'],
            'status' => $row['stock'] > 0 ? 'active' : 'inactive',
            'image_url' => $row['image_url'],
            'created_at' => $created_at
        ];
    }
    echo "<script>console.log('Products loaded: " . count($products) . "');</script>";
} else {
    echo "<script>console.log('No products found in database');</script>";
}

// Xử lý filter
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$stock = isset($_GET['stock']) ? $_GET['stock'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$filteredProducts = array_filter($products, function($product) use ($status, $category, $stock, $search) {
    $ok = true;
    if ($status && $product['status'] !== $status) $ok = false;
    if ($category && $product['category'] !== $category) $ok = false;
    if ($stock !== '' && $product['stock'] != (bool)$stock) $ok = false;
    if ($search && stripos($product['name'], $search) === false && stripos($product['desc'], $search) === false) $ok = false;
    return $ok;
});

// Phân trang
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [3, 10, 25, 50, 100]) ? $rowsPerPage : 10;
$totalRows = count($filteredProducts);
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$start = ($currentPageNumber - 1) * $rowsPerPage;
$end = min($start + $rowsPerPage, $totalRows);
$currentPageProducts = array_slice($filteredProducts, $start, $rowsPerPage);

function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Product List</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="product_list.css" />
    <script>
        console.log('HTML head loaded');
        console.log('Total products to display: <?php echo count($currentPageProducts); ?>');
    </script>
</head>
<body>
<script>console.log('Body started loading');</script>
<div class="container-fluid position-relative d-flex p-0">
    <?php 
    echo "<script>console.log('Loading sidebar...');</script>";
    if (file_exists(__DIR__.'/../../dashboard/sidebar.php')) {
        include __DIR__.'/../../dashboard/sidebar.php'; 
        echo "<script>console.log('Sidebar loaded successfully');</script>";
    } else {
        echo "<script>console.error('Sidebar file not found');</script>";
        echo "<div>Sidebar not found</div>";
    }
    ?>
    <div class="content">
        <?php 
        echo "<script>console.log('Loading navbar...');</script>";
        if (file_exists(__DIR__.'/../../dashboard/navbar.php')) {
            include __DIR__.'/../../dashboard/navbar.php'; 
            echo "<script>console.log('Navbar loaded successfully');</script>";
        } else {
            echo "<script>console.error('Navbar file not found');</script>";
            echo "<div>Navbar not found</div>";
        }
        ?>
        <div class="container-fluid pt-4 px-4">
            <script>console.log('Main content area started');</script>
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-label">Filter</div>
                <form method="GET" class="filter-bar-row">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        <option value="active" <?php if($status=='active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if($status=='inactive') echo 'selected'; ?>>Inactive</option>
                        <option value="scheduled" <?php if($status=='scheduled') echo 'selected'; ?>>Scheduled</option>
                    </select>
                    <select name="category" class="form-select">
                        <option value="">Genre</option>
                        <?php foreach ($available_genres as $genre): ?>
                        <option value="<?php echo htmlspecialchars($genre); ?>" <?php if($category == $genre) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($genre); ?>
                        </option>
                        <?php endforeach; ?>
                        <option value="Chưa phân loại" <?php if($category == 'Chưa phân loại') echo 'selected'; ?>>Chưa phân loại</option>
                    </select>
                    <select name="stock" class="form-select">
                        <option value="">Stock</option>
                        <option value="1" <?php if($stock==='1') echo 'selected'; ?>>In Stock</option>
                        <option value="0" <?php if($stock==='0') echo 'selected'; ?>>Out of Stock</option>
                    </select>
                </form>
            </div>
            <!-- Product Action Bar -->
            <div class="product-action-bar">
                <form method="GET" class="search-bar" style="max-width: 400px;">
                    <input type="text" name="search" placeholder="Search Product" value="<?php echo htmlspecialchars($search); ?>" />
                </form>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <form method="GET" class="entries-dropdown" style="margin:0;">
                        <select name="entries" onchange="this.form.submit()">
                            <option value="3" <?php echo $rowsPerPage == 3 ? 'selected' : ''; ?>>3</option>
                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </form>
                    <a href="/WEB_MXH/admin/pages/product/add_product/add_product.php" class="add-btn"><i class="fas fa-plus"></i> Add Product</a>
                </div>
            </div>
            <!-- Product Table -->
            <div class="table-container">
                <script>console.log('Rendering product table...');</script>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="select-all" /></th>
                            <th>PRODUCT</th>
                            <th>ARTIST</th>
                            <th>GENRE</th>
                            <th>STOCK</th>
                            <th>PRICE</th>
                            <th>QTY</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        echo "<script>console.log('Rendering " . count($currentPageProducts) . " products');</script>";
                        foreach ($currentPageProducts as $index => $product): 
                            echo "<script>console.log('Rendering product " . ($index + 1) . ": " . addslashes($product['name']) . "');</script>";
                        ?>
                        <tr>
                            <td><input type="checkbox" name="selected_products[]" value="<?php echo $product['product_id']; ?>" /></td>
                            <td class="product-cell">
                                <img src="<?php echo $product['image_url']; ?>" class="product-img-thumb" alt="product" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <div style="font-weight:600; color:#222;"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div style="font-size:0.9em;color:#888;"><?php echo htmlspecialchars(substr($product['desc'], 0, 50)) . '...'; ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['artist']); ?></td>
                            <td><?php echo $product['category']; ?></td>
                            <td>
                                <?php if($product['stock']): ?>
                                    <span class="badge-stock-in">In Stock</span>
                                <?php else: ?>
                                    <span class="badge-stock-out">Out</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($product['price'],2); ?>$</td>
                            <td><?php echo $product['qty']; ?></td>
                            <td>
                                <?php if($product['status']=='active'): ?>
                                    <span class="badge-status-active">Active</span>
                                <?php elseif($product['status']=='inactive'): ?>
                                    <span class="badge-status-inactive">Inactive</span>
                                <?php else: ?>
                                    <span class="badge-status-scheduled">Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-dropdown">
                                    <button type="button" class="action-btn"><i class="fas fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="view_product.php?id=<?php echo $product['product_id']; ?>" class="dropdown-item"><i class="fas fa-eye"></i> View</a>
                                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="dropdown-item"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="dropdown-item text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <script>console.log('Product table rendered successfully');</script>
            </div>
            <!-- Pagination -->
            <div class="pagination-bar">
                <span class="entries-info">Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $totalRows; ?> entries</span>
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
        if (file_exists(__DIR__.'/../../dashboard/footer.php')) {
            include __DIR__.'/../../dashboard/footer.php'; 
            echo "<script>console.log('Footer loaded successfully');</script>";
        } else {
            echo "<script>console.error('Footer file not found');</script>";
        }
        ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
<script src="product_list.js"></script>
<script>console.log('All scripts loaded, page should be ready');</script>
</body>
</html>

