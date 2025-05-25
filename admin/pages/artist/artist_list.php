<?php
$currentPage = 'artist';
// Dữ liệu mẫu nghệ sĩ
$artists = [
    [
        'artist_id' => 1,
        'artist_name' => 'The Beatles',
        'bio' => 'The Beatles là ban nhạc rock người Anh được thành lập tại Liverpool năm 1960...',
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/d/df/The_Fabs.JPG',
        'product_count' => 3,
        'status' => 'active'
    ],
    [
        'artist_id' => 2,
        'artist_name' => 'Adele',
        'bio' => 'Adele Laurie Blue Adkins MBE là một ca sĩ kiêm nhạc sĩ người Anh...',
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/6/6e/Adele_2016.jpg',
        'product_count' => 2,
        'status' => 'active'
    ]
];

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
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <?php include __DIR__.'/../dashboard/sidebar.php'; ?>
        
        <div class="content">
            <?php include __DIR__.'/../dashboard/navbar.php'; ?>
            
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Artist Management</h6>
                            
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
                                <a href="/WEB_MXH/admin/pages/artist/add_artist.php" class="add-btn"><i class="fas fa-plus"></i> Add Artist</a>
                            </div>

                            <!-- Artist Table -->
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><input type="checkbox" /></th>
                                            <th scope="col">ARTIST</th>
                                            <th scope="col">PRODUCTS</th>
                                            <th scope="col">STATUS</th>
                                            <th scope="col">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($currentPageArtists as $artist): ?>
                                        <tr>
                                            <td><input type="checkbox" name="selected_artists[]" value="<?php echo $artist['artist_id']; ?>" /></td>
                                            <td class="artist-cell">
                                                <img src="<?php echo $artist['image_url']; ?>" class="artist-img-thumb" alt="artist">
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
                                                    <button class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    Showing <?php echo $start + 1; ?> to <?php echo min($start + $rowsPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
                                </div>
                                <div class="pagination-controls">
                                    <select onchange="window.location.href='<?php echo getUrlWithParams(['rows' => '']); ?>' + this.value">
                                        <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                    </select>
                                    
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
            
            <?php include __DIR__.'/../dashboard/footer.php'; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html> 