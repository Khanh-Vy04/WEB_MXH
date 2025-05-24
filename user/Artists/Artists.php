<?php
// Dữ liệu mẫu cho nghệ sĩ
$artists = [
    [
        'name' => '21 Savage',
        'image' => 'https://link-to-image/21savage.jpg',
        'product_count' => 2
    ],
    [
        'name' => 'Adele',
        'image' => 'https://link-to-image/adele.jpg',
        'product_count' => 4
    ],
    [
        'name' => 'Ariana Grande',
        'image' => 'https://link-to-image/ariana.jpg',
        'product_count' => 7
    ],
    [
        'name' => 'A Tribe Called Quest',
        'image' => 'https://link-to-image/atribecalledquest.jpg',
        'product_count' => 4
    ],
    [
        'name' => 'ASAP Rocky',
        'image' => 'https://link-to-image/asaprocky.jpg',
        'product_count' => 2
    ],
    [
        'name' => 'AC/DC',
        'image' => 'https://link-to-image/acdc.jpg',
        'product_count' => 2
    ],
    [
        'name' => 'Aerosmith',
        'image' => 'https://link-to-image/aerosmith.jpg',
        'product_count' => 3
    ],
    [
        'name' => 'Al Green',
        'image' => 'https://link-to-image/algreen.jpg',
        'product_count' => 3
    ],
    [
        'name' => 'Alex Turner',
        'image' => 'https://link-to-image/alexturner.jpg',
        'product_count' => 1
    ],
    [
        'name' => 'Alice In Chains',
        'image' => 'https://link-to-image/aliceinchains.jpg',
        'product_count' => 4
    ],
    [
        'name' => 'Alvvays',
        'image' => 'https://link-to-image/alvvays.jpg',
        'product_count' => 2
    ],
    [
        'name' => 'Amy Winehouse',
        'image' => 'https://link-to-image/amywinehouse.jpg',
        'product_count' => 1
    ],
    [
        'name' => 'Aqua',
        'image' => 'https://link-to-image/aqua.jpg',
        'product_count' => 1
    ],
    [
        'name' => 'Arctic Monkeys',
        'image' => 'https://link-to-image/arcticmonkeys.jpg',
        'product_count' => 7
    ],
    [
        'name' => 'Avenged Sevenfold',
        'image' => 'https://link-to-image/avengedsevenfold.jpg',
        'product_count' => 3
    ],
    [
        'name' => 'Avril Lavigne',
        'image' => 'https://link-to-image/avrillavigne.jpg',
        'product_count' => 2
    ],
    // ... Có thể thêm nhiều nghệ sĩ khác nếu muốn
];
?>

<!-- Bootstrap 5 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bắt đầu phần nội dung -->
<div class="container py-5">
    <div class="row justify-content-center">
        <?php foreach ($artists as $artist): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center mb-4">
                <div class="card h-100 shadow-sm" style="width: 100%; max-width: 260px;">
                    <img src="<?= $artist['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($artist['name']) ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($artist['name']) ?></h5>
                        <p class="card-text text-muted"><?= $artist['product_count'] ?> sản phẩm</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Thêm CSS căn chỉnh nhẹ -->
<style>
.card-img-top {
    object-fit: cover;
    height: 220px;
}
.card-title {
    font-weight: bold;
    font-size: 1.1rem;
}
.card {
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.card:hover {
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
}
.row {
    margin-left: 0;
    margin-right: 0;
}

/* Đảm bảo submenu không bị đè lên icon và chữ */
.sidebar .submenu {
    padding-left: 40px; /* Đẩy submenu sang phải, tránh icon */
    background: #f7f7f7; /* Màu nền nhẹ cho submenu */
    border-radius: 8px;
    margin: 4px 0 4px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.sidebar .submenu a {
    display: block;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 2px;
}

.sidebar .submenu a:hover {
    background: #e0e0e0;
    color: #222;
}

/* Đảm bảo dropdown không bị chồng lên nhau */
.sidebar .submenu {
    position: relative;
    z-index: 10;
}

.sidebar .navbar .nav-item.dropdown {
    position: relative;
}

.sidebar .navbar .dropdown-menu {
    z-index: 3000 !important;
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    width: 100%;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 200px;
    margin-top: 4px;
    padding: 8px 0;
}

.sidebar .navbar .dropdown-menu.show {
    display: block !important;
}

.sidebar .navbar .dropdown-item {
    padding: 8px 32px 8px 48px;
    color: #333;
    text-decoration: none;
    display: block;
    white-space: nowrap;
}

.sidebar .navbar .dropdown-item:hover {
    background-color: #f5f5f5;
}

.sidebar .navbar .dropdown-item.active {
    background-color: #e9ecef;
}
</style>