<?php

// Mock data dựa trên bảng reviews, users, products
$feedbacks = [
    [
        'product' => [
            'name' => 'Air Jordan',
            'image' => 'https://randomuser.me/api/portraits/men/1.jpg',
            'desc' => 'Air Jordan is a line of basketball shoes produced by Nike.'
        ],
        'reviewer' => [
            'name' => 'Gisela Leppard',
            'email' => 'gleppard@byandex.ru',
        ],
        'rating' => 2,
        'comment' => 'Ut mauris. Fusce consequat. Nulla nisi. Nunc nisl.',
        'date' => 'Apr 20, 2020',
        'status' => 'Published',
    ],
    [
        'product' => [
            'name' => 'Amazon Fire TV',
            'image' => 'https://randomuser.me/api/portraits/men/2.jpg',
            'desc' => '4K UHD smart TV, stream live TV without cable.'
        ],
        'reviewer' => [
            'name' => 'Tracey Ventham',
            'email' => 'tventham@thetimes.co.uk',
        ],
        'rating' => 4,
        'comment' => 'At nunc commodo placerat praesent. Aenean fermentum. Donec ut mauris eget massa tempor convallis.',
        'date' => 'Mar 17, 2021',
        'status' => 'Published',
    ],
    [
        'product' => [
            'name' => 'Apple iPad',
            'image' => 'https://randomuser.me/api/portraits/men/3.jpg',
            'desc' => '10.2-inch Retina Display, 64GB'
        ],
        'reviewer' => [
            'name' => 'Jabez Heggs',
            'email' => 'jheggs@nba.com',
        ],
        'rating' => 1,
        'comment' => 'Ac consequat. Curabitur ut ipsum ac tellus semper interdum.',
        'date' => 'Apr 21, 2020',
        'status' => 'Published',
    ],
    [
        'product' => [
            'name' => 'Apple Watch Series 7',
            'image' => 'https://randomuser.me/api/portraits/women/4.jpg',
            'desc' => 'Starlight Aluminum Case with Starlight Sport Band.'
        ],
        'reviewer' => [
            'name' => 'Ethel Zanardii',
            'email' => 'ezanardi4@maycz.cz',
        ],
        'rating' => 3,
        'comment' => 'Etiam faucibus cursus. Cras non velit nec nisi vulputate nonummy.',
        'date' => 'Jun 12, 2020',
        'status' => 'Pending',
    ],
    // ... Thêm các feedback khác tương tự ...
];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$entries = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$entries = in_array($entries, [10, 25, 50]) ? $entries : 10;
$status = isset($_GET['status']) ? $_GET['status'] : 'All';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Filter by search
if ($search !== '') {
    $feedbacks = array_filter($feedbacks, function($fb) use ($search) {
        return stripos($fb['product']['name'], $search) !== false;
    });
}
// Filter by status
if ($status !== 'All') {
    $feedbacks = array_filter($feedbacks, function($fb) use ($status) {
        return $fb['status'] === $status;
    });
}
// Pagination phân trang
$totalRows = count($feedbacks);
$totalPages = ceil($totalRows / $entries);
$page = min($page, $totalPages > 0 ? $totalPages : 1);
$start = ($page - 1) * $entries;
$feedbacksPage = array_slice($feedbacks, $start, $entries);

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
    <title>Feedback List</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="feedback.css"/>
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php $currentPage = 'feedbacks'; include __DIR__.'/../../dashboard/sidebar.php'; ?>
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="container-fluid pt-4 px-4">
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 feedback-summary-box">
                        <div class="feedback-summary-title">4.89 <i class="fas fa-star feedback-summary-star"></i></div>
                        <div>
                            <div class="feedback-summary-label">Total 187 reviews</div>
                            <div class="feedback-summary-desc">All reviews are from genuine customers</div>
                            <div class="feedback-summary-green" style="font-size:0.9em;">+5 this week</div>
                        </div>
                        <div class="ms-4 feedback-summary-bar" style="min-width:180px;">
                            <div style="font-size:0.95em;">5 Star <span style="float:right;">124</span></div>
                            <div class="progress mb-1" style="height:6px;"><div class="progress-bar bg-success" style="width: 70%"></div></div>
                            <div style="font-size:0.95em;">4 Star <span style="float:right;">40</span></div>
                            <div class="progress mb-1" style="height:6px;"><div class="progress-bar bg-success" style="width: 22%"></div></div>
                            <div style="font-size:0.95em;">3 Star <span style="float:right;">12</span></div>
                            <div class="progress mb-1" style="height:6px;"><div class="progress-bar bg-warning" style="width: 6%"></div></div>
                            <div style="font-size:0.95em;">2 Star <span style="float:right;">7</span></div>
                            <div class="progress mb-1" style="height:6px;"><div class="progress-bar bg-warning" style="width: 1.5%"></div></div>
                            <div style="font-size:0.95em;">1 Star <span style="float:right;">2</span></div>
                            <div class="progress mb-1" style="height:6px;"><div class="progress-bar bg-danger" style="width: 0.5%"></div></div>
                        </div>
                    </div>
                </div>
                <!--<div class="col-md-4">
                    <div class="bg-white rounded p-3 shadow-sm feedback-summary-box">
                        <div class="feedback-summary-label">Reviews statistics</div>
                        <div style="font-size:0.95em;">12 New reviews <span class="feedback-summary-green" style="font-weight:600;">+4.6%</span></div>
                        <div style="font-size:0.95em;">87% Positive reviews</div>
                        <div class="feedback-summary-desc">Weekly Report</div>
                        <div><img src="https://i.imgur.com/8Km9tLL.png" alt="chart" style="width:100%;max-width:120px;"></div>
                    </div>
                </div>-->
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="get" class="d-flex">
                        <input type="text" class="form-control" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="entries" value="<?php echo $entries; ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                        <button type="submit" class="btn btn-primary ms-2">Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <form method="get" class="d-inline">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                        <select class="form-select d-inline-block w-auto" name="entries" onchange="this.form.submit()">
                            <option value="10" <?php if($entries==10) echo 'selected'; ?>>10</option>
                            <option value="25" <?php if($entries==25) echo 'selected'; ?>>25</option>
                            <option value="50" <?php if($entries==50) echo 'selected'; ?>>50</option>
                        </select>
                    </form>
                    <form method="get" class="d-inline">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="entries" value="<?php echo $entries; ?>">
                        <select class="form-select d-inline-block w-auto" name="status" onchange="this.form.submit()">
                            <option value="All" <?php if($status=='All') echo 'selected'; ?>>All</option>
                            <option value="Published" <?php if($status=='Published') echo 'selected'; ?>>Published</option>
                            <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table feedback-table align-middle">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>PRODUCT</th>
                                <th>REVIEWER</th>
                                <th>REVIEW</th>
                                <th>DATE</th>
                                <th>STATUS</th>
                                <th class="actions">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($feedbacksPage as $fb): ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($fb['product']['image']) ?>" class="product-img" alt="product">
                                        <div>
                                            <div style="font-weight:600; color:#222;"><?= htmlspecialchars($fb['product']['name']) ?></div>
                                            <div style="font-size:0.9em;color:#888;"><?= htmlspecialchars($fb['product']['desc']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="reviewer-name"><?= htmlspecialchars($fb['reviewer']['name']) ?></div>
                                    <div class="reviewer-email"><?= htmlspecialchars($fb['reviewer']['email']) ?></div>
                                </td>
                                <td>
                                    <div>
                                        <?php for($i=1;$i<=5;$i++): ?>
                                            <span class="star<?= $i <= $fb['rating'] ? '' : ' gray' ?>"><i class="fas fa-star"></i></span>
                                        <?php endfor; ?>
                                    </div>
                                    <div style="font-size:0.97em; color:#444; margin-top:2px;">
                                        <?= htmlspecialchars($fb['comment']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($fb['date']) ?></td>
                                <td>
                                    <?php if($fb['status']==='Published'): ?>
                                        <span class="badge-published">Published</span>
                                    <?php else: ?>
                                        <span class="badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <div class="dropdown">
                                        <button class="dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-eye"></i> View</a></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt"></i> Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div style="font-size:0.95em;color:#888;">Showing <?php echo $start + 1; ?> to <?php echo min($start + $entries, $totalRows); ?> of <?php echo $totalRows; ?> entries</div>
                    <nav>
                        <ul class="pagination mb-0" id="custom-pagination">
                            <?php
                            // Layout cố định 4 trang
                            $fixedTotalPages = 4;
                            if ($page <= 2) {
                                // Trang 1, 2: [1][2][>]
                                for ($i = 1; $i <= 2; $i++) {
                                    echo '<li class="page-item'.($page==$i?' active':'').'" data-page="'.$i.'"><a class="page-link" href="'.getUrlWithParams(['page'=>$i]).'">'.$i.'</a></li>';
                                }
                                if ($page < $fixedTotalPages-1) {
                                    echo '<li class="page-item" id="next-arrow"><a class="page-link" href="#">&gt;</a></li>';
                                }
                            } else {
                                // Trang 3, 4: [<][3][4]
                                echo '<li class="page-item" id="prev-arrow"><a class="page-link" href="#">&lt;</a></li>';
                                for ($i = 3; $i <= 4; $i++) {
                                    echo '<li class="page-item'.($page==$i?' active':'').'" data-page="'.$i.'"><a class="page-link" href="'.getUrlWithParams(['page'=>$i]).'">'.$i.'</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pag = document.getElementById('custom-pagination');
    if (!pag) return;

    function getCurrentPage() {
        const url = new URL(window.location.href);
        const p = url.searchParams.get('page');
        return p ? parseInt(p) : 1;
    }

    // Đổi tên biến để rõ ràng hơn
    let paginationLayout = (getCurrentPage() === 1 || getCurrentPage() === 2) ? '12' : '34';

    function renderPagination(layout, activePage) {
        let html = '';
        if(layout === '12') {
            // Layout [1][2][>]
            for(let i=1; i<=2; i++) {
                html += `<li class="page-item${activePage===i?' active':''}" data-page="${i}">
                    <a class="page-link" href="${window.location.pathname+window.location.search.replace(/([?&])page=\\d+/, `$1page=${i}`)}">${i}</a>
                </li>`;
            }
            html += '<li class="page-item" id="next-arrow"><a class="page-link" href="#">&gt;</a></li>';
        } else {
            // Layout [<][3][4]
            html = '<li class="page-item" id="prev-arrow"><a class="page-link" href="#">&lt;</a></li>';
            for(let i=3; i<=4; i++) {
                html += `<li class="page-item${activePage===i?' active':''}" data-page="${i}">
                    <a class="page-link" href="${window.location.pathname+window.location.search.replace(/([?&])page=\\d+/, `$1page=${i}`)}">${i}</a>
                </li>`;
            }
        }
        pag.innerHTML = html;
    }

    // Khởi tạo phân trang
    renderPagination(paginationLayout, getCurrentPage());

    // Xử lý sự kiện click
    pag.addEventListener('click', function(e) {
        const next = e.target.closest('#next-arrow');
        const prev = e.target.closest('#prev-arrow');
        const currentPage = getCurrentPage();

        if (next) {
            e.preventDefault();
            if (paginationLayout === '12') {
                paginationLayout = '34';
                renderPagination(paginationLayout, currentPage);
            }
        } else if (prev) {
            e.preventDefault();
            if (paginationLayout === '34') {
                paginationLayout = '12';
                renderPagination(paginationLayout, currentPage);
            }
        }
    });
});
</script>
</body>
</html>
