<?php
require_once '../../config/database.php';
require_once '../includes/session.php';


// Lấy action từ request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'send_message':
        sendMessageToBot();
        break;
        
    case 'get_conversation':
        getBotConversation();
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

// Hàm lấy thông tin inventory từ database
function getInventoryData($conn) {
    $inventory = [
        'products' => [],
        'accessories' => []
    ];
    
    try {
        // Lấy sản phẩm album/đĩa nhạc
        $products_sql = "SELECT p.product_name, p.price, p.stock, p.description, 
                                g.genre_name, a.artist_name
                         FROM products p
                         LEFT JOIN genres g ON p.genre_id = g.genre_id
                         LEFT JOIN artist_products ap ON p.product_id = ap.product_id
                         LEFT JOIN artists a ON ap.artist_id = a.artist_id
                         WHERE p.stock > 0
                         ORDER BY p.product_name";
        
        $result = $conn->query($products_sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $inventory['products'][] = [
                    'name' => $row['product_name'],
                    'artist' => $row['artist_name'] ?? 'Unknown Artist',
                    'genre' => $row['genre_name'] ?? 'Chưa phân loại',
                    'price' => $row['price'],
                    'stock' => $row['stock'],
                    'description' => $row['description']
                ];
            }
        }
        
        // Lấy phụ kiện âm nhạc
        $accessories_sql = "SELECT accessory_name, price, stock, description
                           FROM accessories 
                           WHERE stock > 0
                           ORDER BY accessory_name";
        
        $result = $conn->query($accessories_sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $inventory['accessories'][] = [
                    'name' => $row['accessory_name'],
                    'price' => $row['price'],
                    'stock' => $row['stock'],
                    'description' => $row['description']
                ];
            }
        }
        
    } catch (Exception $e) {
        error_log("Error getting inventory: " . $e->getMessage());
    }
    
    return $inventory;
}

// Hàm gọi ChatGPT API
function callChatGPT($message, $conversation_history = []) {
    global $conn;
    
    try {
        $inventory = getInventoryData($conn);
        
        // Tạo system message với thông tin inventory
        $system_message = "Bạn là AuraBot - trợ lý AI chuyên nghiệp của AuraDisc, cửa hàng đĩa nhạc và phụ kiện âm nhạc hàng đầu Việt Nam.

🎵 THÔNG TIN SẢN PHẨM HIỆN CÓ:

📀 ALBUM/ĐĨA NHẠC:
";
        
        foreach ($inventory['products'] as $product) {
            $system_message .= "• {$product['name']} - {$product['artist']} ({$product['genre']}) - \${$product['price']} - Còn {$product['stock']} sản phẩm\n";
        }
        
        $system_message .= "\n🎧 PHỤ KIỆN ÂM NHẠC:\n";
        
        foreach ($inventory['accessories'] as $accessory) {
            $system_message .= "• {$accessory['name']} - \${$accessory['price']} - Còn {$accessory['stock']} sản phẩm\n";
        }
        
        $system_message .= "\n🤖 VAI TRÒ CỦA BẠN:
✅ Tư vấn sản phẩm âm nhạc dựa trên sở thích khách hàng
✅ Cung cấp thông tin tồn kho chính xác từ database
✅ Gợi ý combo sản phẩm hợp lý (album + phụ kiện)
✅ Giải đáp thắc mắc về giá cả, chất lượng sản phẩm
✅ Hướng dẫn đặt hàng và thanh toán
✅ Trả lời bằng tiếng Việt, thân thiện và chuyên nghiệp

❌ KHÔNG ĐƯỢC:
❌ Bán sản phẩm không có trong kho
❌ Đưa ra giá sai lệch với database
❌ Cam kết giao hàng cụ thể (chuyển cho admin)
❌ Xử lý thanh toán trực tiếp

💡 LUÔN NHỚ:
- Đề cập giá và số lượng còn lại
- Khuyến khích khách hàng đặt hàng nếu quan tâm
- Nếu khách hỏi về giao hàng/thanh toán phức tạp, gợi ý chuyển sang chat admin
- Giữ phong cách trẻ trung, năng động như một cửa hàng nhạc hiện đại";

        // Tạo messages array
        $messages = [
            [
                'role' => 'system',
                'content' => $system_message
            ]
        ];
        
        // Thêm lịch sử hội thoại
        foreach ($conversation_history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        
        // Thêm tin nhắn hiện tại
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $data = [
            'model' => 'gpt-4',
            'messages' => $messages,
            'max_tokens' => 600,
            'temperature' => 0.8
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, OPENAI_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }
        }
        
        // Fallback message nếu API lỗi
        return "🤖 Xin chào! Tôi là AuraBot - trợ lý AI của AuraDisc. Chúng tôi chuyên cung cấp đĩa nhạc và phụ kiện âm nhạc chất lượng cao từ các nghệ sĩ thế giới và Việt Nam. Vui lòng cho tôi biết bạn đang tìm kiếm loại nhạc nào để tôi có thể tư vấn phù hợp nhất! 🎵";
        
    } catch (Exception $e) {
        error_log("ChatGPT API Error: " . $e->getMessage());
        return "🤖 Xin chào! Tôi là AuraBot của AuraDisc. Hiện tại tôi gặp chút vấn đề kỹ thuật, nhưng vẫn có thể hỗ trợ bạn tìm kiếm đĩa nhạc và phụ kiện âm nhạc. Bạn có thể chia sẻ sở thích âm nhạc không? 🎧";
    }
}

// Hàm gửi tin nhắn tới bot
function sendMessageToBot() {
    try {
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
            return;
        }
        
        // Lấy lịch sử hội thoại từ session (session đã được khởi tạo trong session.php)
        if (!isset($_SESSION['bot_conversation'])) {
            $_SESSION['bot_conversation'] = [];
        }
        
        $conversation_history = $_SESSION['bot_conversation'];
        
        // Gọi ChatGPT API
        $bot_response = callChatGPT($message, $conversation_history);
        
        // Lưu tin nhắn vào session
        $_SESSION['bot_conversation'][] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $_SESSION['bot_conversation'][] = [
            'role' => 'assistant', 
            'content' => $bot_response
        ];
        
        // Giới hạn lịch sử tối đa 20 tin nhắn để tránh session quá lớn
        if (count($_SESSION['bot_conversation']) > 20) {
            $_SESSION['bot_conversation'] = array_slice($_SESSION['bot_conversation'], -20);
        }
        
        echo json_encode([
            'success' => true,
            'user_message' => $message,
            'bot_response' => $bot_response,
            'timestamp' => date('H:i')
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

// Hàm lấy lịch sử hội thoại với bot
function getBotConversation() {
    try {
        // session đã được khởi tạo trong session.php
        
        $conversation = $_SESSION['bot_conversation'] ?? [];
        $messages = [];
        
        for ($i = 0; $i < count($conversation); $i += 2) {
            if (isset($conversation[$i]) && isset($conversation[$i + 1])) {
                $messages[] = [
                    'user_message' => $conversation[$i]['content'],
                    'bot_response' => $conversation[$i + 1]['content'],
                    'timestamp' => date('H:i')
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
    }
}

$conn->close();
?> 