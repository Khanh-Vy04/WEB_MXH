<?php
require_once 'config/database.php';

echo "<h2>Kiểm tra dữ liệu trong Database</h2>";

// Kiểm tra artists
echo "<h3>Artists:</h3>";
$artists_sql = "SELECT artist_id, artist_name, status FROM artists ORDER BY artist_id";
$artists_result = $conn->query($artists_sql);
if ($artists_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
    while($row = $artists_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['artist_id'] . "</td>";
        echo "<td>" . $row['artist_name'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Không có artists nào";
}

// Kiểm tra genres
echo "<h3>Genres:</h3>";
$genres_sql = "SELECT genre_id, genre_name FROM genres ORDER BY genre_id";
$genres_result = $conn->query($genres_sql);
if ($genres_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th></tr>";
    while($row = $genres_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['genre_id'] . "</td>";
        echo "<td>" . $row['genre_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Không có genres nào";
}

// Kiểm tra products
echo "<h3>Products:</h3>";
$products_sql = "SELECT product_id, product_name, genre_id FROM products ORDER BY product_id LIMIT 10";
$products_result = $conn->query($products_sql);
if ($products_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Product ID</th><th>Product Name</th><th>Genre ID</th></tr>";
    while($row = $products_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_name'] . "</td>";
        echo "<td>" . $row['genre_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Không có products nào";
}

// Kiểm tra artist_products
echo "<h3>Artist Products:</h3>";
$ap_sql = "SELECT ap.artist_id, ap.product_id, a.artist_name, p.product_name 
           FROM artist_products ap 
           LEFT JOIN artists a ON ap.artist_id = a.artist_id 
           LEFT JOIN products p ON ap.product_id = p.product_id 
           ORDER BY ap.artist_id LIMIT 10";
$ap_result = $conn->query($ap_sql);
if ($ap_result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Artist ID</th><th>Product ID</th><th>Artist Name</th><th>Product Name</th></tr>";
    while($row = $ap_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['artist_id'] . "</td>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['artist_name'] . "</td>";
        echo "<td>" . $row['product_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Không có artist_products nào";
}

// Test các URL cụ thể
echo "<h3>Test URLs:</h3>";
echo "<p><a href='user/products.php?artist_id=4&debug=1'>Test artist_id=4</a></p>";
echo "<p><a href='user/products.php?genre_id=2&debug=1'>Test genre_id=2</a></p>";
echo "<p><a href='user/products.php?artist_id=1&debug=1'>Test artist_id=1</a></p>";
echo "<p><a href='user/products.php?genre_id=1&debug=1'>Test genre_id=1</a></p>";
?> 