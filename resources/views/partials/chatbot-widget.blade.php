{{-- Owner: Royvil — POSTs to /chatbot/message, expects {"reply": "..."} --}}
<button type="button" class="chat-launcher no-print" onclick="toggleChat()" aria-label="Open Diabot"><i class="bi bi-robot"></i></button>
<div id="chatCard" class="chat-card d-none no-print">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h5 class="mb-0">Hi! How can I help you?</h5>
            <small class="text-muted">My name is Diabot! I'll be your assistant for today.</small>
        </div>
        <button type="button" class="btn-close" onclick="toggleChat()" aria-label="Close"></button>
    </div>
    <div id="chatLog" class="chat-log my-2"></div>
    <div class="d-flex flex-wrap gap-1 mb-2">
        @foreach (['What are your operating hours?', 'How do I check my appointment schedule?', 'What should I bring to my appointment?'] as $q)
            <button type="button" class="chip" data-q="{{ $q }}" onclick="sendChat(this.dataset.q)">{{ $q }}</button>
        @endforeach
    </div>
    <form id="chatForm" class="d-flex gap-2">
        <input id="chatInput" class="form-control" maxlength="500" placeholder="What do you need?" autocomplete="off">
        <button type="submit" class="btn btn-navy" aria-label="Send"><i class="bi bi-send"></i></button>
    </form>
</div>
<script>
const chatUrl = "{{ route('chatbot.message') }}";
let chatRequestPending = false;

function toggleChat(){ document.getElementById('chatCard').classList.toggle('d-none'); }
function chatAdd(who, text){
  const d = document.createElement('div'); d.className = 'msg msg-' + who; d.textContent = text;
  const l = document.getElementById('chatLog'); l.appendChild(d); l.scrollTop = l.scrollHeight; return d;
}

async function sendChat(text) {
    text = (text || '').trim();

    if (!text || chatRequestPending) return;

    chatRequestPending = true;
    const input = document.getElementById('chatInput');
    const sendButton = document.querySelector('#chatForm button[type="submit"]');

    chatAdd('me', text);
    const wait = chatAdd('bot', 'Diabot is typing...');
    if (sendButton) sendButton.disabled = true;

    try {
        const csrfMeta = document.querySelector(
            'meta[name="csrf-token"]'
        );

        if (!csrfMeta) {
            throw new Error('Your session is missing a security token. Please refresh the page and try again.');
        }

        const response = await fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content
            },
            body: JSON.stringify({ message: text })
        });

        let data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok) {
            const reason = response.status === 419
                ? 'Your session expired. Refresh the page and try again.'
                : response.status === 429
                    ? 'Too many requests. Please wait a moment and try again.'
                    : 'The chatbot is temporarily unavailable. Please try again in a moment.';
            throw new Error(reason);
        }

        if (typeof data.reply !== 'string' || !data.reply.trim()) {
            throw new Error('The chatbot returned an empty reply. Please try again.');
        }

        wait.textContent = data.reply;
    } catch (error) {
        console.error('Chatbot request failed:', error);
        wait.textContent = error && error.message ? error.message : 'Unable to contact the chatbot right now. Please try again.';
    } finally {
        chatRequestPending = false;
        if (sendButton) sendButton.disabled = false;
        if (input) {
            input.value = '';
            input.focus();
        }
    }
}

document.getElementById('chatForm').addEventListener('submit', e => {
  e.preventDefault();
  const i = document.getElementById('chatInput');
  const t = i.value;
  sendChat(t);
});
</script>