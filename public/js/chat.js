// --- CONFIG ---
const APP_SOCKET_URL = 'https://keloola-bos-management.test:6001'; // Ganti sesuai env
const ITEM_REQUEST_ID = window.ITEM_REQUEST_ID; // Pastikan ini didefinisikan dari Blade
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// --- INIT SOCKET.IO ---
const socket = io(APP_SOCKET_URL, {
    transports: ['websocket'],
    secure: true
});

// --- INIT ECHO ---
const echo = new Echo({
    broadcaster: 'socket.io',
    host: APP_SOCKET_URL,
    client: io,
    transports: ['websocket'],
    secure: true
});

// --- DOM ELEM ---
const chatInput = document.getElementById('chat-input');
const chatSendBtn = document.getElementById('chat-send');
const chatContainer = document.getElementById('chat-box');
const notifSound = document.getElementById('notification-sound');

// --- EVENT: SEND MESSAGE ---
chatSendBtn?.addEventListener('click', function () {
    const message = chatInput.value.trim();
    if (!message) return;

    fetch('/chat/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify({
            item_request_id: ITEM_REQUEST_ID,
            message: message
        })
    }).then(res => res.json()).then(() => {
        chatInput.value = '';
    });
});

// --- EVENT: RECEIVE MESSAGE ---
echo.private(`chat.item-request.${ITEM_REQUEST_ID}`)
    .listen('ChatMessageSent', (e) => {
        const html = `
            <div class="mb-2">
                <strong>${e.sender_name}:</strong> ${e.message}
                <div class="text-muted" style="font-size: 12px;">
                    ${new Date(e.created_at).toLocaleTimeString()}
                </div>
            </div>`;
        chatContainer.innerHTML += html;
        chatContainer.scrollTop = chatContainer.scrollHeight;

        notifSound?.play();
    });

// --- DEBUGGING LOG ---
echo.connector.socket.on('connect', () => {
    console.log('✅ Echo connected via Socket.IO:', socket.id);
});

echo.connector.socket.on('connect_error', (err) => {
    console.error('❌ Echo connection error:', err);
});