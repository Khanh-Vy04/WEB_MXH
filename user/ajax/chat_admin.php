<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng chat']);
    exit();
}

$current_user = getCurrentUser();
$user_id = $current_user['user_id'];

// Lấy action từ request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_latest_session':
        getLatestSession($conn, $user_id);
        break;
    
    case 'send_message':
        sendMessage($conn, $user_id);
        break;
        
    case 'get_messages':
        getMessages($conn, $user_id);
        break;
        
    case 'get_sessions':
        getSessions($conn, $user_id);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

// Hàm lấy session chat gần nhất
function getLatestSession($conn, $user_id) {
    try {
        // Tìm session chat gần nhất của user
        $sql = "SELECT DISTINCT support_id, MAX(created_at) as last_message
                FROM support_replies 
                WHERE user_id = ? 
                GROUP BY support_id 
                ORDER BY last_message DESC 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            echo json_encode([
                'success' => true, 
                'support_id' => $session['support_id']
            ]);
        } else {
            // Chưa có session nào
            echo json_encode([
                'success' => true, 
                'support_id' => null
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

// Hàm tạo support_id mới
function generateSupportId($user_id) {
    // Tạo support_id từ timestamp + random để đảm bảo tính duy nhất
    $timestamp = time();
    $random = rand(10, 99); // 2 chữ số random
    
    // Lấy 7 chữ số cuối của timestamp + 2 chữ số random = 9 chữ số
    $support_id = intval(substr($timestamp, -7) . $random);
    
    // Đảm bảo support_id không vượt quá giới hạn int(11)
    if ($support_id > 2147483647) {
        $support_id = intval(substr($timestamp, -8));
    }
    
    return $support_id;
}

// Hàm gửi tin nhắn
function sendMessage($conn, $user_id) {
    try {
        $support_id = $_POST['support_id'] ?? null;
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
            return;
        }
        
        // Tạo support_id mới nếu chưa có
        if (empty($support_id)) {
            $support_id = generateSupportId($user_id);
        }
        
        // Thêm tin nhắn user vào database
        $sql = "INSERT INTO support_replies (support_id, user_id, reply_message, is_customer_reply) 
                VALUES (?, ?, ?, 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $support_id, $user_id, $message);
        
        if ($stmt->execute()) {
            $reply_id = $conn->insert_id;
            
            // Kiểm tra xem đây có phải tin nhắn đầu tiên của session không
            $count_sql = "SELECT COUNT(*) as count FROM support_replies WHERE support_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $support_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count = $count_result->fetch_assoc()['count'];
            
            $response = [
                'success' => true,
                'reply_id' => $reply_id,
                'support_id' => $support_id,
                'message' => 'Tin nhắn đã được gửi',
                'is_first_message' => $count == 1
            ];
            
            // Nếu là tin nhắn đầu tiên, tự động gửi tin nhắn chào mừng
            if ($count == 1) {
                $welcome_message = "Cảm ơn bạn đã liên hệ với AuraDisc! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi sớm nhất. Vui lòng đợi trong giây lát.";
                
                $welcome_sql = "INSERT INTO support_replies (support_id, reply_message, is_customer_reply) 
                               VALUES (?, ?, 0)";
                
                $welcome_stmt = $conn->prepare($welcome_sql);
                $welcome_stmt->bind_param("is", $support_id, $welcome_message);
                $welcome_stmt->execute();
                
                $response['auto_reply'] = [
                    'reply_id' => $conn->insert_id,
                    'message' => $welcome_message,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode($response);
        } else {
            throw new Exception('Không thể gửi tin nhắn');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

// Hàm lấy tin nhắn
function getMessages($conn, $user_id) {
    try {
        $support_id = $_GET['support_id'] ?? '';
        
        if (empty($support_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu support_id']);
            return;
        }
        
        // Lấy tất cả tin nhắn của session này
        $sql = "SELECT reply_id, reply_message, is_customer_reply, created_at,
                       CASE WHEN is_customer_reply = 1 THEN 'user' ELSE 'admin' END as sender
                FROM support_replies 
                WHERE support_id = ? 
                ORDER BY created_at ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $support_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'reply_id' => $row['reply_id'],
                'text' => $row['reply_message'],
                'sender' => $row['sender'],
                'time' => date('H:i', strtotime($row['created_at'])),
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

// Hàm lấy danh sách sessions
function getSessions($conn, $user_id) {
    try {
        $sql = "SELECT support_id, 
                       MIN(created_at) as started_at,
                       MAX(created_at) as last_message,
                       COUNT(*) as message_count,
                       (SELECT reply_message FROM support_replies sr2 
                        WHERE sr2.support_id = sr.support_id 
                        ORDER BY created_at DESC LIMIT 1) as last_message_text
                FROM support_replies sr
                WHERE user_id = ?
                GROUP BY support_id
                ORDER BY last_message DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = [
                'support_id' => $row['support_id'],
                'started_at' => date('d/m/Y H:i', strtotime($row['started_at'])),
                'last_message' => date('d/m/Y H:i', strtotime($row['last_message'])),
                'message_count' => $row['message_count'],
                'last_message_text' => substr($row['last_message_text'], 0, 50) . '...'
            ];
        }
        
        echo json_encode(['success' => true, 'sessions' => $sessions]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

$conn->close();
?> 