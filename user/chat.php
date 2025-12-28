<?php
require_once '../config/database.php';
require_once 'includes/session.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$current_user = getCurrentUser();
$current_user_id = intval($current_user['user_id']); 

$freelancer_name = "Ai Linh";
$freelancer_id = 1;
$project_code = "V4UXJY7Z";
$project_title = "NHẬN ĐÁNH VĂN BẢN, NHẬP LIỆU THEO YÊU CẦU";
$project_image = "assets/images/clients/c1.png"; 
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 101; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - UniWork</title>
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Roboto', sans-serif; height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
        .chat-header { background: #fff; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; border-bottom: 1px solid #e4e6eb; flex-shrink: 0; }
        .logo-area { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-area img { height: 35px; }
        .logo-text { font-size: 20px; font-weight: 700; color: #412d3b; text-decoration: none; }
        .header-actions { display: flex; align-items: center; gap: 15px; }
        .nav-btn { display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; background: #f0f2f5; color: #65676b; text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .nav-btn:hover { background: #e4e6eb; color: #050505; }
        .user-actions { display: flex; align-items: center; gap: 20px; margin-left: 20px; border-left: 1px solid #e4e6eb; padding-left: 20px; }
        .user-avatar-small { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
        
        .chat-container { display: flex; flex: 1; overflow: hidden; background: #fff; }
        .left-sidebar { width: 350px; border-right: 1px solid #e4e6eb; display: flex; flex-direction: column; background: #fff; }
        .search-box { padding: 15px; }
        .search-input { width: 100%; padding: 8px 12px 8px 35px; background: #f0f2f5; border: none; border-radius: 20px; outline: none; }
        .conversation-list { flex: 1; overflow-y: auto; }
        .conversation-item { display: flex; padding: 12px 15px; cursor: pointer; transition: background 0.2s; }
        .conversation-item:hover, .conversation-item.active { background: #f0f2f5; }
        .conv-avatar { width: 48px; height: 48px; border-radius: 50%; margin-right: 12px; object-fit: cover; }
        
        .main-chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; min-width: 0; }
        .chat-area-header { height: 60px; padding: 0 20px; border-bottom: 1px solid #e4e6eb; display: flex; align-items: center; justify-content: space-between; }
        
        /* Flex container for messages */
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
            word-wrap: break-word; 
        }
        
        /* Ensure specific alignment */
        .message-received { 
            align-self: flex-start !important; 
            background: #fff; 
            color: #050505; 
            border-bottom-left-radius: 4px; 
        }
        
        .message-sent { 
            align-self: flex-end !important; 
            background: #0084ff; 
            color: #fff; 
            border-bottom-right-radius: 4px; 
        }
        
        .message-image { max-width: 200px; border-radius: 10px; cursor: pointer; }
        .message-file { display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.05); padding: 10px; border-radius: 10px; }
        .message-quote { background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 0; overflow: hidden; width: 300px; color: #333; }
        .quote-header { background: #f8f9fa; padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .quote-body { padding: 15px; }
        .quote-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem; }
        .quote-total { font-size: 1.1rem; font-weight: bold; color: #dc3545; text-align: right; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; }
        
        .input-area { padding: 15px; border-top: 1px solid #e4e6eb; background: #fff; display: flex; align-items: center; gap: 10px; }
        .message-input { flex: 1; background: #f0f2f5; border: none; border-radius: 20px; padding: 10px 15px; outline: none; }
        
        .right-sidebar { width: 350px; border-left: 1px solid #e4e6eb; background: #fff; display: flex; flex-direction: column; overflow-y: auto; }
        .info-header { padding: 15px 20px; border-bottom: 1px solid #e4e6eb; font-weight: 600; }
        .info-content { padding: 20px; }
        .project-card { border: 1px solid #e4e6eb; border-radius: 8px; padding: 12px; margin-bottom: 20px; display: flex; gap: 12px; }
        .project-thumb { width: 60px; height: 60px; border-radius: 4px; object-fit: cover; }
        .loading { text-align: center; color: #999; margin: 10px 0; }
        
        .btn-confirm-complete { display: none; margin-left: auto; }

        /* Timeline CSS */
        .timeline { position: relative; padding-left: 20px; margin-top: 20px; border-left: 2px solid #e4e6eb; margin-left: 10px; }
        .timeline-item { position: relative; padding-bottom: 20px; padding-left: 20px; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-dot { position: absolute; left: -26px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: #e4e6eb; border: 2px solid #fff; box-shadow: 0 0 0 1px #e4e6eb; }
        .timeline-item.active .timeline-dot { background: #28a745; box-shadow: 0 0 0 1px #28a745; }
        .timeline-content { font-size: 13px; color: #666; }
        .timeline-item.active .timeline-content { color: #000; font-weight: 500; }
        
        @media (max-width: 992px) { .right-sidebar { display: none; } }
        @media (max-width: 768px) { .left-sidebar { display: none; } }
    </style>
</head>
<body>

    <header class="chat-header">
        <div class="d-flex align-items-center gap-4">
            <a href="index.php" class="logo-area">
                <img src="assets/logo/favicon.png" alt="UniWork">
                <span class="logo-text">UniWork</span>
            </a>
            <div class="header-actions">
                <a href="index.php" class="nav-btn"><i class="fas fa-home"></i><span>Trang chủ</span></a>
                <a href="product-detail.php?id=<?php echo $product_id; ?>" class="nav-btn"><i class="fas fa-arrow-left"></i><span>Quay lại dự án</span></a>
            </div>
        </div>
        <div class="user-actions">
            <span class="badge bg-light text-dark me-2">ID: <?= $current_user_id ?></span>
            <button class="btn btn-outline-danger btn-sm me-2" onclick="resetDemo()">Reset Demo</button>
            <img src="<?php echo isset($current_user['image_url']) ? htmlspecialchars($current_user['image_url']) : 'assets/img/user.jpg'; ?>" 
                 class="user-avatar-small" onerror="this.src='https://via.placeholder.com/32'">
        </div>
    </header>

    <div class="chat-container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <div class="search-box"><input type="text" class="search-input" placeholder="Tìm kiếm đơn hàng"></div>
            <div class="conversation-list">
                <div class="conversation-item active">
                    <img src="assets/images/clients/c1.png" class="conv-avatar">
                    <div>
                        <div style="font-weight: 600;"><?= $freelancer_name ?></div>
                        <div style="font-size: 12px; color: #666;">#<?= $project_code ?></div>
                        <div style="font-size: 13px; color: #888;">Đang trực tuyến</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="main-chat-area">
            <div class="chat-area-header">
                <div class="d-flex align-items-center">
                    <img src="assets/images/clients/c1.png" style="width:36px;height:36px;border-radius:50%;">
                    <span style="font-weight:600;margin-left:10px;"><?= $freelancer_name ?></span>
                </div>
                <button class="btn btn-success btn-sm btn-confirm-complete" id="btnConfirmUser" onclick="confirmUserCompletion()">
                    <i class="fas fa-check-circle me-1"></i> Xác nhận hoàn thành
                </button>
            </div>

            <div class="messages-container" id="messagesList">
                <div class="loading">Đang tải tin nhắn...</div>
            </div>

            <div class="input-area">
                <div style="font-size:1.2rem;color:#65676b;display:flex;gap:10px;margin-right:10px;">
                    <label style="cursor:pointer;"><i class="fas fa-paperclip"></i><input type="file" id="fileInput" hidden onchange="uploadFile(this.files[0])"></label>
                    <label style="cursor:pointer;"><i class="far fa-image"></i><input type="file" id="imageInput" accept="image/*" hidden onchange="uploadFile(this.files[0])"></label>
                </div>
                <input type="text" class="message-input" placeholder="Nhập tin nhắn..." id="messageInput">
                <div style="color:#0084ff;font-size:1.2rem;cursor:pointer;padding:8px;" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <div class="info-header">Chi tiết dự án</div>
            <div class="info-content">
                <div class="project-card">
                    <img src="<?= $project_image ?>" class="project-thumb" onerror="this.src='https://via.placeholder.com/60'">
                    <div style="font-size:14px;font-weight:600;"><?= $project_title ?></div>
                </div>
                
                <h6 style="margin-top:20px;font-size:14px;font-weight:700;">Tài liệu dự án</h6>
                <div id="fileList" style="margin-top:10px;">
                    <div class="text-muted small">Đang tải...</div>
                </div>

                <h6 style="margin-top:20px;font-size:14px;font-weight:700;">Tiến độ dự án</h6>
                <div class="timeline" id="projectTimeline">
                    <div class="timeline-item" id="step1">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">Hoàn thành báo giá</div>
                    </div>
                    <div class="timeline-item" id="step2">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">Freelancer hoàn thành công việc</div>
                    </div>
                    <div class="timeline-item" id="step3">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">Người mua xác nhận</div>
                    </div>
                    <div class="timeline-item" id="step4">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">Người bán xác nhận</div>
                    </div>
                </div>
                
                <button style="margin-top:30px;width:100%;padding:8px;border:1px solid #dc3545;color:#dc3545;background:transparent;border-radius:6px;">Gửi khiếu nại</button>
            </div>
        </div>
    </div>

    <!-- Quote Details Modal -->
    <div class="modal fade" id="quoteDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-file-invoice-dollar me-2"></i>Chi Tiết Báo Giá</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="quoteModalBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-success" id="btnAcceptQuote" onclick="acceptQuote()">Chấp nhận & Thanh toán</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const CURRENT_USER_ID = <?= $current_user_id ?>;
        const PROJECT_ID = <?= $product_id ?>;
        const RECEIVER_ID = <?= $freelancer_id ?>;
        
        let lastMsgCount = 0;
        let projectStatus = {};

        function fetchMessages() {
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'GET',
                data: { action: 'get_messages', project_id: PROJECT_ID, current_user_id: CURRENT_USER_ID },
                success: function(response) {
                    if(response.success) {
                        projectStatus = response.data.status || {};
                        renderMessages(response.data.messages);
                        updateUIStatus();
                    }
                }
            });
        }
        
        function resetDemo() {
            if(!confirm('Reset lại toàn bộ trạng thái demo (Báo giá, Xác nhận hoàn thành)?')) return;
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'POST',
                data: { action: 'reset_status', project_id: PROJECT_ID },
                success: function(res) {
                    if(res.success) {
                        alert('Đã reset trạng thái demo.');
                        fetchMessages();
                    }
                }
            });
        }
        
        function updateUIStatus() {
            const btnComplete = document.getElementById('btnConfirmUser');
            
            // Logic: Quote Accepted -> Freelancer Work Done -> User Confirmed
            
            if (projectStatus.quote_accepted == 1 && projectStatus.freelancer_work_done == 1 && projectStatus.user_confirmed == 0) {
                // User can confirm only if Freelancer marked work as done
                btnComplete.style.display = 'block';
                btnComplete.classList.remove('btn-secondary');
                btnComplete.classList.add('btn-success');
                btnComplete.innerText = 'Xác nhận hoàn thành';
                btnComplete.disabled = false;
            } else if (projectStatus.user_confirmed == 1) {
                btnComplete.style.display = 'block';
                btnComplete.classList.remove('btn-success');
                btnComplete.classList.add('btn-secondary');
                btnComplete.innerText = 'Đã xác nhận hoàn thành';
                btnComplete.disabled = true;
            } else {
                // Hidden if quote not accepted OR freelancer hasn't finished work
                btnComplete.style.display = 'none';
            }

            // Timeline Update
            const steps = [
                { id: 'step1', done: projectStatus.quote_accepted == 1 },
                { id: 'step2', done: projectStatus.freelancer_work_done == 1 },
                { id: 'step3', done: projectStatus.user_confirmed == 1 },
                { id: 'step4', done: projectStatus.freelancer_confirmed == 1 }
            ];

            steps.forEach(s => {
                const el = document.getElementById(s.id);
                if(el) {
                    if(s.done) el.classList.add('active');
                    else el.classList.remove('active');
                }
            });
        }

        function renderMessages(messages) {
            if (messages.length === lastMsgCount && messages.length > 0) return;
            
            const container = $('#messagesList');
            container.empty();
            
            messages.forEach(msg => {
                let contentHtml = '';
                // Ensure boolean conversion here just in case API returns string
                const isSelf = (msg.is_self === true || msg.is_self === "true" || msg.is_self == 1);
                
                if (msg.message_type === 'text') {
                    contentHtml = msg.message_content;
                } else if (msg.message_type === 'image') {
                    contentHtml = `<img src="${msg.message_content}" class="message-image" onclick="window.open(this.src)">`;
                } else if (msg.message_type === 'file') {
                    contentHtml = `
                        <div class="message-file">
                            <i class="fas fa-file-download fa-lg"></i>
                            <div>
                                <div style="font-weight:600;">${msg.file_name}</div>
                                <a href="${msg.message_content}" download>Tải xuống</a>
                            </div>
                        </div>`;
                } else if (msg.message_type === 'quote') {
                    const q = msg.quote_data;
                    const safeQ = encodeURIComponent(JSON.stringify(q));
                    const isAccepted = projectStatus.quote_accepted == 1;
                    const badge = isAccepted ? '<span class="badge bg-success">Đã chấp nhận</span>' : '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                    
                    contentHtml = `
                        <div class="message-quote">
                            <div class="quote-header">
                                <span><i class="fas fa-file-invoice"></i> BÁO GIÁ</span>
                                ${badge}
                            </div>
                            <div class="quote-body">
                                <div class="quote-row"><span>Dịch vụ:</span> <strong>${q.service || 'N/A'}</strong></div>
                                <div class="quote-row"><span>Mã:</span> <span>${q.projectCode || 'N/A'}</span></div>
                                <div class="quote-total">${q.price || '0'}</div>
                                <div style="margin-top:10px;text-align:center;">
                                    <button class="btn btn-primary btn-sm w-100" onclick="showQuoteDetail('${safeQ}')">Xem chi tiết</button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // IMPORTANT: Add !important to CSS to force alignment or check inline styles
                const alignStyle = isSelf ? 'align-self: flex-end; background: #0084ff; color: #fff; border-bottom-right-radius: 4px;' : 'align-self: flex-start; background: #fff; color: #050505; border-bottom-left-radius: 4px;';
                
                const html = `<div class="message-bubble" style="${alignStyle}">${contentHtml}</div>`;
                container.append(html);
            });
            
            lastMsgCount = messages.length;
            container.scrollTop(container[0].scrollHeight);
        }

        function showQuoteDetail(quoteJson) {
            const q = JSON.parse(decodeURIComponent(quoteJson));
            const html = `
                <div class="list-group">
                    <div class="list-group-item"><div class="d-flex w-100 justify-content-between"><h6 class="mb-1">Dịch vụ</h6><small>${q.service}</small></div></div>
                    <div class="list-group-item"><div class="d-flex w-100 justify-content-between"><h6 class="mb-1">Mã dự án</h6><small>${q.projectCode}</small></div></div>
                    <div class="list-group-item"><div class="d-flex w-100 justify-content-between"><h6 class="mb-1">Giá trị</h6><strong class="text-danger">${q.price}</strong></div></div>
                    <div class="list-group-item"><h6 class="mb-1">Điều khoản</h6><p class="mb-1 small text-muted">${q.terms || '...'}</p></div>
                </div>
            `;
            $('#quoteModalBody').html(html);
            
            const btnAccept = document.getElementById('btnAcceptQuote');
            if (projectStatus.quote_accepted == 1) {
                btnAccept.innerText = 'Đã chấp nhận';
                btnAccept.disabled = true;
            } else {
                btnAccept.innerText = 'Chấp nhận & Thanh toán';
                btnAccept.disabled = false;
            }
            
            new bootstrap.Modal(document.getElementById('quoteDetailModal')).show();
        }
        
        function acceptQuote() {
            if(confirm('Bạn có chắc chắn muốn chấp nhận báo giá này?')) {
                updateStatus('quote_accepted');
                bootstrap.Modal.getInstance(document.getElementById('quoteDetailModal')).hide();
            }
        }
        
        function confirmUserCompletion() {
            if(confirm('Xác nhận freelancer đã hoàn thành công việc?')) {
                updateStatus('user_confirmed');
            }
        }
        
        function updateStatus(field) {
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'POST',
                data: { action: 'update_status', project_id: PROJECT_ID, field: field },
                success: function(res) {
                    if(res.success) fetchMessages(); 
                }
            });
        }

        function sendMessage() {
            const content = $('#messageInput').val().trim();
            if (!content) return;
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'POST',
                data: { action: 'send_message', project_id: PROJECT_ID, sender_id: CURRENT_USER_ID, receiver_id: RECEIVER_ID, content: content, type: 'text' },
                success: function(res) { if(res.success) { $('#messageInput').val(''); fetchMessages(); } }
            });
        }

        function uploadFile(file) {
            if(!file) return;
            const formData = new FormData();
            formData.append('action', 'upload_file');
            formData.append('file', file);
            formData.append('project_id', PROJECT_ID);
            formData.append('sender_id', CURRENT_USER_ID);
            formData.append('receiver_id', RECEIVER_ID);
            
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.success) {
                        fetchMessages();
                        if(res.data.type === 'file') fetchDocuments();
                    } else alert('Upload failed: ' + res.message);
                }
            });
        }
        
        function fetchDocuments() {
            $.ajax({
                url: 'ajax/chat_handler_demo.php',
                type: 'GET',
                data: { action: 'get_documents', project_id: PROJECT_ID },
                success: function(response) {
                    if(response.success) {
                        let html = '';
                        if(response.data.length === 0) html = '<div class="small text-muted">Chưa có tài liệu</div>';
                        response.data.forEach(file => {
                            html += `<div style="display:flex;align-items:center;padding:8px;background:#f8f9fa;border-radius:6px;margin-bottom:5px;">
                                    <i class="fas fa-file-alt text-primary me-2"></i>
                                    <div style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;">
                                        <a href="${file.message_content}" target="_blank" style="text-decoration:none;color:#333;">${file.file_name}</a>
                                    </div></div>`;
                        });
                        $('#fileList').html(html);
                    }
                }
            });
        }

        $('#messageInput').on('keypress', function(e) { if(e.which === 13) sendMessage(); });

        fetchMessages();
        fetchDocuments();
        setInterval(fetchMessages, 3000);
    </script>
</body>
</html>
