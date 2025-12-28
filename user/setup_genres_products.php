<?php
require_once '../config/database.php';

// 0. Update Schema if needed
// Add 'image' to genres if not exists
$checkCol = $conn->query("SHOW COLUMNS FROM genres LIKE 'image'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE genres ADD COLUMN image VARCHAR(255) DEFAULT 'default_genre.jpg'");
    echo "Added 'image' column to genres table.<br>";
}

// Add 'freelancer_id' to products if not exists
$checkColP = $conn->query("SHOW COLUMNS FROM products LIKE 'freelancer_id'");
if ($checkColP->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN freelancer_id INT(11) DEFAULT 1"); // Default to admin/freelancer for now
    echo "Added 'freelancer_id' column to products table.<br>";
}

// 1. Setup Genres (Danh mục kĩ năng)
$genres = [
    ['name' => 'Lập trình & CNTT', 'desc' => 'Dịch vụ lập trình website, ứng dụng mobile, phần mềm...', 'image' => 'it.jpg'],
    ['name' => 'Thiết kế đồ họa', 'desc' => 'Thiết kế logo, banner, bộ nhận diện thương hiệu...', 'image' => 'design.jpg'],
    ['name' => 'Viết lách & Dịch thuật', 'desc' => 'Viết bài SEO, content marketing, dịch thuật đa ngôn ngữ...', 'image' => 'writing.jpg'],
    ['name' => 'Digital Marketing', 'desc' => 'Quảng cáo Facebook, Google, SEO website, Email Marketing...', 'image' => 'marketing.jpg'],
    ['name' => 'Video & Âm thanh', 'desc' => 'Dựng video, chỉnh sửa âm thanh, lồng tiếng, thu âm...', 'image' => 'video.jpg'],
    ['name' => 'Đời sống & Cá nhân', 'desc' => 'Tư vấn tâm lý, hướng dẫn kỹ năng sống, lifestyle...', 'image' => 'lifestyle.jpg']
];

echo "<h3>Updating Genres...</h3>";
foreach ($genres as $g) {
    $name = $g['name'];
    $desc = $g['desc'];
    $image = $g['image'];
    
    // Check if exists
    $check = $conn->query("SELECT genre_id FROM genres WHERE genre_name = '$name'");
    if ($check->num_rows == 0) {
        // Use genre_name and description columns
        $sql = "INSERT INTO genres (genre_name, description, image) VALUES ('$name', '$desc', '$image')";
        if ($conn->query($sql)) {
            echo "Inserted genre: $name<br>";
        } else {
            echo "Error inserting genre $name: " . $conn->error . "<br>";
        }
    } else {
        echo "Genre exists: $name<br>";
    }
}

// 2. Setup Products (Dịch vụ)
$products = [
    'Lập trình & CNTT' => [
        ['name' => 'Lập trình Website trọn gói', 'price' => 5000000, 'description' => 'Thiết kế và lập trình website chuyên nghiệp, chuẩn SEO.'],
        ['name' => 'Lập trình Mobile App (iOS/Android)', 'price' => 10000000, 'description' => 'Phát triển ứng dụng di động đa nền tảng.'],
        ['name' => 'Sửa lỗi & Tối ưu website', 'price' => 500000, 'description' => 'Fix bug, tăng tốc độ tải trang, bảo mật website.']
    ],
    'Thiết kế đồ họa' => [
        ['name' => 'Thiết kế Logo thương hiệu', 'price' => 1000000, 'description' => 'Logo độc đáo, sáng tạo, bao gồm bộ nhận diện thương hiệu.'],
        ['name' => 'Thiết kế Banner quảng cáo', 'price' => 300000, 'description' => 'Banner thu hút cho Facebook, Google Ads.'],
        ['name' => 'Vẽ minh họa 2D/3D', 'price' => 1500000, 'description' => 'Minh họa sách, game, nhân vật theo yêu cầu.']
    ],
    'Viết lách & Dịch thuật' => [
        ['name' => 'Viết bài chuẩn SEO', 'price' => 100000, 'description' => 'Bài viết chất lượng, tối ưu từ khóa lên top Google.'],
        ['name' => 'Dịch thuật Anh - Việt', 'price' => 50000, 'description' => 'Dịch tài liệu, hợp đồng, video clip chính xác.']
    ],
    'Digital Marketing' => [
        ['name' => 'Chạy quảng cáo Facebook Ads', 'price' => 2000000, 'description' => 'Tối ưu ngân sách, target đúng đối tượng khách hàng.'],
        ['name' => 'Quản trị Fanpage trọn gói', 'price' => 3000000, 'description' => 'Đăng bài, trả lời comment, tăng tương tác tự nhiên.']
    ]
];

echo "<h3>Updating Products...</h3>";
foreach ($products as $genreName => $items) {
    // Get genre_id
    $gRes = $conn->query("SELECT genre_id FROM genres WHERE genre_name = '$genreName'");
    if ($gRes && $gRes->num_rows > 0) {
        $genreId = $gRes->fetch_assoc()['genre_id'];
        
        foreach ($items as $p) {
            $pName = $p['name'];
            $pPrice = $p['price'];
            $pDesc = $p['description'];
            
            // Check if exists
            $checkP = $conn->query("SELECT product_id FROM products WHERE product_name = '$pName'");
            if ($checkP->num_rows == 0) {
                // Use product_name and image_url columns
                // Default freelancer_id = 1 (assuming admin/freelancer exists with id 1)
                $sqlP = "INSERT INTO products (product_name, price, description, genre_id, freelancer_id, image_url, stock) 
                         VALUES ('$pName', $pPrice, '$pDesc', $genreId, 1, 'default_service.jpg', 100)";
                if ($conn->query($sqlP)) {
                    echo "Inserted product: $pName<br>";
                } else {
                    echo "Error inserting product $pName: " . $conn->error . "<br>";
                }
            } else {
                echo "Product exists: $pName<br>";
            }
        }
    }
}

$conn->close();
?>
<br>
<a href="index.php">Go back to Home</a>
