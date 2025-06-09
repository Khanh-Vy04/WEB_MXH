<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';

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
        
        if ($amount <= 0) {
            throw new Exception('Số tiền nạp phải lớn hơn 0');
        }
        
        // Bắt đầu transaction
        $conn->autocommit(false);
        
        try {
            // Cập nhật số dư
            $update_query = "UPDATE users SET balance = balance + ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("di", $amount, $user_id);
            $update_stmt->execute();
            
            // Lấy số dư mới
            $balance_query = "SELECT balance FROM users WHERE user_id = ?";
            $balance_stmt = $conn->prepare($balance_query);
            $balance_stmt->bind_param("i", $user_id);
            $balance_stmt->execute();
            $result = $balance_stmt->get_result();
            $new_balance = $result->fetch_assoc()['balance'];
            
            $conn->commit();
            $conn->autocommit(true);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Nạp tiền thành công!',
                'new_balance' => floatval($new_balance),
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

// Lưu lịch sử giao dịch (tạo bảng nếu chưa có)
function logTransaction($user_id, $type, $amount, $description) {
    global $pdo;
    
    try {
        // Tạo bảng transactions nếu chưa có
        $create_table_query = "CREATE TABLE IF NOT EXISTS wallet_transactions (
            transaction_id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11) NOT NULL,
            transaction_type ENUM('add', 'deduct') NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            description TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (transaction_id),
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            INDEX idx_user_transactions (user_id),
            INDEX idx_transaction_date (created_at)
        )";
        $pdo->exec($create_table_query);
        
        // Insert transaction
        $insert_query = "INSERT INTO wallet_transactions (user_id, transaction_type, amount, description) 
                        VALUES (?, ?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute([$user_id, $type, $amount, $description]);
        
    } catch (Exception $e) {
        // Log error nhưng không throw để không ảnh hưởng transaction chính
        error_log("Failed to log transaction: " . $e->getMessage());
    }
}

// Lấy lịch sử giao dịch
function getTransactionHistory() {
    global $pdo;
    
    try {
        $user_id = $_SESSION['user_id'];
        $limit = intval($_POST['limit'] ?? 20);
        $offset = intval($_POST['offset'] ?? 0);
        
        // Tạo bảng nếu chưa có
        logTransaction($user_id, 'add', 0, ''); // Chỉ để tạo bảng
        
        $query = "SELECT transaction_id, transaction_type, amount, description, created_at 
                 FROM wallet_transactions 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $limit, $offset]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format dữ liệu
        foreach ($transactions as &$transaction) {
            $transaction['formatted_amount'] = number_format($transaction['amount'], 0, ',', '.') . '₫';
            $transaction['created_at_formatted'] = date('d/m/Y H:i', strtotime($transaction['created_at']));
        }
        
        echo json_encode(['success' => true, 'transactions' => $transactions]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Format tiền tệ VND
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . '₫';
}
?> 