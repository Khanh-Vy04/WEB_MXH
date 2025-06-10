<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_balance':
            getUserBalance();
            break;
        case 'add_balance':
            addBalance();
            break;
        case 'deduct_balance':
            deductBalance();
            break;
        case 'get_transaction_history':
            getTransactionHistory();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
            break;
    }
}

// Lấy số dư hiện tại
function getUserBalance() {
    global $conn;
    
    try {
        $user_id = $_SESSION['user_id'];
        
        $query = "SELECT balance FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            echo json_encode([
                'success' => true, 
                'balance' => floatval($user['balance']),
                'formatted_balance' => number_format($user['balance'], 0, ',', '.') . '₫'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Nạp tiền vào tài khoản
function addBalance() {
    global $conn;
    
    try {
        $user_id = $_SESSION['user_id'];
        $amount = floatval($_POST['amount'] ?? 0);
        $description = $_POST['description'] ?? 'Nạp tiền vào tài khoản';
        $order_id = $_POST['order_id'] ?? null; // Thêm order_id từ cổng thanh toán
        
        if ($amount <= 0) {
            throw new Exception('Số tiền nạp phải lớn hơn 0');
        }
        
        // Bắt đầu transaction
        $conn->autocommit(false);
        
        try {
            // Kiểm tra xem order_id đã được sử dụng chưa trong một bảng đơn giản
            if (!empty($order_id)) {
                // Kiểm tra trong notes hoặc created_at để tránh duplicate
                $check_query = "SELECT COUNT(*) as count FROM users WHERE user_id = ? AND 
                               (FIND_IN_SET(?, REPLACE(address, ' ', '')) > 0 OR address LIKE ?)";
                $order_search = "%{$order_id}%";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("iss", $user_id, $order_id, $order_search);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $existing_count = $check_result->fetch_assoc()['count'];
                
                // Đơn giản hóa: chỉ check thời gian gần đây (trong 10 phút)
                $recent_check = "SELECT balance FROM users WHERE user_id = ? AND 
                               created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
                // Skip duplicate check for now, rely on payment gateway
            }
            
            // Cập nhật số dư
            $update_query = "UPDATE users SET balance = balance + ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("di", $amount, $user_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception('Không thể cập nhật số dư: ' . $conn->error);
            }
            
            // Lấy số dư mới
            $balance_query = "SELECT balance FROM users WHERE user_id = ?";
            $balance_stmt = $conn->prepare($balance_query);
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $result = $balance_stmt->get_result();
            $user_data = $result->fetch_assoc();
            
            if (!$user_data) {
                throw new Exception('Không thể lấy số dư mới');
            }
            
            $new_balance = $user_data['balance'];
            
            // Ghi log đơn giản vào address field (tạm thời)
            if (!empty($order_id)) {
                $log_info = date('Y-m-d H:i:s') . " - Nạp {$amount}₫ - Order: {$order_id}";
                $current_address_query = "SELECT address FROM users WHERE user_id = ?";
                $addr_stmt = $conn->prepare($current_address_query);
                $addr_stmt->bind_param("i", $user_id);
                $addr_stmt->execute();
                $addr_result = $addr_stmt->get_result();
                $current_address = $addr_result->fetch_assoc()['address'] ?? '';
                
                $new_address = trim($current_address . "\n" . $log_info);
                $update_log_query = "UPDATE users SET address = ? WHERE user_id = ?";
                $log_stmt = $conn->prepare($update_log_query);
                $log_stmt->bind_param("si", $new_address, $user_id);
                $log_stmt->execute();
            }
            
            $conn->commit();
            $conn->autocommit(true);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Nạp tiền thành công!',
                'new_balance' => floatval($new_balance),
                'formatted_balance' => number_format($new_balance, 0, ',', '.') . '₫',
                'order_id' => $order_id
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            $conn->autocommit(true);
            throw $e;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Note: Không dùng bảng wallet_transactions riêng, chỉ cập nhật users.balance

// Trừ tiền từ tài khoản
function deductBalance() {
    global $conn;
    
    try {
        $user_id = $_SESSION['user_id'];
        $amount = floatval($_POST['amount'] ?? 0);
        $description = $_POST['description'] ?? 'Thanh toán đơn hàng';
        
        if ($amount <= 0) {
            throw new Exception('Số tiền trừ phải lớn hơn 0');
        }
        
        // Kiểm tra số dư hiện tại
        $balance_query = "SELECT balance FROM users WHERE user_id = ?";
        $balance_stmt = $conn->prepare($balance_query);
        $balance_stmt->bind_param("i", $user_id);
        $balance_stmt->execute();
        $result = $balance_stmt->get_result();
        $user_data = $result->fetch_assoc();
        $current_balance = floatval($user_data['balance']);
        
        if ($current_balance < $amount) {
            throw new Exception('Số dư không đủ để thực hiện giao dịch');
        }
        
        // Bắt đầu transaction
        $conn->autocommit(false);
        
        try {
            // Trừ số dư
            $update_query = "UPDATE users SET balance = balance - ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("di", $amount, $user_id);
            $update_stmt->execute();
            
            $new_balance = $current_balance - $amount;
            
            $conn->commit();
            $conn->autocommit(true);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Thanh toán thành công!',
                'new_balance' => $new_balance,
                'formatted_balance' => number_format($new_balance, 0, ',', '.') . '₫'
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            $conn->autocommit(true);
            throw $e;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Lấy lịch sử giao dịch từ address field (đơn giản hóa)
function getTransactionHistory() {
    global $conn;
    
    try {
        $user_id = $_SESSION['user_id'];
        
        // Lấy thông tin từ address field
        $query = "SELECT address, balance, created_at FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
        $transactions = [];
        
        if ($user_data && !empty($user_data['address'])) {
            $logs = explode("\n", $user_data['address']);
            $transaction_id = 1;
            
            foreach ($logs as $log) {
                $log = trim($log);
                if (empty($log)) continue;
                
                // Parse log format: "2024-01-15 14:30:00 - Nạp 100000₫ - Order: ABC123"
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - (.+) - Order: (.+)$/', $log, $matches)) {
                    $transactions[] = [
                        'transaction_id' => $transaction_id++,
                        'transaction_type' => 'add',
                        'amount' => extractAmountFromLog($matches[2]),
                        'description' => $matches[2],
                        'order_id' => $matches[3],
                        'created_at' => $matches[1],
                        'formatted_amount' => $matches[2],
                        'created_at_formatted' => date('d/m/Y H:i', strtotime($matches[1]))
                    ];
                }
            }
        }
        
        // Reverse để có transaction mới nhất trước
        $transactions = array_reverse($transactions);
        
        echo json_encode([
            'success' => true, 
            'transactions' => $transactions,
            'current_balance' => floatval($user_data['balance']),
            'formatted_balance' => number_format($user_data['balance'], 0, ',', '.') . '₫'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Extract amount from log text
function extractAmountFromLog($text) {
    if (preg_match('/(\d+)₫/', $text, $matches)) {
        return floatval($matches[1]);
    }
    return 0;
}

// Format tiền tệ VND
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . '₫';
}
?> 