// Chat Widget JavaScript
class ChatWidget {
    constructor() {
        this.currentChatType = null;
        this.isOptionsVisible = false;
        this.currentSupportId = null;
        this.isSessionsView = false;
        this.messages = {
            bot: []
        };
        this.showScrollButton = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadSampleMessages();
    }

    bindEvents() {
        // Toggle chat options
        const chatIcon = document.querySelector('.chat-icon');
        const chatOptions = document.querySelector('.chat-options');
        
        chatIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleChatOptions();
        });

        // Chat option clicks
        document.querySelectorAll('.chat-option').forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                if (option.classList.contains('admin')) {
                    this.openAdminChat();
                } else {
                    this.openChat('bot');
                }
            });
        });

        // Close chat box
        document.querySelector('.chat-close').addEventListener('click', () => {
            this.closeChat();
        });

        // Send message
        const sendBtn = document.querySelector('.chat-send');
        const chatInput = document.querySelector('.chat-input');
        
        sendBtn.addEventListener('click', () => {
            this.sendMessage();
        });

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Click outside to close options
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.chat-widget')) {
                this.hideChatOptions();
            }
        });

        // Admin controls
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-new-chat')) {
                this.createNewAdminChat();
            } else if (e.target.closest('.btn-chat-history')) {
                this.showChatHistory();
            } else if (e.target.closest('.btn-back-to-chat')) {
                this.backToCurrentChat();
            } else if (e.target.closest('.scroll-to-bottom')) {
                this.scrollToBottom(true);
                this.hideScrollToBottomButton();
            }
        });

        // Scroll detection for chat messages - sử dụng delegation
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new MutationObserver(() => {
                const chatMessages = document.querySelector('.chat-messages');
                if (chatMessages && !chatMessages.dataset.scrollListenerAdded) {
                    chatMessages.addEventListener('scroll', (e) => {
                        this.handleChatScroll(e.target);
                    });
                    chatMessages.dataset.scrollListenerAdded = 'true';
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }

    toggleChatOptions() {
        const chatOptions = document.querySelector('.chat-options');
        const chatIcon = document.querySelector('.chat-icon');
        
        if (this.isOptionsVisible) {
            this.hideChatOptions();
        } else {
            this.showChatOptions();
        }
    }

    showChatOptions() {
        const chatOptions = document.querySelector('.chat-options');
        const chatIcon = document.querySelector('.chat-icon');
        
        chatOptions.classList.add('show');
        chatIcon.classList.add('active');
        this.isOptionsVisible = true;
    }

    hideChatOptions() {
        const chatOptions = document.querySelector('.chat-options');
        const chatIcon = document.querySelector('.chat-icon');
        
        chatOptions.classList.remove('show');
        chatIcon.classList.remove('active');
        this.isOptionsVisible = false;
    }

    openChat(type) {
        this.currentChatType = type;
        this.isSessionsView = false;
        this.hideChatOptions();
        
        // Update chat header
        const chatHeader = document.querySelector('.chat-header');
        const chatTitle = document.querySelector('.chat-title h4');
        const chatSubtitle = document.querySelector('.chat-title p');
        const chatAvatar = document.querySelector('.chat-avatar');
        
        chatHeader.className = `chat-header ${type}`;
        
        if (type === 'admin') {
            chatTitle.textContent = 'Admin Support';
            chatSubtitle.textContent = 'Hỗ trợ khách hàng';
            chatAvatar.innerHTML = '<i class="fa fa-user-tie"></i>';
        } else {
            chatTitle.textContent = 'AuraBot';
            chatSubtitle.textContent = 'Trợ lý AI thông minh';
            chatAvatar.innerHTML = '<i class="fa fa-robot"></i>';
        }
        
        // Show/hide appropriate sections
        document.querySelector('.admin-chat-controls').style.display = type === 'admin' ? 'block' : 'none';
        document.querySelector('.chat-messages').style.display = 'block';
        document.querySelector('.chat-sessions').style.display = 'none';
        document.querySelector('.chat-input-container').style.display = 'flex';
        
        // Load messages
        if (type === 'bot') {
            this.loadBotMessages();
        }
        
        // Show chat box
        const chatBox = document.querySelector('.chat-box');
        chatBox.classList.add('show');
        
        // Prevent chat box from affecting page scroll
        chatBox.addEventListener('wheel', (e) => {
            e.stopPropagation();
        });
        
        chatBox.addEventListener('touchmove', (e) => {
            e.stopPropagation();
        });
        
        // Setup scroll isolation and listeners
        setTimeout(() => {
            const chatMessages = document.querySelector('.chat-messages');
            if (chatMessages) {
                // Setup complete scroll isolation
                this.setupScrollIsolation(chatMessages);
                
                // Add scroll detection for button
                if (!chatMessages.dataset.scrollListenerAdded) {
                    chatMessages.addEventListener('scroll', (e) => {
                        e.stopPropagation();
                        this.handleChatScroll(e.target);
                    });
                    chatMessages.dataset.scrollListenerAdded = 'true';
                }
            }
            
            document.querySelector('.chat-input').focus();
        }, 300);
    }

    async openAdminChat() {
        try {
            // Get latest active session or create new one
            const response = await fetch('ajax/chat_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_latest_session'
            });

            const data = await response.json();
            
            if (data.success) {
                if (data.support_id) {
                    // Has existing session
                    this.currentSupportId = data.support_id;
                    this.openChat('admin');
                    this.loadAdminMessages(data.support_id);
                } else {
                    // No session, will create on first message
                    this.currentSupportId = null;
                    this.openChat('admin');
                    this.clearMessages();
                }
            } else {
                alert(data.message || 'Lỗi khi mở chat');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Lỗi kết nối. Vui lòng thử lại sau.');
        }
    }



    async createNewAdminChat() {
        try {
            // Create new session by resetting support_id
            this.currentSupportId = null;
            this.openChat('admin');
            this.clearMessages();
            
            // Show welcome message
            this.displayMessage('Chào bạn! Hãy gửi tin nhắn để bắt đầu cuộc trò chuyện mới.', 'admin');
        } catch (error) {
            console.error('Error creating new chat:', error);
        }
    }

    showChatHistory() {
        // Show sessions list
        document.querySelector('.chat-messages').style.display = 'none';
        document.querySelector('.chat-sessions').style.display = 'block';
        document.querySelector('.chat-input-container').style.display = 'none';
        document.querySelector('.admin-chat-controls').style.display = 'none';
        
        // Load sessions
        this.loadSessions();
    }

    backToCurrentChat() {
        // Hide sessions and show current chat
        document.querySelector('.chat-messages').style.display = 'block';
        document.querySelector('.chat-sessions').style.display = 'none';
        document.querySelector('.chat-input-container').style.display = 'flex';
        document.querySelector('.admin-chat-controls').style.display = 'block';
    }

    clearMessages() {
        document.querySelector('.chat-messages').innerHTML = '';
    }

    closeChat() {
        document.querySelector('.chat-box').classList.remove('show');
        this.currentChatType = null;
    }

    loadMessages(type) {
        const messagesContainer = document.querySelector('.chat-messages');
        messagesContainer.innerHTML = '';
        
        const messages = this.messages[type];
        messages.forEach(message => {
            this.displayMessage(message.text, message.sender, message.time, false);
        });
        
        this.scrollToBottom();
    }

    async loadBotMessages() {
        try {
            const response = await fetch('ajax/chat_bot.php?action=get_conversation');
            const data = await response.json();
            
            if (data.success) {
                const messagesContainer = document.querySelector('.chat-messages');
                messagesContainer.innerHTML = '';
                
                data.messages.forEach(message => {
                    this.displayMessage(message.user_message, 'user', message.timestamp, false);
                    this.displayMessage(message.bot_response, 'bot', message.timestamp, false);
                });
                
                this.scrollToBottom(false);
                

            } else {
                console.error('Error loading bot messages:', data.message);
                // Show welcome message if no conversation
                this.displayMessage('🤖 Xin chào! Tôi là AuraBot - trợ lý AI của AuraDisc. Tôi có thể giúp bạn tìm kiếm album và phụ kiện âm nhạc. Bạn cần hỗ trợ gì hôm nay? 🎵', 'bot', new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}), false);
            }
        } catch (error) {
            console.error('Error loading bot conversation:', error);
            // Show welcome message on error
            this.displayMessage('🤖 Xin chào! Tôi là AuraBot - trợ lý AI của AuraDisc. Tôi có thể giúp bạn tìm kiếm album và phụ kiện âm nhạc. Bạn cần hỗ trợ gì hôm nay? 🎵', 'bot', new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}), false);
        }
    }

    async sendMessage() {
        const input = document.querySelector('.chat-input');
        const message = input.value.trim();
        
        if (!message || !this.currentChatType) return;
        
        // Display user message immediately
        const now = new Date();
        this.displayMessage(message, 'user', now);
        
        // Clear input
        input.value = '';
        
        if (this.currentChatType === 'admin') {
            await this.sendAdminMessage(message);
        } else {
            await this.sendBotMessage(message);
        }
    }

    async sendAdminMessage(message) {
        try {
            let body = `action=send_message&message=${encodeURIComponent(message)}`;
            if (this.currentSupportId) {
                body += `&support_id=${this.currentSupportId}`;
            }

            const response = await fetch('ajax/chat_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            });

            const data = await response.json();
            
            if (data.success) {
                // Set support_id if it was created
                if (data.support_id && !this.currentSupportId) {
                    this.currentSupportId = data.support_id;
                }
                
                // If there's an auto-reply (welcome message)
                if (data.auto_reply) {
                    setTimeout(() => {
                        this.displayMessage(data.auto_reply.message, 'admin', data.auto_reply.created_at);
                    }, 1000);
                }
            } else {
                alert(data.message || 'Lỗi khi gửi tin nhắn');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Lỗi kết nối. Vui lòng thử lại sau.');
        }
    }

    async sendBotMessage(message) {
        try {
            // Show typing indicator
            this.showTypingIndicator();

            const response = await fetch('ajax/chat_bot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&message=${encodeURIComponent(message)}`
            });

            const data = await response.json();
            
            // Hide typing indicator
            this.hideTypingIndicator();
            
            if (data.success) {
                // Display bot response
                this.displayMessage(data.bot_response, 'bot', data.timestamp);
            } else {
                alert(data.message || 'Lỗi khi gửi tin nhắn đến AuraBot');
            }
        } catch (error) {
            this.hideTypingIndicator();
            console.error('Error sending bot message:', error);
            
            // Fallback response if API fails
            this.displayMessage('🤖 Xin lỗi, tôi đang gặp vấn đề kỹ thuật. Vui lòng thử lại sau hoặc liên hệ admin support!', 'bot', new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'}));
        }
    }

    displayMessage(text, sender, time, animate = true) {
        const messagesContainer = document.querySelector('.chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        
        const timeStr = typeof time === 'string' ? time : time.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        messageDiv.innerHTML = `
            <div class="message-bubble">${text}</div>
            <div class="message-time">${timeStr}</div>
        `;
        
        if (animate) {
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateY(20px)';
        }
        
        messagesContainer.appendChild(messageDiv);
        
        if (animate) {
            setTimeout(() => {
                messageDiv.style.transition = 'all 0.3s ease';
                messageDiv.style.opacity = '1';
                messageDiv.style.transform = 'translateY(0)';
            }, 50);
        }
        
        this.scrollToBottom(true);
        

    }

    simulateResponse(userMessage) {
        // Only for bot chat
        if (this.currentChatType !== 'bot') return;
        
        // Show typing indicator
        this.showTypingIndicator();
        
        setTimeout(() => {
            this.hideTypingIndicator();
            
            const response = this.getBotResponse(userMessage);
            const now = new Date();
            
            this.displayMessage(response, 'bot', now);
            
            // Save response
            this.messages.bot.push({
                text: response,
                sender: 'bot',
                time: now
            });
            
        }, 1000 + Math.random() * 2000); // Random delay 1-3 seconds
    }



    getBotResponse(message) {
        const responses = [
            "🤖 Xin chào! Tôi là AuraBot, trợ lý AI của AuraDisc. Tôi có thể giúp gì cho bạn?",
            "🎵 Tôi có thể giúp bạn tìm album, nghệ sĩ yêu thích hoặc giải đáp thắc mắc về sản phẩm!",
            "🔍 Hãy cho tôi biết bạn đang tìm kiếm gì? Tôi sẽ tìm giúp bạn!",
            "💡 Tôi có thể gợi ý những album phù hợp với sở thích của bạn!",
            "🎧 Bạn có muốn tôi giới thiệu những sản phẩm mới nhất không?"
        ];
        
        const lowerMessage = message.toLowerCase();
        
        if (lowerMessage.includes('album') || lowerMessage.includes('nhạc')) {
            return "🎵 AuraDisc có rất nhiều album từ các nghệ sĩ nổi tiếng! Bạn thích thể loại nhạc nào? Pop, Rock, Jazz hay EDM?";
        }
        
        if (lowerMessage.includes('nghệ sĩ') || lowerMessage.includes('artist')) {
            return "🌟 Chúng tôi có album từ nhiều nghệ sĩ tài năng! Bạn có thể xem trong mục 'Nghệ sĩ nổi bật' nhé!";
        }
        
        if (lowerMessage.includes('giá') || lowerMessage.includes('price')) {
            return "💰 Giá các sản phẩm rất đa dạng từ $9.99 - $49.99. Bạn muốn xem sản phẩm nào cụ thể?";
        }
        
        if (lowerMessage.includes('giao hàng')) {
            return "🚚 Chúng tôi giao hàng toàn quốc trong 2-5 ngày! Miễn phí ship cho đơn hàng trên $50!";
        }
        
        return responses[Math.floor(Math.random() * responses.length)];
    }

    showTypingIndicator() {
        const messagesContainer = document.querySelector('.chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        
        messagesContainer.appendChild(typingDiv);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    scrollToBottom(smooth = true) {
        const messagesContainer = document.querySelector('.chat-messages');
        if (smooth) {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    handleChatScroll(container) {
        const scrollTop = container.scrollTop;
        const scrollHeight = container.scrollHeight;
        const clientHeight = container.clientHeight;
        
        // Check if content is scrollable
        const canScroll = scrollHeight > clientHeight;
        
        // Check if user scrolled up from bottom
        const isNearBottom = scrollTop + clientHeight >= scrollHeight - 50;
        
        // Show scroll-to-bottom button if not near bottom
        if (!isNearBottom && canScroll && !this.showScrollButton) {
            this.showScrollToBottomButton();
        } else if ((isNearBottom || !canScroll) && this.showScrollButton) {
            this.hideScrollToBottomButton();
        }
    }

    showScrollToBottomButton() {
        this.showScrollButton = true;
        let scrollBtn = document.querySelector('.scroll-to-bottom');
        
        if (!scrollBtn) {
            scrollBtn = document.createElement('button');
            scrollBtn.className = 'scroll-to-bottom';
            scrollBtn.innerHTML = '<i class="fa fa-chevron-down"></i>';
            scrollBtn.title = 'Cuộn xuống cuối';
            
            const chatBox = document.querySelector('.chat-box');
            chatBox.appendChild(scrollBtn);
        }
        
        scrollBtn.style.display = 'flex';
        setTimeout(() => scrollBtn.classList.add('show'), 10);
    }

    hideScrollToBottomButton() {
        this.showScrollButton = false;
        const scrollBtn = document.querySelector('.scroll-to-bottom');
        if (scrollBtn) {
            scrollBtn.classList.remove('show');
            setTimeout(() => {
                if (!this.showScrollButton) {
                    scrollBtn.style.display = 'none';
                }
            }, 300);
        }
    }

    setupScrollIsolation(chatMessages) {
        // Function to setup complete scroll isolation
        if (chatMessages.dataset.scrollIsolated) return;
        
        // Prevent all wheel events from bubbling
        chatMessages.addEventListener('wheel', (e) => {
            e.stopPropagation();
            
            const { scrollTop, scrollHeight, clientHeight } = chatMessages;
            const atTop = scrollTop <= 0;
            const atBottom = scrollTop + clientHeight >= scrollHeight;
            
            // Prevent page scroll at boundaries
            if ((atTop && e.deltaY < 0) || (atBottom && e.deltaY > 0)) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // Prevent touch events on mobile
        chatMessages.addEventListener('touchmove', (e) => {
            e.stopPropagation();
        });
        
        // Prevent keyboard scroll events
        chatMessages.addEventListener('keydown', (e) => {
            if (['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'].includes(e.key)) {
                e.stopPropagation();
            }
        });
        
        chatMessages.dataset.scrollIsolated = 'true';
    }

    async loadAdminMessages(supportId) {
        try {
            const response = await fetch(`ajax/chat_admin.php?action=get_messages&support_id=${supportId}`);
            const data = await response.json();
            
            if (data.success) {
                const messagesContainer = document.querySelector('.chat-messages');
                messagesContainer.innerHTML = '';
                
                data.messages.forEach(message => {
                    this.displayMessage(message.text, message.sender, message.time, false);
                });
                
                this.scrollToBottom();
            } else {
                console.error('Error loading messages:', data.message);
            }
        } catch (error) {
            console.error('Error loading admin messages:', error);
        }
    }

    async loadSessions() {
        const sessionsList = document.querySelector('.sessions-list');
        sessionsList.innerHTML = '<div class="sessions-loading"><div class="loading-spinner"></div><p>Đang tải...</p></div>';
        
        try {
            const response = await fetch('ajax/chat_admin.php?action=get_sessions');
            const data = await response.json();
            
            if (data.success) {
                if (data.sessions.length === 0) {
                    sessionsList.innerHTML = `
                        <div class="sessions-empty">
                            <i class="fa fa-comments"></i>
                            <h4>Chưa có cuộc trò chuyện nào</h4>
                            <p>Bắt đầu chat với admin để được hỗ trợ!</p>
                        </div>
                    `;
                } else {
                    let sessionsHtml = '';
                    data.sessions.forEach(session => {
                        sessionsHtml += `
                            <div class="session-item" data-support-id="${session.support_id}">
                                <div class="session-header">
                                    <span class="session-id">Chat #${session.support_id}</span>
                                    <span class="session-time">${session.last_message}</span>
                                </div>
                                <div class="session-preview">${session.last_message_text}</div>
                                <div class="session-stats">
                                    <span class="session-stat">
                                        <i class="fa fa-comments"></i> ${session.message_count} tin nhắn
                                    </span>
                                    <span class="session-stat">
                                        <i class="fa fa-clock"></i> ${session.started_at}
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    sessionsList.innerHTML = sessionsHtml;
                    
                    // Add click listeners to session items
                    document.querySelectorAll('.session-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const supportId = item.dataset.supportId;
                            this.loadSession(supportId);
                        });
                    });
                }
            } else {
                sessionsList.innerHTML = `
                    <div class="sessions-empty">
                        <i class="fa fa-exclamation-triangle"></i>
                        <h4>Lỗi tải dữ liệu</h4>
                        <p>${data.message}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            sessionsList.innerHTML = `
                <div class="sessions-empty">
                    <i class="fa fa-exclamation-triangle"></i>
                    <h4>Lỗi kết nối</h4>
                    <p>Không thể tải danh sách chat</p>
                </div>
            `;
        }
    }

    loadSession(supportId) {
        this.currentSupportId = supportId;
        this.openChat('admin');
        this.loadAdminMessages(supportId);
    }

    loadSampleMessages() {
        // Sample bot messages  
        this.messages.bot = [
            {
                text: "🤖 Chào bạn! Tôi là AuraBot - trợ lý AI thông minh của AuraDisc!",
                sender: "bot",
                time: "10:30"
            },
            {
                text: "🎵 Tôi có thể giúp bạn tìm kiếm album, nghệ sĩ và trả lời các câu hỏi về sản phẩm. Hãy hỏi tôi bất cứ điều gì!",
                sender: "bot", 
                time: "10:31"
            }
        ];
    }
}

// Initialize chat widget when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new ChatWidget();
});

// Export for external use
window.ChatWidget = ChatWidget; 