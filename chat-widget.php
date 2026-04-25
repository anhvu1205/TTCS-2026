<link rel="stylesheet" href="assets/css/chat-widget.css">

<div id="sf-chat-widget">
    <button id="sf-chat-toggle" type="button" aria-label="Mở chatbot">
        <span>💬</span>
    </button>

    <div id="sf-chat-panel">
        <div class="sf-chat-header">
            <div>
                <div class="sf-chat-title">SimpleFit Assistant</div>
                <div class="sf-chat-subtitle">Tư vấn sản phẩm miễn phí</div>
            </div>
            <button id="sf-chat-close" type="button" aria-label="Đóng chatbot">×</button>
        </div>

        <div id="chatBox" class="sf-chat-body"></div>

        <div class="sf-quick-actions">
            <button data-msg="Tư vấn áo">Áo</button>
            <button data-msg="Tư vấn quần">Quần</button>
            <button data-msg="Tư vấn váy">Váy</button>
            <button data-msg="Sản phẩm dưới 500k">Dưới 500k</button>
            <button data-msg="Có mã giảm giá không">Mã giảm giá</button>
            <button data-msg="Ship bao lâu">Ship</button>
        </div>

        <div class="sf-chat-footer">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn...">
            <button id="chatSendBtn" type="button">Gửi</button>
        </div>
    </div>
</div>

<script src="assets/js/chat-widget.js"></script>