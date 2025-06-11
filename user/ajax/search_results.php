<?php
require_once '../../config/database.php';

$search = isset($_GET['term']) ? trim($_GET['term']) : '';
$results = [];

if (!empty($search)) {
    $search_param = "%{$search}%";
    
    // Search products
    $product_sql = "SELECT product_id, product_name, 'product' as type FROM products 
                    WHERE product_name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $product_result = $stmt->get_result();
    while ($row = $product_result->fetch_assoc()) {
        $results[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'type' => 'Sản phẩm',
            'url' => 'product-detail.php?type=product&id=' . $row['product_id']
        ];
    }
    
    // Search artists
    $artist_sql = "SELECT artist_id, artist_name, 'artist' as type FROM artists 
                   WHERE artist_name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($artist_sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $artist_result = $stmt->get_result();
    while ($row = $artist_result->fetch_assoc()) {
        $results[] = [
            'id' => $row['artist_id'],
            'name' => $row['artist_name'],
            'type' => 'Nghệ sĩ',
            'url' => 'products.php?artist_id=' . $row['artist_id']
        ];
    }
    
    // Search genres
    $genre_sql = "SELECT genre_id, genre_name, 'genre' as type FROM genres 
                  WHERE genre_name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($genre_sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $genre_result = $stmt->get_result();
    while ($row = $genre_result->fetch_assoc()) {
        $results[] = [
            'id' => $row['genre_id'],
            'name' => $row['genre_name'],
            'type' => 'Dòng nhạc',
            'url' => 'products.php?genre_id=' . $row['genre_id']
        ];
    }
    
    // Search accessories
    $accessory_sql = "SELECT accessory_id, accessory_name, 'accessory' as type FROM accessories 
                      WHERE accessory_name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($accessory_sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $accessory_result = $stmt->get_result();
    while ($row = $accessory_result->fetch_assoc()) {
        $results[] = [
            'id' => $row['accessory_id'],
            'name' => $row['accessory_name'],
            'type' => 'Phụ kiện',
            'url' => 'product-detail.php?type=accessory&id=' . $row['accessory_id']
        ];
    }
}

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($results);