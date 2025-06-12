<?php
require_once '../../../../config/database.php';

// Test cập nhật price
$test_price = "800000";
$clean_price = str_replace(',', '', $test_price);
$final_price = floatval($clean_price);

echo "Test Price Processing:<br>";
echo "Input: $test_price<br>";
echo "Clean: $clean_price<br>";
echo "Final: $final_price<br>";
echo "Type: " . gettype($final_price) . "<br><br>";

// Test với formatted price
$test_price_formatted = "800,000";
$clean_price_formatted = str_replace(',', '', $test_price_formatted);
$final_price_formatted = floatval($clean_price_formatted);

echo "Test Formatted Price:<br>";
echo "Input: $test_price_formatted<br>";
echo "Clean: $clean_price_formatted<br>";
echo "Final: $final_price_formatted<br>";
echo "Type: " . gettype($final_price_formatted) . "<br><br>";

// Test database update
$product_id = 1; // Test với product ID 1
$sql = "UPDATE products SET price = ? WHERE product_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("di", $final_price_formatted, $product_id);
    if ($stmt->execute()) {
        echo "✅ Database update successful! Price $final_price_formatted saved for product $product_id<br>";
        
        // Kiểm tra giá trị đã lưu
        $check_sql = "SELECT price FROM products WHERE product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        echo "✅ Saved price in DB: " . $row['price'] . "<br>";
    } else {
        echo "❌ Database update failed: " . $stmt->error . "<br>";
    }
} else {
    echo "❌ Prepare failed: " . $conn->error . "<br>";
}
?> 