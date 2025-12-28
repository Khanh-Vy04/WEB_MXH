<?php
require_once '../config/database.php';
require_once 'includes/session.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$current_user = getCurrentUser();

// Mock data (trong thực tế sẽ lấy từ DB dựa trên product_id/order_id)
$freelancer_name = "Ai Linh";
$project_code = "V4UXJY7Z";
$project_title = "NHẬN ĐÁNH VĂN BẢN, NHẬP LIỆU THEO YÊU CẦU";
$project_image = "assets/images/clients/c1.png"; // Placeholder

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - AuraDisc</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Bootstrap 5 (Sử dụng Bootstrap 5 cho giao diện hiện đại này) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .chat-header {
            background: #fff;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid #e4e6eb;
            flex-shrink: 0;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-area img {
            height: 35px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #412d3b; /* Sử dụng màu chủ đạo của web */
            text-decoration: none;
        }
        
        .logo-area:hover .logo-text {
            color: #412d3b;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            background: #f0f2f5;
            color: #65676b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-btn:hover {
            background: #e4e6eb;
            color: #050505;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: 20px;
            border-left: 1px solid #e4e6eb;
            padding-left: 20px;
        }

        .user-actions i {
            font-size: 1.2rem;
            color: #65676b;
            cursor: pointer;
        }

        .user-avatar-small {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Main Layout */
        .chat-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            background: #fff;
        }

        /* Left Sidebar - Conversation List */
        .left-sidebar {
            width: 350px;
            border-right: 1px solid #e4e6eb;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .search-box {
            padding: 15px;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #65676b;
        }

        .search-input {
            width: 100%;
            padding: 8px 12px 8px 35px;
            background: #f0f2f5;
            border: none;
            border-radius: 20px;
            outline: none;
        }

        .filter-bar {
            padding: 0 15px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f0f2f5;
        }

        .toggle-unread {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #65676b;
        }

        .form-switch .form-check-input {
            cursor: pointer;
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            display: flex;
            padding: 12px 15px;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }

        .conversation-item:hover, .conversation-item.active {
            background: #f0f2f5;
        }

        .conversation-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #0d6efd;
        }

        .conv-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
        }

        .conv-info {
            flex: 1;
            min-width: 0;
        }

        .conv-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .conv-name {
            font-weight: 600;
            font-size: 15px;
            color: #050505;
        }

        .conv-project-code {
            color: #65676b;
            font-size: 12px;
        }

        .conv-time {
            font-size: 12px;
            color: #65676b;
        }

        .conv-preview {
            color: #65676b;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Center - Chat Area */
        .main-chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            min-width: 0;
        }

        .chat-area-header {
            height: 60px;
            padding: 0 20px;
            border-bottom: 1px solid #e4e6eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-area-header .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .chat-area-header .name {
            font-weight: 600;
            font-size: 16px;
        }

        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            position: relative;
        }

        .message-received {
            align-self: flex-start;
            background: #fff;
            color: #050505;
            border-bottom-left-radius: 4px;
        }

        .message-sent {
            align-self: flex-end;
            background: #0084ff;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .input-area {
            padding: 15px;
            border-top: 1px solid #e4e6eb;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-actions i {
            font-size: 1.2rem;
            color: #65676b;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .input-actions i:hover {
            background: #f0f2f5;
        }

        .message-input {
            flex: 1;
            background: #f0f2f5;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            outline: none;
            max-height: 100px;
            overflow-y: auto;
        }

        .send-btn {
            color: #0084ff;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px;
        }

        /* Right Sidebar - Info */
        .right-sidebar {
            width: 350px;
            border-left: 1px solid #e4e6eb;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .info-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e4e6eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }

        .info-content {
            padding: 20px;
        }

        .trust-banner {
            background: #e7f3ff;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            font-size: 13px;
            color: #0c5460;
        }

        .project-card {
            border: 1px solid #e4e6eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .project-thumb {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .project-details-title {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .accordion-item {
            border: none;
            border-bottom: 1px solid #f0f2f5;
        }

        .accordion-button {
            padding: 15px 0;
            font-weight: 600;
            color: #050505;
            box-shadow: none !important;
            background: transparent !important;
        }

        .accordion-body {
            padding: 0 0 15px 0;
        }

        .project-info-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .text-label {
            color: #65676b;
        }

        .file-empty {
            background: #f7f8fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #65676b;
            font-size: 14px;
        }

        .report-btn {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #dc3545;
            color: #dc3545;
            background: transparent;
            padding: 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .report-btn:hover {
            background: #dc3545;
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .right-sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .left-sidebar {
                display: none;
            }
            .chat-container.list-view .main-chat-area {
                display: none;
            }
            .chat-container.list-view .left-sidebar {
                display: flex;
                width: 100%;
            }
            
            .header-actions .nav-btn span {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="chat-header">
        <div class="d-flex align-items-center gap-4">
            <a href="index.php" class="logo-area">
                <img src="assets/logo/favicon.png" alt="UniWork Logo">
                <span class="logo-text">UniWork</span>
            </a>
            
            <div class="header-actions">
                <a href="index.php" class="nav-btn">
                    <i class="fas fa-home"></i>
                    <span>Trang chủ</span>
                </a>
                
                <?php if($product_id > 0): ?>
                <a href="product-detail.php?id=<?php echo $product_id; ?>" class="nav-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Quay lại dự án</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="user-actions">
            <i class="fas fa-bell"></i>
            <img src="<?php echo isset($current_user['image_url']) ? htmlspecialchars($current_user['image_url']) : 'assets/img/user.jpg'; ?>" 
                 class="user-avatar-small" 
                 onerror="this.src='https://via.placeholder.com/32'">
        </div>
    </header>

    <!-- Main Container -->
    <div class="chat-container">
        
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="search-box">
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" placeholder="Tìm kiếm đơn hàng">
                </div>
            </div>
            
            <div class="filter-bar">
                <div class="toggle-unread form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="unreadOnly">
                    <label class="form-check-label" for="unreadOnly">Chỉ chưa đọc</label>
                </div>
                <select class="form-select form-select-sm" style="width: auto; border: none; background-color: transparent;">
                    <option>Tất cả</option>
                </select>
            </div>

            <div class="conversation-list">
                <!-- Active Conversation Item -->
                <div class="conversation-item active">
                    <img src="assets/images/clients/c1.png" class="conv-avatar">
                    <div class="conv-info">
                        <div class="conv-top">
                            <span class="conv-name">Ai Linh</span>
                            <span class="conv-time">17:58</span>
                        </div>
                        <div class="conv-project-code">#V4UXJY7Z</div>
                        <div class="conv-preview">Bước 1: Trò chuyện với Freelancer Bắt đầu...</div>
                    </div>
                </div>

                <!-- Another Item -->
                <div class="conversation-item">
                    <img src="https://via.placeholder.com/48" class="conv-avatar">
                    <div class="conv-info">
                        <div class="conv-top">
                            <span class="conv-name">Minh Hoàng</span>
                            <span class="conv-time">Hôm qua</span>
                        </div>
                        <div class="conv-project-code">#A8B9C2D3</div>
                        <div class="conv-preview">Đã gửi file thiết kế demo</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="main-chat-area">
            <div class="chat-area-header">
                <img src="assets/images/clients/c1.png" class="avatar">
                <span class="name">Ai Linh</span>
            </div>

            <div class="messages-container" id="messagesList">
                <div class="text-center text-muted small my-3">Bắt đầu trò chuyện</div>
                <!-- Messages will be appended here -->
            </div>

            <div class="input-area">
                <div class="input-actions">
                    <i class="far fa-smile" title="Emoji"></i>
                    <i class="fas fa-paperclip" title="Đính kèm"></i>
                    <i class="far fa-image" title="Hình ảnh"></i>
                </div>
                <input type="text" class="message-input" placeholder="Nhập tin nhắn..." id="messageInput">
                <div class="send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="info-header">
                <span>Chi tiết</span>
                <i class="fas fa-times cursor-pointer"></i>
            </div>

            <div class="info-content">
                <div class="trust-banner">
                    <i class="fas fa-shield-alt mt-1"></i>
                    <div>An toàn hơn khi thanh toán qua hệ thống trung gian</div>
                </div>

                <div class="project-card">
                    <img src="assets/images/features/f1.jpg" class="project-thumb" onerror="this.src='https://via.placeholder.com/60'">
                    <div class="project-details-title">NHẬN ĐÁNH VĂN BẢN, NHẬP LIỆU THEO YÊU CẦU</div>
                </div>

                <div class="accordion" id="chatAccordion">
                    <!-- Project Info -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo">
                                Thông tin dự án
                            </button>
                        </h2>
                        <div id="collapseInfo" class="accordion-collapse collapse show" data-bs-parent="#chatAccordion">
                            <div class="accordion-body">
                                <div class="project-info-row">
                                    <span class="text-label">Mã dự án</span>
                                    <span class="fw-bold">
                                        V4UXJY7Z 
                                        <i class="far fa-copy ms-1 text-muted cursor-pointer"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiles">
                                Tài liệu dự án
                            </button>
                        </h2>
                        <div id="collapseFiles" class="accordion-collapse collapse show" data-bs-parent="#chatAccordion">
                            <div class="accordion-body">
                                <div class="file-empty">
                                    Chưa có tài liệu
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="report-btn">
                    <i class="fas fa-flag me-2"></i> Báo cáo
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function sendMessage() {
            var input = document.getElementById('messageInput');
            var text = input.value.trim();
            
            if (text) {
                var container = document.getElementById('messagesList');
                
                // Create user message bubble
                var msgDiv = document.createElement('div');
                msgDiv.className = 'message-bubble message-sent';
                msgDiv.innerText = text;
                
                container.appendChild(msgDiv);
                input.value = '';
                container.scrollTop = container.scrollHeight;

                // Simulate reply
                setTimeout(function() {
                    var replyDiv = document.createElement('div');
                    replyDiv.className = 'message-bubble message-received';
                    replyDiv.innerText = 'Cảm ơn bạn đã liên hệ. Mình có thể giúp gì cho bạn?';
                    container.appendChild(replyDiv);
                    container.scrollTop = container.scrollHeight;
                }, 1000);
            }
        }

        // Send on Enter key
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>
