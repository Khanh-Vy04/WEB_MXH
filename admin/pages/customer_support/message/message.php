<?php
$currentPage = 'messages';
require_once __DIR__.'/../../../../config/database.php';
require_once __DIR__.'/../../../../includes/session.php';

$current_user_id = 1; 
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
    <link rel="stylesheet" href="message.css?v=<?= time() ?>">
    <style>
        .message-image { max-width: 200px; border-radius: 8px; cursor: pointer; }
        .message-file { background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #dee2e6; }
        .message-quote { width: 100%; max-width: 350px; background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .quote-head { background: #2d3436; color: white; padding: 8px 12px; font-weight: bold; font-size: 0.9rem; display: flex; justify-content: space-between; }
        .quote-content { padding: 12px; font-size: 0.9rem; }
        .quote-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .btn-complete-disabled { opacity: 0.6; cursor: not-allowed; }

        /* Timeline CSS */
        .timeline { position: relative; padding-left: 20px; margin-top: 20px; border-left: 2px solid #e4e6eb; margin-left: 10px; }
        .timeline-item { position: relative; padding-bottom: 20px; padding-left: 20px; }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-dot { position: absolute; left: -26px; top: 0; width: 14px; height: 14px; border-radius: 50%; background: #e4e6eb; border: 2px solid #fff; box-shadow: 0 0 0 1px #e4e6eb; }
        .timeline-item.active .timeline-dot { background: #28a745; box-shadow: 0 0 0 1px #28a745; }
        .timeline-content { font-size: 13px; color: #666; }
        .timeline-item.active .timeline-content { color: #000; font-weight: 500; }
    </style>
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>

    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>

        <div class="container-fluid pt-4 px-4" style="background:rgb(247, 251, 255); min-height: calc(100vh - 80px);">
            <div class="header-section mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0" style="color: #412d3b; font-weight: 700; font-size: 1.8rem;">
                        <i class="fas fa-comments" style="color: #deccca; margin-right: 10px;"></i>
                        Quản Lý Chat Support
                    </h2>
                </div>
            </div>

            <div class="bg-white rounded p-4 mb-4 shadow-sm" style="border: 1px solid #e9ecef;">
                <div class="row" style="height: 65vh;">
                    <!-- Left: Conversations -->
                    <div class="col-md-3 border-end">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0" style="color: #412d3b; font-weight: 700;">
                                <i class="fas fa-list" style="color: #deccca; margin-right: 8px;"></i> Danh sách
                            </h5>
                        </div>
                        <div id="sessionList" style="height: calc(100% - 50px); overflow-y: auto;">
                            <div class="text-center text-muted mt-4">Đang tải...</div>
                        </div>
                    </div>

                    <!-- Center: Chat -->
                    <div class="col-md-6 border-end d-flex flex-column">
                        <div class="chat-header p-3 border-bottom" style="background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%); color: white;">
                            <div class="d-flex align-items-center" id="chatHeaderInfo">
                                <div style="flex:1;">Chọn một cuộc trò chuyện</div>
                            </div>
                        </div>

                        <div class="chat-messages flex-grow-1 p-3" id="chat-box" style="background:#f8f9fa; overflow-y: auto;"></div>

                        <!-- Chat Input -->
                        <div class="chat-input border-top" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display:none;" id="inputArea">
                            <div class="chat-toolbar">
                                <div class="toolbar-actions">
                                    <label class="btn-toolbar mb-0 cursor-pointer"><i class="fas fa-paperclip"></i><input type="file" hidden onchange="uploadFile(this.files[0])"></label>
                                    <label class="btn-toolbar mb-0 cursor-pointer"><i class="far fa-image"></i><input type="file" accept="image/*" hidden onchange="uploadFile(this.files[0])"></label>
                                </div>
                                <div class="toolbar-actions">
                                    <button type="button" class="btn-action-large btn-quote" data-bs-toggle="modal" data-bs-target="#quoteModal">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> Gửi báo giá
                                    </button>
                                    <button type="button" class="btn-action-large btn-primary me-2" id="btnWorkDone" onclick="confirmWorkDone()" style="display:none;">
                                        <i class="fas fa-clipboard-check me-1"></i> XN hoàn thành CV
                                    </button>
                                    <button type="button" class="btn-action-large btn-complete btn-complete-disabled" id="btnComplete" onclick="confirmCompletion()" disabled title="Chờ khách hàng xác nhận trước">
                                        <i class="fas fa-check-circle me-1"></i> Hoàn tất đơn
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex align-items-end gap-2 p-3">
                                <textarea id="msgInput" class="form-control" rows="2" placeholder="Nhập tin nhắn..." style="resize:none;border-radius:15px;"></textarea>
                                <button onclick="sendMessage()" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Docs -->
                    <div class="col-md-3 d-flex flex-column bg-white">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-folder-open me-2 text-warning"></i> Tài liệu dự án</h6>
                        </div>
                        <div class="flex-grow-1 p-3" style="overflow-y: auto;" id="docList">
                            <div class="text-center text-muted small">Chọn dự án để xem</div>
                        </div>

                        <div class="p-3 border-top">
                             <h6 class="mb-3 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i> Tiến độ</h6>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>

<!-- Quote Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-file-invoice-dollar me-2"></i>Tạo Báo Giá</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="quoteForm">
            <div class="mb-3"><label>Dịch vụ</label><input type="text" class="form-control" name="service" required></div>
            <div class="mb-3"><label>Mã dự án</label><input type="text" class="form-control" name="projectCode" required></div>
            <div class="mb-3"><label>Giá (VNĐ)</label><input type="text" class="form-control" name="price" required></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary" onclick="sendQuote()">Gửi</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
<script>
    const CURRENT_USER_ID = 1; 
    let currentProjectId = 0;
    let currentReceiverId = 0;
    let lastMsgCount = 0;
    let projectStatus = {};

    function loadSessions() {
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            data: { action: 'get_conversations', user_id: CURRENT_USER_ID },
            success: function(res) {
                if(res.success) {
                    let html = '';
                    res.data.forEach(s => {
                        const active = s.project_id == currentProjectId ? 'background:#e9ecef;' : '';
                        const otherId = (s.sender_id == CURRENT_USER_ID) ? s.receiver_id : s.sender_id;
                        html += `<div class="p-3 mb-2 rounded border" style="cursor:pointer;${active}" 
                                 onclick="selectSession(${s.project_id}, ${otherId}, '${s.other_name}')">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width:40px;height:40px;font-weight:bold;">${s.other_name.charAt(0).toUpperCase()}</div>
                                    <div style="flex:1;overflow:hidden;"><div class="fw-bold text-truncate">${s.other_name}</div><div class="small text-muted text-truncate">${s.message_type == 'text' ? s.message_content : '['+s.message_type+']'}</div></div>
                                    ${s.unread > 0 ? `<span class="badge bg-danger rounded-pill">${s.unread}</span>` : ''}
                                </div></div>`;
                    });
                    $('#sessionList').html(html);
                }
            }
        });
    }

    function selectSession(projectId, receiverId, name) {
        currentProjectId = projectId;
        currentReceiverId = receiverId;
        $('#chatHeaderInfo').html(`<div><h6 class="mb-0 text-white">${name}</h6><small class="text-white-50">Project #${projectId}</small></div>`);
        $('#inputArea').show(); 
        lastMsgCount = 0;
        loadMessages();
        loadDocs();
    }

    function loadMessages() {
        if(!currentProjectId) return;
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            data: { action: 'get_messages', project_id: currentProjectId, current_user_id: CURRENT_USER_ID },
            success: function(res) {
                if(res.success) {
                    projectStatus = res.data.status || {};
                    updateStatusUI();
                    if(res.data.messages.length !== lastMsgCount) {
                        renderMessages(res.data.messages);
                        lastMsgCount = res.data.messages.length;
                    }
                }
            }
        });
    }
    
    function updateStatusUI() {
        const btnComplete = document.getElementById('btnComplete');
        const btnWorkDone = document.getElementById('btnWorkDone');

        // Logic for Work Done Button (Only show if quote accepted and not yet marked done)
        if (projectStatus.quote_accepted == 1 && projectStatus.freelancer_work_done == 0) {
            btnWorkDone.style.display = 'inline-block';
        } else {
            btnWorkDone.style.display = 'none';
        }

        // Logic for Complete Button
        if (projectStatus.freelancer_confirmed == 1) {
            btnComplete.innerHTML = '<i class="fas fa-check-double me-1"></i> Đã hoàn thành';
            btnComplete.classList.remove('btn-complete-disabled');
            btnComplete.classList.add('btn-success');
            btnComplete.disabled = true;
        } else if (projectStatus.user_confirmed == 1) {
            btnComplete.innerHTML = '<i class="fas fa-check-circle me-1"></i> Hoàn tất đơn';
            btnComplete.classList.remove('btn-complete-disabled');
            btnComplete.disabled = false;
            btnComplete.title = "Khách hàng đã xác nhận, bạn có thể hoàn tất";
        } else {
            btnComplete.innerHTML = '<i class="fas fa-check-circle me-1"></i> Hoàn tất đơn';
            btnComplete.classList.add('btn-complete-disabled');
            btnComplete.disabled = true;
            btnComplete.title = "Chờ khách hàng xác nhận trước";
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
    
    function confirmWorkDone() {
        if(confirm('Xác nhận bạn đã hoàn thành công việc và gửi cho khách hàng?')) {
            $.ajax({
                url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
                type: 'POST',
                data: { action: 'update_status', project_id: currentProjectId, field: 'freelancer_work_done' },
                success: function(res) { if(res.success) loadMessages(); }
            });
        }
    }
    
    function confirmCompletion() {
        if(confirm('Xác nhận hoàn tất đơn hàng và nhận thanh toán?')) {
            $.ajax({
                url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
                type: 'POST',
                data: { action: 'update_status', project_id: currentProjectId, field: 'freelancer_confirmed' },
                success: function(res) { if(res.success) loadMessages(); }
            });
        }
    }

    function renderMessages(msgs) {
        const box = $('#chat-box');
        box.empty();
        msgs.forEach(msg => {
            let content = '';
            if(msg.message_type === 'text') content = msg.message_content;
            else if(msg.message_type === 'image') content = `<img src="${msg.message_content}" class="message-image" onclick="window.open(this.src)">`;
            else if(msg.message_type === 'file') content = `<div class="message-file"><i class="fas fa-download me-2"></i><a href="${msg.message_content}" download>${msg.file_name}</a></div>`;
            else if(msg.message_type === 'quote') {
                const q = msg.quote_data;
                const statusBadge = projectStatus.quote_accepted == 1 ? '<span class="badge bg-success ms-auto">Đã chấp nhận</span>' : '<span class="badge bg-warning text-dark ms-auto">Chờ duyệt</span>';
                content = `<div class="message-quote"><div class="quote-head"><span>BÁO GIÁ</span>${statusBadge}</div><div class="quote-content"><div class="quote-row"><span>Dịch vụ:</span> <b>${q.service}</b></div><div class="quote-row"><span>Mã:</span> <span>${q.projectCode}</span></div><div class="quote-total">${q.price}</div></div></div>`;
            }

            const align = msg.is_self ? 'text-end' : 'text-start';
            const bubble = msg.is_self ? 'bg-primary text-white' : 'bg-white border text-dark';
            let bubbleStyle = `display:inline-block;padding:10px;border-radius:15px;max-width:70%;text-align:left;`;
            if (['image', 'quote'].includes(msg.message_type)) bubbleStyle = 'display:inline-block;max-width:70%;text-align:left;';

            box.append(`<div class="mb-3 ${align}"><div class="${['image','quote'].includes(msg.message_type) ? '' : bubble}" style="${bubbleStyle}">${content}</div><div class="small text-muted mt-1">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</div></div>`);
        });
        box.scrollTop(box[0].scrollHeight);
    }

    function loadDocs() {
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            data: { action: 'get_documents', project_id: currentProjectId },
            success: function(res) {
                if(res.success) {
                    let html = '';
                    if(res.data.length === 0) html = '<div class="text-center text-muted small">Chưa có tài liệu</div>';
                    res.data.forEach(d => {
                        html += `<div class="p-2 mb-2 bg-light border rounded d-flex align-items-center"><i class="fas fa-file me-2 text-secondary"></i><div class="text-truncate flex-grow-1 small"><a href="${d.message_content}" download>${d.file_name}</a></div></div>`;
                    });
                    $('#docList').html(html);
                }
            }
        });
    }

    function sendMessage() {
        const val = $('#msgInput').val().trim();
        if(!val || !currentProjectId) return;
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            type: 'POST',
            data: { action: 'send_message', project_id: currentProjectId, sender_id: CURRENT_USER_ID, receiver_id: currentReceiverId, content: val, type: 'text' },
            success: function(res) { if(res.success) { $('#msgInput').val(''); loadMessages(); } }
        });
    }

    function sendQuote() {
        const form = new FormData(document.getElementById('quoteForm'));
        const data = Object.fromEntries(form);
        const json = JSON.stringify(data);
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            type: 'POST',
            data: { action: 'send_message', project_id: currentProjectId, sender_id: CURRENT_USER_ID, receiver_id: currentReceiverId, content: json, type: 'quote' },
            success: function(res) { if(res.success) { bootstrap.Modal.getInstance(document.getElementById('quoteModal')).hide(); loadMessages(); } }
        });
    }

    function uploadFile(file) {
        if(!file || !currentProjectId) return;
        const formData = new FormData();
        formData.append('action', 'upload_file');
        formData.append('file', file);
        formData.append('project_id', currentProjectId);
        formData.append('sender_id', CURRENT_USER_ID);
        formData.append('receiver_id', currentReceiverId);
        $.ajax({
            url: '/WEB_MXH/user/ajax/chat_handler_demo.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) { if(res.success) { loadMessages(); if(res.data.type == 'file') loadDocs(); } else alert('Upload failed: ' + res.message); }
        });
    }

    $('#msgInput').on('keypress', function(e) { if(e.which === 13) sendMessage(); });
    loadSessions();
    setInterval(loadMessages, 3000);
    setInterval(loadSessions, 10000);
</script>
</body>
</html>
