import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key : "e4CAOxd0XO9zTOmLKalk",
    wsHost: "ws.keloola.xyz",
    wsPort: 8080,
    wssPort: 443,
    forceTLS: true, // jika menggunakan https
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/authorize',
    disableStats: true,
});


window.Echo.private('chat.item-request.'+itemRequestId).listen('ChatMessageSent', (e) => 
{
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
    loadChat();
    loadWorkflow();
});