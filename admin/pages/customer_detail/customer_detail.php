<?php
$currentPage = 'customer';
// Sample data for a vinyl record web (mockup)
$customer = [
    'user_id' => 1,
    'full_name' => 'Nguyen Van A',
    'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
    'email' => 'nguyenvana@gmail.com',
    'phone' => '+84901234567',
];
$default_address = [
    'name' => 'Nguyen Van A',
    'address' => '123 Le Loi Street',
    'city' => 'District 1',
    'state' => 'Ho Chi Minh City',
    'country' => 'Vietnam',
    'email' => 'nguyenvana@gmail.com',
    'phone' => '+84901234567',
];
$orders = [
    [ 'order_id' => 2453, 'total' => 650000, 'payment_status' => 'PAID', 'delivery_type' => 'Cash on delivery', ],
    [ 'order_id' => 2452, 'total' => 800000, 'payment_status' => 'CANCELLED', 'delivery_type' => 'Free shipping', ],
    [ 'order_id' => 2451, 'total' => 375000, 'payment_status' => 'PENDING', 'delivery_type' => 'Local pickup', ],
    [ 'order_id' => 2450, 'total' => 657000, 'payment_status' => 'CANCELLED', 'delivery_type' => 'Standard shipping', ],
    [ 'order_id' => 2449, 'total' => 9562000, 'payment_status' => 'FAILED', 'delivery_type' => 'Express', ],
    [ 'order_id' => 2448, 'total' => 46000, 'payment_status' => 'PAID', 'delivery_type' => 'Local delivery', ],
    // Thêm dữ liệu mẫu
    [ 'order_id' => 2447, 'total' => 1200000, 'payment_status' => 'PAID', 'delivery_type' => 'Express', ],
    [ 'order_id' => 2446, 'total' => 320000, 'payment_status' => 'PENDING', 'delivery_type' => 'Standard shipping', ],
    [ 'order_id' => 2445, 'total' => 210000, 'payment_status' => 'PAID', 'delivery_type' => 'Free shipping', ],
    [ 'order_id' => 2444, 'total' => 999000, 'payment_status' => 'FAILED', 'delivery_type' => 'Local pickup', ],
];
$wishlist = [
    [ 'product_img' => 'https://i.imgur.com/AbbeyRoad.jpg', 'product_name' => 'The Beatles - Abbey Road', 'color' => 'Black', 'size' => '12"', ],
    [ 'product_img' => 'https://i.imgur.com/DarkSide.jpg', 'product_name' => 'Pink Floyd - The Dark Side of the Moon', 'color' => 'Black', 'size' => '12"', ],
    [ 'product_img' => 'https://i.imgur.com/KindOfBlue.jpg', 'product_name' => 'Miles Davis - Kind of Blue', 'color' => 'Blue', 'size' => '12"', ],
    [ 'product_img' => 'https://i.imgur.com/Nevermind.jpg', 'product_name' => 'Nirvana - Nevermind', 'color' => 'Black', 'size' => '12"', ],
    [ 'product_img' => 'https://i.imgur.com/GreatestHits.jpg', 'product_name' => 'Queen - Greatest Hits', 'color' => 'Red', 'size' => '12"', ],
    // Thêm dữ liệu mẫu
    [ 'product_img' => 'https://i.imgur.com/AbbeyRoad.jpg', 'product_name' => 'The Beatles - Let It Be', 'color' => 'White', 'size' => '7"', ],
    [ 'product_img' => 'https://i.imgur.com/DarkSide.jpg', 'product_name' => 'Pink Floyd - Animals', 'color' => 'Pink', 'size' => '12"', ],
    [ 'product_img' => 'https://i.imgur.com/KindOfBlue.jpg', 'product_name' => 'Miles Davis - Bitches Brew', 'color' => 'Blue', 'size' => '12"', ],
];
$reviews = [
    [ 'product_name' => 'The Beatles - Abbey Road', 'rating' => 5, 'review' => 'Amazing sound quality, classic album!', ],
    [ 'product_name' => 'Pink Floyd - The Dark Side of the Moon', 'rating' => 4, 'review' => 'Great pressing, but the cover was a bit damaged.', ],
    [ 'product_name' => 'Miles Davis - Kind of Blue', 'rating' => 5, 'review' => 'Jazz masterpiece, must-have for any collection.', ],
    // Thêm dữ liệu mẫu
    [ 'product_name' => 'Queen - Greatest Hits', 'rating' => 4, 'review' => 'Good compilation, but some tracks missing.', ],
    [ 'product_name' => 'Nirvana - Nevermind', 'rating' => 5, 'review' => 'Legendary grunge album!', ],
    [ 'product_name' => 'The Beatles - Let It Be', 'rating' => 3, 'review' => 'Not my favorite Beatles album.', ],
];
// Xử lý view all
$show_all_orders = isset($_GET['all_orders']);
$show_all_wishlist = isset($_GET['all_wishlist']);
$show_all_reviews = isset($_GET['all_reviews']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Detail (Mockup)</title>
    <link href="customer_detail.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .customer-card, .address-card {background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 24px;}
        .customer-avatar {width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-right: 24px;}
        .customer-meta {font-size: 1.1em; color: #888;}
        .customer-stats {display: flex; gap: 2rem; margin-top: 18px;}
        .customer-stats .stat {text-align: center;}
        .section-title {font-size: 1.3rem; font-weight: 600; margin-bottom: 16px; color: #222;}
        .table th, .table td {vertical-align: middle; text-align: center; padding: 12px 16px;}
        .table th {background: #f8f9fa; font-weight: 700; color: #222;}
        .table td {color: #333;}
        .table thead tr th, .table tbody tr td {border-bottom: 1.5px solid #e5e7eb;}
        .table .first-col {text-align: left;}
        .badge-status {font-size: 0.95em; padding: 2px 10px; border-radius: 8px;}
        .badge-PAID {background: #d1fae5; color: #065f46;}
        .badge-CANCELLED {background: #fee2e2; color: #991b1b;}
        .badge-PENDING {background: #fef9c3; color: #92400e;}
        .badge-FAILED {background: #fee2e2; color: #991b1b;}
        .wishlist-img {width: 36px; height: 36px; object-fit: cover; border-radius: 8px; margin-right: 10px;}
        .star {color: #f59e42; font-size: 1.1em;}
        .customer-header-flex { display: flex; align-items: center; gap: 20px; }
        .customer-header-flex .customer-avatar { flex-shrink: 0; }
        .customer-header-flex .customer-name { font-size: 1.7rem; font-weight: 700; margin-bottom: 0; }
        .address-info-row { display: flex; margin-bottom: 6px; }
        .address-info-label { min-width: 70px; color: #888; font-weight: 500; }
        .address-info-value { color: #222; }
        @media (max-width: 991px) {
            .table th, .table td {padding: 10px 8px;}
        }
    </style>
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../dashboard/sidebar.php'; ?>
    <div class="content">
        <?php include __DIR__.'/../dashboard/navbar.php'; ?>
        <div class="container py-4">
            <div class="row g-4">
                <!-- Left column -->
                <div class="col-lg-4">
                    <div class="customer-card mb-4">
                        <div class="section-title mb-3">Customer Detail</div>
                        <div class="customer-header-flex mb-3">
                            <img src="<?php echo $customer['avatar']; ?>" class="customer-avatar" alt="avatar">
                            <div class="customer-name"><?php echo $customer['full_name']; ?></div>
                        </div>
                        <hr>
                        <div class="address-card" style="box-shadow:none; padding:0; background:none;">
                            <div class="address-info-row"><div class="address-info-label">Address</div><div class="address-info-value"><?php echo $default_address['address']; ?><br><?php echo $default_address['city']; ?>, <?php echo $default_address['state']; ?><br><?php echo $default_address['country']; ?></div></div>
                            <div class="address-info-row"><div class="address-info-label">Email</div><div class="address-info-value"><?php echo $default_address['email']; ?></div></div>
                            <div class="address-info-row"><div class="address-info-label">Phone</div><div class="address-info-value"><?php echo $default_address['phone']; ?></div></div>
                        </div>
                    </div>
                </div>
                <!-- Right column -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0">Orders <span class="text-secondary" style="font-size:1rem;">(<?php echo count($orders); ?>)</span></div>
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete customer</button>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width:90px; text-align:left;">ORDER</th>
                                    <th style="min-width:110px;">TOTAL</th>
                                    <th style="min-width:140px;">PAYMENT STATUS</th>
                                    <th style="min-width:150px;">DELIVERY TYPE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $orders_to_show = $show_all_orders ? $orders : array_slice($orders, 0, 6);
                                foreach($orders_to_show as $o): ?>
                                <tr>
                                    <td class="first-col"><a href="#">#<?php echo $o['order_id']; ?></a></td>
                                    <td><?php echo number_format($o['total'],0,',','.'); ?>₫</td>
                                    <td><span class="badge badge-status badge-<?php echo $o['payment_status']; ?>"><?php echo $o['payment_status']; ?></span></td>
                                    <td><?php echo $o['delivery_type']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-end"><small>1 to <?php echo min(count($orders_to_show), count($orders)); ?> Items of <?php echo count($orders); ?> &nbsp; 
                            <?php if(!$show_all_orders && count($orders) > 6): ?>
                                <a href="?all_orders=1<?php echo $show_all_wishlist ? '&all_wishlist=1' : ''; ?><?php echo $show_all_reviews ? '&all_reviews=1' : ''; ?>">View all</a>
                            <?php elseif($show_all_orders): ?>
                                <a href="?<?php echo $show_all_wishlist ? 'all_wishlist=1&' : ''; ?><?php echo $show_all_reviews ? 'all_reviews=1&' : ''; ?>">Show less</a>
                            <?php endif; ?>
                        </small></div>
                    </div>
                    <div class="section-title">Wishlist <span class="text-secondary" style="font-size:1rem;">(<?php echo count($wishlist); ?>)</span></div>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width:220px; text-align:left;">PRODUCTS</th>
                                    <th style="min-width:90px;">COLOR</th>
                                    <th style="min-width:70px;">SIZE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $wishlist_to_show = $show_all_wishlist ? $wishlist : array_slice($wishlist, 0, 5);
                                foreach($wishlist_to_show as $w): ?>
                                <tr>
                                    <td class="first-col"><img src="<?php echo $w['product_img']; ?>" class="wishlist-img" alt=""> <?php echo $w['product_name']; ?></td>
                                    <td><?php echo $w['color']; ?></td>
                                    <td><?php echo $w['size']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-end"><small>1 to <?php echo min(count($wishlist_to_show), count($wishlist)); ?> Items of <?php echo count($wishlist); ?> &nbsp; 
                            <?php if(!$show_all_wishlist && count($wishlist) > 5): ?>
                                <a href="?all_wishlist=1<?php echo $show_all_orders ? '&all_orders=1' : ''; ?><?php echo $show_all_reviews ? '&all_reviews=1' : ''; ?>">View all</a>
                            <?php elseif($show_all_wishlist): ?>
                                <a href="?<?php echo $show_all_orders ? 'all_orders=1&' : ''; ?><?php echo $show_all_reviews ? 'all_reviews=1&' : ''; ?>">Show less</a>
                            <?php endif; ?>
                        </small></div>
                    </div>
                    <div class="section-title">Ratings & reviews <span class="text-secondary" style="font-size:1rem;">(<?php echo count($reviews); ?>)</span></div>
                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th style="min-width:200px; text-align:left;">PRODUCT</th>
                                    <th style="min-width:90px;">RATING</th>
                                    <th style="min-width:220px;">REVIEW</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $reviews_to_show = $show_all_reviews ? $reviews : array_slice($reviews, 0, 3);
                                foreach($reviews_to_show as $r): ?>
                                <tr>
                                    <td class="first-col"><?php echo $r['product_name']; ?></td>
                                    <td><?php for($i=0;$i<$r['rating'];$i++) echo '<span class="star">★</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($r['review']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-end"><small>1 to <?php echo min(count($reviews_to_show), count($reviews)); ?> Items of <?php echo count($reviews); ?> &nbsp; 
                            <?php if(!$show_all_reviews && count($reviews) > 3): ?>
                                <a href="?all_reviews=1<?php echo $show_all_orders ? '&all_orders=1' : ''; ?><?php echo $show_all_wishlist ? '&all_wishlist=1' : ''; ?>">View all</a>
                            <?php elseif($show_all_reviews): ?>
                                <a href="?<?php echo $show_all_orders ? 'all_orders=1&' : ''; ?><?php echo $show_all_wishlist ? 'all_wishlist=1&' : ''; ?>">Show less</a>
                            <?php endif; ?>
                        </small></div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/4e9c2b1c2e.js" crossorigin="anonymous"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html>
