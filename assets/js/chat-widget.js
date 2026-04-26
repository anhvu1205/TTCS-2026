(function () {
    const toggleBtn = document.getElementById('sf-chat-toggle');
    const closeBtn = document.getElementById('sf-chat-close');
    const resetBtn = document.getElementById('sf-chat-reset');
    const panel = document.getElementById('sf-chat-panel');
    const chatBox = document.getElementById('chatBox');
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSendBtn');

    if (!toggleBtn || !panel || !chatBox || !input || !sendBtn) return;

    function getChatUserId() {
        return window.SF_CHAT_USER_ID || 'guest';
    }

    function getSessionKey() {
        return 'sf_chat_session_id_' + getChatUserId();
    }

    function getSessionId() {
        const key = getSessionKey();
        let sid = localStorage.getItem(key);

        if (!sid) {
            sid = 'sess_' + getChatUserId() + '_' + Math.random().toString(36).slice(2) + '_' + Date.now();
            localStorage.setItem(key, sid);
        }

        return sid;
    }

    function getHistoryKey() {
        return 'sf_chat_history_' + getChatUserId();
    }

    function saveChatHistory() {
        localStorage.setItem(getHistoryKey(), chatBox.innerHTML);
    }

    function loadChatHistory() {
        const saved = localStorage.getItem(getHistoryKey());
        if (saved) {
            chatBox.innerHTML = saved;
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    function clearChatHistory() {
        localStorage.removeItem(getHistoryKey());
        localStorage.removeItem(getSessionKey());

        chatBox.innerHTML = '';

        addMessage(
            'assistant',
            'Bạn muốn tư vấn áo, quần, váy hay sản phẩm theo giá?'
        );
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function addMessage(role, text, isHtml = false) {
        const row = document.createElement('div');
        row.className = 'sf-chat-row ' + role;

        const bubble = document.createElement('div');
        bubble.className = 'sf-chat-bubble';

        bubble.innerHTML = isHtml ? text : escapeHtml(text);

        row.appendChild(bubble);
        chatBox.appendChild(row);
        chatBox.scrollTop = chatBox.scrollHeight;

        saveChatHistory();
    }

    function openChat() {
        panel.style.display = 'block';
        toggleBtn.style.display = 'none';

        if (chatBox.innerHTML.trim() === '') {
            loadChatHistory();
        }

        if (chatBox.innerHTML.trim() === '') {
            addMessage(
                'assistant',
                'Xin chào 👋 Mình có thể tư vấn sản phẩm, giá, danh mục, mã giảm giá, giao hàng hoặc gợi ý đồ phù hợp cho bạn.'
            );
        }

        input.focus();
    }

    function closeChat() {
        panel.style.display = 'none';
        toggleBtn.style.display = 'flex';
    }

    async function sendChat(customMessage = null) {
        const message = customMessage || input.value.trim();
        if (!message) return;

        addMessage('user', message);
        input.value = '';

        const loadingRow = document.createElement('div');
        loadingRow.className = 'sf-chat-row assistant';
        loadingRow.id = 'sf-chat-loading';
        loadingRow.innerHTML = '<div class="sf-chat-bubble">Đang xử lý...</div>';
        chatBox.appendChild(loadingRow);
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            const res = await fetch('./api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: getSessionId(),
                    message: message
                })
            });

            const text = await res.text();
            let data = {};

            try {
                data = JSON.parse(text);
            } catch (e) {
                data = {
                    reply_html: 'Response không phải JSON:<br>' + escapeHtml(text)
                };
            }

            const loading = document.getElementById('sf-chat-loading');
            if (loading) loading.remove();

            if (data.reply_html) {
                addMessage('assistant', data.reply_html, true);
            } else {
                addMessage('assistant', data.reply || data.error || 'Bot chưa có phản hồi.');
            }
        } catch (err) {
            const loading = document.getElementById('sf-chat-loading');
            if (loading) loading.remove();

            addMessage('assistant', 'Có lỗi khi kết nối bot: ' + err.message);
        }
    }

    toggleBtn.addEventListener('click', openChat);

    if (closeBtn) {
        closeBtn.addEventListener('click', closeChat);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', clearChatHistory);
    }

    sendBtn.addEventListener('click', function () {
        sendChat();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            sendChat();
        }
    });

    document.querySelectorAll('.sf-quick-actions button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sendChat(this.dataset.msg);
        });
    });

    chatBox.addEventListener('click', function (e) {
        const choice = e.target.closest('[data-msg]');
        if (choice) {
            e.preventDefault();
            sendChat(choice.dataset.msg);
            return;
        }

        const addCartBtn = e.target.closest('.chat-add-cart');
        if (addCartBtn) {
            e.preventDefault();

            const id = addCartBtn.dataset.id;
            addCartBtn.disabled = true;
            addCartBtn.textContent = 'Đang thêm...';

            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id) + '&add_to_cart=1'
            })
                .then(function () {
                    addCartBtn.textContent = 'Đã thêm ✓';
                    addCartBtn.classList.add('added');
                })
                .catch(function () {
                    addCartBtn.disabled = false;
                    addCartBtn.textContent = '+ Giỏ';
                    alert('Không thêm được vào giỏ hàng.');
                });
        }
    });
})();