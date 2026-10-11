</div> <!-- /.container -->

<!-- AI AGENT CHATBOT FLOATING WIDGET -->
<button class="ai-bot-trigger" id="aiBotTrigger" title="AI Book Assistant">
    <div class="pulse-badge"></div>
    <i data-lucide="sparkles" style="width:26px; height:26px;"></i>
</button>

<div class="ai-bot-window" id="aiBotWindow">
    <!-- Chat Header -->
    <div class="ai-bot-header">
        <div class="ai-bot-header-title">
            <div class="ai-bot-avatar">
                <i data-lucide="bot" style="width:22px; height:22px;"></i>
            </div>
            <div>
                <h4>Libro AI Assistant</h4>
                <span>Mood & Book Finder • Online</span>
            </div>
        </div>
        <button class="ai-bot-close-btn" id="aiBotCloseBtn" title="Close Chat">
            <i data-lucide="x" style="width:18px; height:18px;"></i>
        </button>
    </div>

    <!-- Mood Quick-Selection Chips Bar -->
    <div class="ai-bot-chips-bar">
        <div class="mood-chip" data-mood="peace">🧘 Peace & Calm</div>
        <div class="mood-chip" data-mood="romantic">💖 Romance</div>
        <div class="mood-chip" data-mood="thrill">🕵️ Suspense</div>
        <div class="mood-chip" data-mood="fantasy">🧙 Fantasy</div>
        <div class="mood-chip" data-mood="drama">🎭 Emotion</div>
        <div class="mood-chip" data-mood="action">⚡ Action</div>
        <div class="mood-chip" data-mood="manga">📚 Manga</div>
    </div>

    <!-- Chat Messages Box -->
    <div class="ai-bot-messages" id="aiBotMessages">
        <!-- Initial Bot Greeting -->
        <div class="chat-msg bot">
            <div class="chat-msg-bubble">
                Hello! 👋 I'm <strong>Libro AI</strong>, your personal reading assistant. <br><br>
                How are you feeling today? Tap a mood chip above or type what kind of book you'd like to read!
            </div>
        </div>
    </div>

    <!-- Chat Input Footer -->
    <form class="ai-bot-footer" id="aiBotForm" autocomplete="off">
        <input type="text" id="aiBotInput" class="ai-bot-input" placeholder="Ask for book suggestions (e.g. peaceful story)..." required />
        <button type="submit" class="ai-bot-send-btn" id="aiBotSendBtn" title="Send message">
            <i data-lucide="send" style="width:18px; height:18px;"></i>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const triggerBtn = document.getElementById('aiBotTrigger');
    const closeBtn   = document.getElementById('aiBotCloseBtn');
    const chatWindow = document.getElementById('aiBotWindow');
    const chatForm   = document.getElementById('aiBotForm');
    const chatInput  = document.getElementById('aiBotInput');
    const chatMsgs   = document.getElementById('aiBotMessages');
    const moodChips  = document.querySelectorAll('.mood-chip');

    // Toggle Chat Window
    triggerBtn.addEventListener('click', () => {
        chatWindow.classList.toggle('open');
        if (chatWindow.classList.contains('open')) {
            chatInput.focus();
        }
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.classList.remove('open');
    });

    // Handle Quick Mood Chips
    moodChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const mood = this.getAttribute('data-mood');
            const chipText = this.innerText.trim();
            sendUserMessage("I'm in the mood for " + chipText, mood);
        });
    });

    // Handle Form Submit
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = chatInput.value.trim();
        if (!query) return;
        sendUserMessage(query, '');
        chatInput.value = '';
    });

    function sendUserMessage(text, mood) {
        // Append User Message Bubble
        appendMsg(text, 'user');

        // Show Typing Indicator
        const typingId = showTypingIndicator();
        scrollToBottom();

        // AJAX POST to aiBotHandler.php
        fetch('aiBotHandler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, mood: mood })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator(typingId);
            if (data.status === 'success') {
                appendBotResponse(data.reply, data.books);
            } else {
                appendMsg(data.reply || "Sorry, I encountered an issue fetching recommendations.", 'bot');
            }
            scrollToBottom();
        })
        .catch(err => {
            removeTypingIndicator(typingId);
            appendMsg("Unable to connect to AI Assistant right now. Please try again.", 'bot');
            scrollToBottom();
        });
    }

    function appendMsg(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg ' + sender;
        
        let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');

        msgDiv.innerHTML = `<div class="chat-msg-bubble">${formattedText}</div>`;
        chatMsgs.appendChild(msgDiv);
        scrollToBottom();
    }

    function appendBotResponse(replyText, books) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg bot';

        let formattedText = replyText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formattedText = formattedText.replace(/\n/g, '<br>');

        let html = `<div class="chat-msg-bubble">${formattedText}`;

        if (books && books.length > 0) {
            html += `<div class="chat-book-cards">`;
            books.forEach(b => {
                const badgeClass = b.is_paid === 'free' ? 'badge-free' : 'badge-paid';
                const badgeLabel = b.is_paid === 'free' ? 'FREE' : ('₹' + b.price);

                html += `
                    <div class="chat-book-card">
                        <img src="${b.cover}" class="chat-book-thumb" alt="${escapeHtml(b.title)}" onerror="this.src='../logo.png'" />
                        <div class="chat-book-info">
                            <div class="chat-book-title">${escapeHtml(b.title)}</div>
                            <div class="chat-book-author">by ${escapeHtml(b.author)}</div>
                            <div class="chat-book-meta">
                                <span class="badge ${badgeClass}">${badgeLabel}</span>
                                <span style="font-size:11px; color:var(--text-muted);">${escapeHtml(b.category)}</span>
                            </div>
                        </div>
                        <a href="bookDetail.php?bid=${b.bid}" class="chat-book-btn">View</a>
                    </div>
                `;
            });
            html += `</div>`;
        }

        html += `</div>`;
        msgDiv.innerHTML = html;
        chatMsgs.appendChild(msgDiv);
        scrollToBottom();
        lucide.createIcons();
    }

    function showTypingIndicator() {
        const id = 'typing_' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg bot';
        msgDiv.id = id;
        msgDiv.innerHTML = `
            <div class="chat-msg-bubble" style="background:#f1f5f9;">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        `;
        chatMsgs.appendChild(msgDiv);
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }
});
</script>
</body>
</html>
