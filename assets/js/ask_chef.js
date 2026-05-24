let askChefCleanupFns = [];
let typingTimeout = null;

function getTimeString() {
    const now = new Date();
    return now.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function escapeHtml(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

export function initAskChef() {
    askChefCleanupFns.forEach((fn) => fn());
    askChefCleanupFns = [];
    if (typingTimeout) {
        clearTimeout(typingTimeout);
        typingTimeout = null;
    }

    const messagesContainer = document.getElementById("chat-messages");
    const input = document.getElementById("chat-input");
    const sendBtn = document.getElementById("send-btn");
    const typingIndicator = document.getElementById("typing-indicator");
    const suggestionChips = document.querySelectorAll(".suggestion-chip");

    if (!messagesContainer || !input || !sendBtn) return;

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function appendUserMessage(text) {
        const wrapper = document.createElement("div");
        wrapper.className = "message user-message";
        wrapper.innerHTML = `
            <div class="message-avatar"><i class="fa-solid fa-user"></i></div>
            <div class="message-bubble">
                <p>${escapeHtml(text)}</p>
                <span class="message-time">${getTimeString()}</span>
            </div>
        `;
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function appendChefMessage(text) {
        const wrapper = document.createElement("div");
        wrapper.className = "message chef-message";
        wrapper.innerHTML = `
            <div class="message-avatar"><i class="fa-solid fa-utensils"></i></div>
            <div class="message-bubble">
                <p>${escapeHtml(text)}</p>
                <span class="message-time">${getTimeString()}</span>
            </div>
        `;
        messagesContainer.appendChild(wrapper);
        scrollToBottom();
    }

    function showTyping() {
        if (typingIndicator) typingIndicator.classList.add("visible");
        scrollToBottom();
    }

    function hideTyping() {
        if (typingIndicator) typingIndicator.classList.remove("visible");
    }

    async function fetchChefResponse(text) {
        try {
            const response = await fetch("/api/ask-chef", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: text }),
            });
            const data = await response.json();
            return (
                data.response ||
                "I apologize, I am having trouble connecting to my kitchen right now."
            );
        } catch (e) {
            return "I apologize, I am having trouble connecting to my kitchen right now.";
        }
    }

    async function handleSend(text) {
        const trimmed = text.trim();
        if (!trimmed) return;

        appendUserMessage(trimmed);
        input.value = "";
        input.focus();

        showTyping();

        const response = await fetchChefResponse(trimmed);
        hideTyping();
        appendChefMessage(response);
    }

    const onSendClick = () => handleSend(input.value);
    const onInputKeydown = (e) => {
        if (e.key === "Enter") handleSend(input.value);
    };

    sendBtn.addEventListener("click", onSendClick);
    input.addEventListener("keydown", onInputKeydown);
    askChefCleanupFns.push(() => {
        sendBtn.removeEventListener("click", onSendClick);
        input.removeEventListener("keydown", onInputKeydown);
    });

    suggestionChips.forEach((chip) => {
        const handler = () => {
            const question = chip.getAttribute("data-question");
            if (question) handleSend(question);
        };
        chip.addEventListener("click", handler);
        askChefCleanupFns.push(() =>
            chip.removeEventListener("click", handler),
        );
    });

    scrollToBottom();
}
