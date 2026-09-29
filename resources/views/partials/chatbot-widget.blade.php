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
function toggleChat(){ document.getElementById('chatCard').classList.toggle('d-none'); }
function chatAdd(who, text){
  const d = document.createElement('div'); d.className = 'msg msg-' + who; d.textContent = text;
  const l = document.getElementById('chatLog'); l.appendChild(d); l.scrollTop = l.scrollHeight; return d;
}
async function sendChat(text){
  text = (text || '').trim(); if (!text) return;
  chatAdd('me', text);
  const wait = chatAdd('bot', '…');
  try {
    const r = await fetch(chatUrl, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({message:text})});
    const d = await r.json();
    wait.textContent = d.reply || 'Sorry, I could not answer that. Please contact our staff.';
  } catch (e) { wait.textContent = 'Connection problem. Please try again.'; }
}
document.getElementById('chatForm').addEventListener('submit', e => {
  e.preventDefault(); const i = document.getElementById('chatInput'); const t = i.value; i.value = ''; sendChat(t);
});
</script>