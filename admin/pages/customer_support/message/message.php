<?php
$currentPage = 'messages';
require_once __DIR__.'/../../../../config/database.php';

// Lấy danh sách sessions từ database
function getChatSessions($conn) {
    $sql = "SELECT 
                sr.support_id,
                u.full_name as customer_name,
                u.user_id,
                COUNT(sr.reply_id) as message_count,
                MAX(sr.created_at) as last_message_time,
                (SELECT reply_message FROM support_replies sr2 
                 WHERE sr2.support_id = sr.support_id 
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT is_customer_reply FROM support_replies sr3 
                 WHERE sr3.support_id = sr.support_id 
                 ORDER BY created_at DESC LIMIT 1) as last_message_is_customer,
                SUM(CASE WHEN sr.is_customer_reply = 1 AND sr.created_at > COALESCE(
                    (SELECT MAX(created_at) FROM support_replies sr3 
                     WHERE sr3.support_id = sr.support_id AND sr3.is_customer_reply = 0), '2000-01-01'
                ) THEN 1 ELSE 0 END) as unread_count
            FROM support_replies sr
            LEFT JOIN users u ON sr.user_id = u.user_id
            WHERE sr.user_id IS NOT NULL
            GROUP BY sr.support_id, u.full_name, u.user_id
            ORDER BY last_message_time DESC";
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Lấy tin nhắn theo support_id
function getChatMessages($conn, $support_id) {
    $sql = "SELECT 
                sr.reply_id,
                sr.reply_message,
                sr.is_customer_reply,
                sr.created_at,
                u.full_name as customer_name
            FROM support_replies sr
            LEFT JOIN users u ON sr.user_id = u.user_id
            WHERE sr.support_id = ?
            ORDER BY sr.created_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $support_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Xử lý gửi tin nhắn admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $support_id = intval($_POST['support_id']);
    $message = trim($_POST['message']);
    
    if (!empty($message) && $support_id > 0) {
        $sql = "INSERT INTO support_replies (support_id, reply_message, is_customer_reply) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $support_id, $message);
        
        if ($stmt->execute()) {
            header("Location: message.php?id=" . $support_id . "&success=1");
            exit();
        }
    }
}

// Lấy dữ liệu
$sessions = getChatSessions($conn);
$selected_id = isset($_GET['id']) ? intval($_GET['id']) : ($sessions[0]['support_id'] ?? 0);
$current_messages = $selected_id ? getChatMessages($conn, $selected_id) : [];
$current_session = null;

foreach ($sessions as $session) {
    if ($session['support_id'] == $selected_id) {
        $current_session = $session;
        break;
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . ' giây trước';
    if ($diff < 3600) return floor($diff/60) . ' phút trước';
    if ($diff < 86400) return floor($diff/3600) . ' giờ trước';
    return floor($diff/86400) . ' ngày trước';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chat Support</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="message.css">
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>

    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>

        <div class="container-fluid pt-4 px-4" style="background:rgb(247, 251, 255); min-height: calc(100vh - 80px);">
            <!-- Header Section -->
            <div class="header-section mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0" style="color: #412d3b; font-weight: 700; font-size: 1.8rem;">
                        <i class="fas fa-comments" style="color: #deccca; margin-right: 10px;"></i>
                        Quản Lý Chat Support
                    </h2>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;"><?= count($sessions) ?> cuộc trò chuyện</span>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshPage()">
                            <i class="fas fa-sync-alt"></i> Làm mới
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded p-4 mb-4 shadow-sm" style="border: 1px solid #e9ecef;">
                
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check"></i> Tin nhắn đã được gửi thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row" style="height: 65vh;">
                    <div class="col-md-4 border-end">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0" style="color: #412d3b; font-weight: 700;">
                                <i class="fas fa-list" style="color: #deccca; margin-right: 8px;"></i> Danh sách chat
                            </h5>
                        </div>
                        
                        <div style="height: calc(100% - 50px); max-height: 320px; overflow-y: auto;">
                            <?php if (empty($sessions)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-comments fa-3x mb-3"></i>
                                    <p>Chưa có cuộc trò chuyện nào</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($sessions as $session): ?>
                                <div class="session-item p-3 mb-2 rounded <?= $session['support_id'] == $selected_id ? 'active' : '' ?>" 
                                     onclick="selectSession(<?= $session['support_id'] ?>)" style="cursor: pointer;">
                                    <div class="d-flex align-items-start">
                                        <div class="session-avatar me-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width:40px;height:40px;background:linear-gradient(135deg, #412d3b 0%, #deccca 100%);color:white;font-weight:700;font-size:0.9em;">
                                                <?= strtoupper(substr($session['customer_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 text-truncate" style="font-weight: 600; color: #412d3b; max-width: 150px;">
                                                    <?= htmlspecialchars($session['customer_name'] ?? 'Khách hàng') ?>
                                                </h6>
                                                <small class="text-muted" style="flex-shrink: 0;"><?= timeAgo($session['last_message_time']) ?></small>
                                            </div>
                                            
                                            <p class="mb-1 text-muted small text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars(substr($session['last_message'], 0, 35)) ?><?= strlen($session['last_message']) > 35 ? '...' : '' ?>
                                                <?php if ($session['last_message_is_customer'] == 1): ?>
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.6em;">chưa phản hồi</span>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small style="color:rgb(255, 243, 251); max-width: 90px; overflow: hidden; text-overflow: ellipsis;">
                                                    <i class="fas fa-hashtag"></i> <?= $session['support_id'] ?>
                                                </small>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge text-dark" style="background: #deccca; font-size: 0.6em;"><?= $session['message_count'] ?></span>
                                                    <?php if ($session['unread_count'] > 0): ?>
                                                        <span class="badge bg-danger" style="font-size: 0.6em;"><?= $session['unread_count'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-8 d-flex flex-column">
                        <?php if ($current_session): ?>
                            <div class="chat-header p-3 border-bottom" style="background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%); color: white;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width:40px;height:40px;background:rgba(255,255,255,0.2);color:white;font-weight:700;border:2px solid rgba(255,255,255,0.3);">
                                        <?= strtoupper(substr($current_session['customer_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0" style="font-weight: 600; color: white;">
                                            <?= htmlspecialchars($current_session['customer_name'] ?? 'Khách hàng') ?>
                                        </h6>
                                        <small style="color: rgba(255,255,255,0.8);">
                                            <i class="fas fa-hashtag"></i> Support ID: <?= $current_session['support_id'] ?>
                                            • <?= $current_session['message_count'] ?> tin nhắn
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($current_session['last_message_is_customer'] == 1): ?>
                                            <span class="badge bg-warning text-dark mb-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Chưa trả lời
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(255,255,255,0.2); color: white;">
                                                <i class="fas fa-check-circle me-1" style="font-size: 0.6em; color: #4caf50;"></i>
                                                Đã trả lời
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-messages flex-grow-1 p-3" id="chat-box" style="background:#f8f9fa; overflow-y: auto;">
                                <?php foreach($current_messages as $msg): ?>
                                    <div class="message-item mb-3 d-flex <?= $msg['is_customer_reply'] ? '' : 'justify-content-end' ?>">
                                        <?php if ($msg['is_customer_reply']): ?>
                                            <div class="message-avatar me-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width:35px;height:35px;background:linear-gradient(135deg, #412d3b 0%, #deccca 100%);color:white;font-weight:600;font-size:0.9em;">
                                                    <?= strtoupper(substr($msg['customer_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div class="message-content">
                                                <div class="message-bubble customer-message">
                                                    <?= nl2br(htmlspecialchars($msg['reply_message'])) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('H:i d/m/Y', strtotime($msg['created_at'])) ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <div class="message-content text-end">
                                                <div class="message-bubble admin-message">
                                                    <?= nl2br(htmlspecialchars($msg['reply_message'])) ?>
                                                </div>
                                                <small class="text-muted">
                                                    Admin • <?= date('H:i d/m/Y', strtotime($msg['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="message-avatar ms-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width:35px;height:35px;background:linear-gradient(135deg, #deccca 0%, #412d3b 100%);color:white;font-weight:600;">
                                                    A
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="chat-input border-top p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <form method="POST" class="d-flex align-items-end gap-2">
                                    <input type="hidden" name="action" value="send_message">
                                    <input type="hidden" name="support_id" value="<?= $current_session['support_id'] ?>">
                                    
                                    <div class="flex-grow-1">
                                        <textarea name="message" class="form-control" rows="2" 
                                                placeholder="Nhập tin nhắn phản hồi khách hàng..." 
                                                required style="resize: none; border: 2px solid #e9ecef; border-radius: 15px; padding: 12px 16px; background: white;"></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #412d3b 0%, #deccca 100%); color: white; border: none; border-radius: 12px; padding: 10px 20px; font-weight: 600;">
                                        <i class="fas fa-paper-plane"></i> Gửi
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center text-muted">
                                    <i class="fas fa-comments fa-4x mb-4"></i>
                                    <h5>Chọn một cuộc trò chuyện để bắt đầu</h5>
                                    <p>Chọn từ danh sách bên trái để xem và trả lời tin nhắn</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
<script>
function selectSession(supportId) {
    window.location.href = 'message.php?id=' + supportId;
}

function refreshPage() {
    window.location.reload();
}

window.onload = function() {
    var chatBox = document.getElementById('chat-box');
    if(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

setInterval(function() {
    if (window.location.href.indexOf('?id=') > -1) {
        window.location.reload();
    }
}, 30000);

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="message"]');
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
});
</script>
</body>
</html> 