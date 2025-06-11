<?php
require_once 'config/database.php';

echo "<h1>Database Structure Analysis</h1>";
echo "<style>
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
.error { color: red; font-weight: bold; }
.success { color: green; font-weight: bold; }
.info { background: #e7f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
</style>";

// Kiểm tra kết nối database
if ($conn->connect_error) {
    echo "<div class='error'>Connection failed: " . $conn->connect_error . "</div>";
    exit;
}

echo "<div class='success'>✅ Database connected successfully</div>";

// Function để describe table
function describeTable($conn, $tableName) {
    echo "<h2>📋 Table: $tableName</h2>";
    
    // DESCRIBE table
    $desc_sql = "DESCRIBE $tableName";
    $desc_result = $conn->query($desc_sql);
    
    if ($desc_result) {
        echo "<h3>Structure:</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $desc_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ Error describing table: " . $conn->error . "</div>";
        return;
    }
    
    // Count records
    $count_sql = "SELECT COUNT(*) as total FROM $tableName";
    $count_result = $conn->query($count_sql);
    $total = $count_result->fetch_assoc()['total'];
    
    echo "<h3>Data (Total: $total records):</h3>";
    
    // Show sample data
    $sample_sql = "SELECT * FROM $tableName LIMIT 10";
    $sample_result = $conn->query($sample_sql);
    
    if ($sample_result && $sample_result->num_rows > 0) {
        echo "<table>";
        $first_row = true;
        while($row = $sample_result->fetch_assoc()) {
            if ($first_row) {
                echo "<tr>";
                foreach($row as $key => $value) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first_row = false;
            }
            echo "<tr>";
            foreach($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>ℹ️ No data found in table</div>";
    }
    
    echo "<hr>";
}

// Describe các bảng chính
describeTable($conn, 'genres');
describeTable($conn, 'artists');
describeTable($conn, 'products');

// Kiểm tra bảng artist_products nếu có
$tables_sql = "SHOW TABLES LIKE 'artist_products'";
$tables_result = $conn->query($tables_sql);
if ($tables_result->num_rows > 0) {
    describeTable($conn, 'artist_products');
} else {
    echo "<div class='error'>❌ Table 'artist_products' not found!</div>";
}

// Kiểm tra relationship data
echo "<h2>🔗 Relationship Analysis</h2>";

// Products với genre
echo "<h3>Products by Genre:</h3>";
$pg_sql = "SELECT g.genre_id, g.genre_name, COUNT(p.product_id) as product_count 
           FROM genres g 
           LEFT JOIN products p ON g.genre_id = p.genre_id 
           GROUP BY g.genre_id, g.genre_name 
           ORDER BY g.genre_id";
$pg_result = $conn->query($pg_sql);

if ($pg_result) {
    echo "<table>";
    echo "<tr><th>Genre ID</th><th>Genre Name</th><th>Product Count</th></tr>";
    while($row = $pg_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['genre_id'] . "</td>";
        echo "<td>" . $row['genre_name'] . "</td>";
        echo "<td>" . $row['product_count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Products với artist (nếu có bảng artist_products)
if ($conn->query("SHOW TABLES LIKE 'artist_products'")->num_rows > 0) {
    echo "<h3>Products by Artist:</h3>";
    $pa_sql = "SELECT a.artist_id, a.artist_name, COUNT(ap.product_id) as product_count 
               FROM artists a 
               LEFT JOIN artist_products ap ON a.artist_id = ap.artist_id 
               GROUP BY a.artist_id, a.artist_name 
               ORDER BY a.artist_id";
    $pa_result = $conn->query($pa_sql);

    if ($pa_result) {
        echo "<table>";
        echo "<tr><th>Artist ID</th><th>Artist Name</th><th>Product Count</th></tr>";
        while($row = $pa_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['artist_id'] . "</td>";
            echo "<td>" . $row['artist_name'] . "</td>";
            echo "<td>" . $row['product_count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test specific queries
echo "<h2>🧪 Test Queries</h2>";

echo "<h3>Test genre_id = 2:</h3>";
$test_genre_sql = "SELECT * FROM genres WHERE genre_id = 2";
$test_genre_result = $conn->query($test_genre_sql);
if ($test_genre_result && $test_genre_result->num_rows > 0) {
    $genre = $test_genre_result->fetch_assoc();
    echo "<div class='success'>✅ Genre ID 2 found: " . $genre['genre_name'] . "</div>";
    
    // Test products for this genre
    $test_products_sql = "SELECT COUNT(*) as count FROM products WHERE genre_id = 2";
    $test_products_result = $conn->query($test_products_sql);
    $product_count = $test_products_result->fetch_assoc()['count'];
    echo "<div class='info'>Products for genre_id 2: $product_count</div>";
    
    if ($product_count > 0) {
        $products_sql = "SELECT product_id, product_name, price, stock FROM products WHERE genre_id = 2 LIMIT 5";
        $products_result = $conn->query($products_sql);
        echo "<table>";
        echo "<tr><th>Product ID</th><th>Name</th><th>Price</th><th>Stock</th></tr>";
        while($row = $products_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>" . $row['price'] . "</td>";
            echo "<td>" . $row['stock'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='error'>❌ Genre ID 2 not found!</div>";
}

echo "<h3>Test artist_id = 4:</h3>";
$test_artist_sql = "SELECT * FROM artists WHERE artist_id = 4";
$test_artist_result = $conn->query($test_artist_sql);
if ($test_artist_result && $test_artist_result->num_rows > 0) {
    $artist = $test_artist_result->fetch_assoc();
    echo "<div class='success'>✅ Artist ID 4 found: " . $artist['artist_name'] . "</div>";
} else {
    echo "<div class='error'>❌ Artist ID 4 not found!</div>";
}

// Quick fix suggestions
echo "<h2>💡 Quick Fix Suggestions</h2>";
echo "<div class='info'>";
echo "1. Check if the URL is correct: <code>http://localhost/web_mxh/user/products.php?genre_id=2&debug=1</code><br>";
echo "2. Test direct access: <a href='user/products.php?genre_id=2&debug=1' target='_blank'>Click here to test genre_id=2 with debug</a><br>";
echo "3. Test artist: <a href='user/products.php?artist_id=4&debug=1' target='_blank'>Click here to test artist_id=4 with debug</a><br>";
echo "4. Check if you're accessing from the correct domain/port<br>";
echo "5. Check if there are any PHP errors in browser console or server logs<br>";
echo "</div>";

$conn->close();
?> 