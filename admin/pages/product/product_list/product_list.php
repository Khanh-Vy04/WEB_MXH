<?php
$currentPage = 'productlist';
// Dữ liệu mẫu sản phẩm đĩa than
$products = [
    [
        'name' => 'The Beatles - Abbey Road',
        'desc' => 'Vinyl, 180g, 2019 Remaster',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0001',
        'price' => 650000,
        'qty' => 12,
        'status' => 'active',
    ],
    [
        'name' => 'Pink Floyd - The Wall',
        'desc' => 'Vinyl, 2LP, Gatefold',
        'category' => 'Progressive Rock',
        'stock' => false,
        'sku' => 'VN-0002',
        'price' => 800000,
        'qty' => 5,
        'status' => 'inactive',
    ],
    [
        'name' => 'Miles Davis - Kind of Blue',
        'desc' => 'Vinyl, 180g, Jazz Classic',
        'category' => 'Jazz',
        'stock' => true,
        'sku' => 'VN-0003',
        'price' => 720000,
        'qty' => 8,
        'status' => 'active',
    ],
    [
        'name' => 'Nirvana - Nevermind',
        'desc' => 'Vinyl, 180g, 2011 Remaster',
        'category' => 'Grunge',
        'stock' => true,
        'sku' => 'VN-0004',
        'price' => 690000,
        'qty' => 3,
        'status' => 'scheduled',
    ],
    [
        'name' => 'Queen - Greatest Hits',
        'desc' => 'Vinyl, 2LP, Compilation',
        'category' => 'Rock',
        'stock' => false,
        'sku' => 'VN-0005',
        'price' => 850000,
        'qty' => 0,
        'status' => 'inactive',
    ],
    [
        'name' => 'Led Zeppelin IV',
        'desc' => 'Vinyl, 180g, Classic Rock',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0006',
        'price' => 780000,
        'qty' => 7,
        'status' => 'active',
    ],
    [
        'name' => 'AC/DC - Back in Black',
        'desc' => 'Vinyl, 180g, Remastered',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0007',
        'price' => 730000,
        'qty' => 6,
        'status' => 'active',
    ],
    [
        'name' => 'Michael Jackson - Thriller',
        'desc' => 'Vinyl, 180g, Pop Classic',
        'category' => 'Pop',
        'stock' => false,
        'sku' => 'VN-0008',
        'price' => 900000,
        'qty' => 0,
        'status' => 'inactive',
    ],
    [
        'name' => 'Eagles - Hotel California',
        'desc' => 'Vinyl, 180g, Remastered',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0009',
        'price' => 810000,
        'qty' => 4,
        'status' => 'scheduled',
    ],
    [
        'name' => 'Fleetwood Mac - Rumours',
        'desc' => 'Vinyl, 180g, Classic Album',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0010',
        'price' => 760000,
        'qty' => 9,
        'status' => 'active',
    ],
    [
        'name' => 'David Bowie - The Rise and Fall of Ziggy Stardust',
        'desc' => 'Vinyl, 180g, Glam Rock',
        'category' => 'Rock',
        'stock' => false,
        'sku' => 'VN-0011',
        'price' => 820000,
        'qty' => 0,
        'status' => 'inactive',
    ],
    [
        'name' => 'The Rolling Stones - Let It Bleed',
        'desc' => 'Vinyl, 180g, Remastered',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0012',
        'price' => 770000,
        'qty' => 2,
        'status' => 'active',
    ],
    [
        'name' => 'The Doors - L.A. Woman',
        'desc' => 'Vinyl, 180g, Classic Rock',
        'category' => 'Rock',
        'stock' => true,
        'sku' => 'VN-0013',
        'price' => 740000,
        'qty' => 5,
        'status' => 'scheduled',
    ],
    [
        'name' => 'U2 - The Joshua Tree',
        'desc' => 'Vinyl, 180g, Remastered',
        'category' => 'Rock',
        'stock' => false,
        'sku' => 'VN-0014',
        'price' => 830000,
        'qty' => 0,
        'status' => 'inactive',
    ],
    [
        'name' => 'Radiohead - OK Computer',
        'desc' => 'Vinyl, 2LP, Alternative',
        'category' => 'Alternative',
        'stock' => true,
        'sku' => 'VN-0015',
        'price' => 950000,
        'qty' => 6,
        'status' => 'active',
    ],
    [
        'name' => 'Daft Punk - Random Access Memories',
        'desc' => 'Vinyl, 2LP, Electronic',
        'category' => 'Electronic',
        'stock' => true,
        'sku' => 'VN-0016',
        'price' => 990000,
        'qty' => 8,
        'status' => 'active',
    ],
    [
        'name' => 'Adele - 21',
        'desc' => 'Vinyl, 180g, Pop',
        'category' => 'Pop',
        'stock' => false,
        'sku' => 'VN-0017',
        'price' => 870000,
        'qty' => 0,
        'status' => 'inactive',
    ],
    [
        'name' => 'Taylor Swift - 1989',
        'desc' => 'Vinyl, 2LP, Pop',
        'category' => 'Pop',
        'stock' => true,
        'sku' => 'VN-0018',
        'price' => 920000,
        'qty' => 10,
        'status' => 'scheduled',
    ],
    [
        'name' => 'Norah Jones - Come Away With Me',
        'desc' => 'Vinyl, 180g, Jazz',
        'category' => 'Jazz',
        'stock' => true,
        'sku' => 'VN-0019',
        'price' => 710000,
        'qty' => 4,
        'status' => 'active',
    ],
    [
        'name' => 'Amy Winehouse - Back to Black',
        'desc' => 'Vinyl, 180g, Soul',
        'category' => 'Soul',
        'stock' => false,
        'sku' => 'VN-0020',
        'price' => 880000,
        'qty' => 0,
        'status' => 'inactive',
    ],
];

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
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$start = ($currentPage - 1) * $rowsPerPage;
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
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="product_list.css" />
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="container-fluid pt-4 px-4">
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
                        <option value="">Category</option>
                        <option value="Rock" <?php if($category=='Rock') echo 'selected'; ?>>Rock</option>
                        <option value="Progressive Rock" <?php if($category=='Progressive Rock') echo 'selected'; ?>>Progressive Rock</option>
                        <option value="Jazz" <?php if($category=='Jazz') echo 'selected'; ?>>Jazz</option>
                        <option value="Grunge" <?php if($category=='Grunge') echo 'selected'; ?>>Grunge</option>
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
                    <form method="POST" style="margin:0;">
                        <button type="submit" class="export-btn"><i class="fas fa-file-export"></i> Export</button>
                    </form>
                    <a href="/WEB_MXH/admin/pages/product/add_product/add_product.php" class="add-btn"><i class="fas fa-plus"></i> Add Product</a>
                </div>
            </div>
            <!-- Product Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="select-all" /></th>
                            <th>PRODUCT</th>
                            <th>CATEGORY</th>
                            <th>STOCK</th>
                            <th>SKU</th>
                            <th>PRICE</th>
                            <th>QTY</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currentPageProducts as $product): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_products[]" value="<?php echo $product['sku']; ?>" /></td>
                            <td class="product-cell">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png" class="product-img-thumb" alt="vinyl">
                                <div>
                                    <div style="font-weight:600; color:#222;"><?php echo $product['name']; ?></div>
                                    <div style="font-size:0.9em;color:#888;"><?php echo $product['desc']; ?></div>
                                </div>
                            </td>
                            <td><?php echo $product['category']; ?></td>
                            <td>
                                <?php if($product['stock']): ?>
                                    <span class="badge-stock-in">In Stock</span>
                                <?php else: ?>
                                    <span class="badge-stock-out">Out</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['sku']; ?></td>
                            <td><?php echo number_format($product['price'],0,',','.'); ?>₫</td>
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
                                        <a href="#" class="dropdown-item"><i class="fas fa-eye"></i> View</a>
                                        <a href="#" class="dropdown-item text-danger"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="pagination-bar">
                <span class="entries-info">Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $totalRows; ?> entries</span>
                <div class="pagination-controls">
                    <?php if ($currentPage > 1): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $currentPage - 1]); ?>" class="page-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $i]); ?>" class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo getUrlWithParams(['page' => $currentPage + 1]); ?>" class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
<script src="product_list.js"></script>
</body>
</html>

