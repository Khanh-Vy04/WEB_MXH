<?php
// Working version based on successful testing

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default user if not logged in (for testing)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 4;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'get_invoices') {
    try {
        require_once '../config/database.php';
        
        $sql = "SELECT 
                    o.order_id as id,
                    o.order_date as created_at,
                    o.total_amount as subtotal,
                    o.final_amount as total_amount,
                    o.stage_id as status,
                    o.voucher_discount as discount_amount
                FROM orders o 
                WHERE o.buyer_id = ?
                ORDER BY o.order_date DESC 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $row['created_at'] = date('d/m/Y H:i', strtotime($row['created_at']));
            $row['payment_method'] = 'Ví điện tử';
            $row['total_items'] = 1;
            $row['items_preview'] = 'Loading...';
            $invoices[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'invoices' => $invoices,
            'total' => count($invoices),
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 1,
                'total_items' => count($invoices),
                'items_per_page' => 10
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Action không hợp lệ!',
        'debug' => [
            'received_action' => $action,
            'post_data' => $_POST
        ]
    ]);
}
?> 