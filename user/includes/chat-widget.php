<!-- Chat Widget -->
<div class="chat-widget">
    <!-- Chat Icon -->
    <div class="chat-icon">
        <i class="fa fa-comments"></i>
        <!-- Optional notification badge -->
        <!-- <div class="notification-badge">1</div> -->
    </div>
    
    <!-- Chat Options Menu -->
    <div class="chat-options">
        <div class="chat-option admin">
            <i class="fa fa-user-tie"></i>
            <div class="chat-option-content">
                <h4>Chat với Admin</h4>
                <p>Hỗ trợ trực tiếp từ nhân viên</p>
            </div>
        </div>
        
        <div class="chat-option bot">
            <i class="fa fa-robot"></i>
            <div class="chat-option-content">
                <h4>Chat với AuraBot</h4>
                <p>Trợ lý AI thông minh 24/7</p>
            </div>
        </div>
    </div>
    
    <!-- Chat Box -->
    <div class="chat-box">
        <!-- Chat Header -->
        <div class="chat-header admin">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <i class="fa fa-user-tie"></i>
                </div>
                <div class="chat-title">
                    <h4>Admin Support</h4>
                    <p>Hỗ trợ khách hàng</p>
                </div>
            </div>
            <button class="chat-close">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <!-- Admin Chat Controls (Only visible in admin mode) -->
        <div class="admin-chat-controls" style="display: none;">
            <div class="chat-controls-header">
                <button class="btn-new-chat">
                    <i class="fa fa-plus"></i> Chat mới
                </button>
                <button class="btn-chat-history">
                    <i class="fa fa-history"></i> Lịch sử
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages">
            <!-- Messages will be dynamically added here -->
        </div>
        
        <!-- Sessions List (Hidden by default) -->
        <div class="chat-sessions" style="display: none;">
            <div class="sessions-header">
                <h4>Lịch sử Chat với Admin</h4>
                <button class="btn-back-to-chat">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </button>
            </div>
            <div class="sessions-list">
                <!-- Sessions will be loaded here -->
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input-container">
            <input type="text" class="chat-input" placeholder="Nhập tin nhắn...">
            <button class="chat-send">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div> 