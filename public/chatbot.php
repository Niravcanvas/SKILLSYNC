<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../app/config/database.php';

try {
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user['full_name'] ?? explode('@', $user['email'])[0] ?? 'there';
} catch (PDOException $e) {
    $username = 'there';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Assistant – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../public/images/favicon.svg">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --primary:    #6366f1;
    --secondary:  #ec4899;
    --accent:     #14b8a6;
    --bg:         #080e1a;
    --bg-card:    #111827;
    --bg-input:   #0d1424;
    --bg-lift:    #161f31;
    --border:     #1f2d45;
    --border-lit: #2e3f5e;
    --text:       #f1f5f9;
    --muted:      #64748b;
    --g1: linear-gradient(135deg, #6366f1, #8b5cf6);
    --g2: linear-gradient(135deg, #ec4899, #f43f5e);
    --g3: linear-gradient(135deg, #14b8a6, #06b6d4);
  }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    padding-top: 80px;
    display: flex;
    flex-direction: column;
  }

  body::before {
    content: ''; position: fixed; inset: 0; z-index: -1;
    background:
      radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%),
      radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.05) 0%, transparent 70%);
  }

  .wrap {
    max-width: 860px; margin: 0 auto; padding: 2rem 1.5rem 2rem;
    flex: 1; display: flex; flex-direction: column; gap: 1.25rem;
    width: 100%;
  }

  /* ── Hero ── */
  .hero {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.5rem; padding: 1.75rem 2rem;
    position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    animation: fadeUp .4s ease both;
  }
  .hero::after {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
    pointer-events: none;
  }
  .hero-label {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
    color: var(--primary); background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.2); padding: .25rem .8rem;
    border-radius: 999px; margin-bottom: .6rem;
  }
  .hero-label .dot { width:6px; height:6px; background:var(--primary); border-radius:50%; animation:pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

  .hero h1 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(1.2rem, 2.5vw, 1.6rem);
    font-weight: 700; letter-spacing: -.02em;
  }
  .hero h1 em {
    font-style: normal; background: var(--g1);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  }
  .hero p { color: var(--muted); font-size: .88rem; margin-top: .3rem; }

  .clear-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .6rem 1.1rem;
    background: transparent; color: var(--muted);
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .82rem;
    border: 1px solid var(--border); border-radius: .75rem; cursor: pointer;
    transition: border-color .25s, color .25s, background .25s;
    flex-shrink: 0;
  }
  .clear-btn:hover { border-color: var(--border-lit); color: var(--text); background: var(--bg-lift); }

  /* ── Chat window ── */
  .chat-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.5rem; overflow: hidden;
    display: flex; flex-direction: column;
    flex: 1; min-height: 520px;
    animation: fadeUp .4s .08s ease both;
  }

  /* Messages area */
  .chat-messages {
    flex: 1; padding: 1.75rem; overflow-y: auto;
    display: flex; flex-direction: column; gap: 1rem;
    background: var(--bg);
  }

  .chat-messages::-webkit-scrollbar { width: 5px; }
  .chat-messages::-webkit-scrollbar-track { background: transparent; }
  .chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

  /* ── Empty / welcome ── */
  .welcome {
    margin: auto; text-align: center; max-width: 420px;
    padding: 2rem 1rem;
  }
  .welcome-icon {
    width: 56px; height: 56px; border-radius: 1rem;
    background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
  }
  .welcome h3 {
    font-family: 'Sora', sans-serif; font-size: 1.2rem; font-weight: 700;
    margin-bottom: .4rem;
  }
  .welcome p { color: var(--muted); font-size: .88rem; margin-bottom: 1.5rem; }

  .chips { display: flex; flex-wrap: wrap; gap: .6rem; justify-content: center; }
  .chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .48rem .95rem;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 999px; font-size: .8rem; color: var(--muted);
    cursor: pointer; transition: border-color .2s, color .2s, background .2s, transform .2s;
  }
  .chip:hover { border-color: var(--primary); color: var(--text); background: var(--bg-lift); transform: translateY(-2px); }

  /* ── Messages ── */
  .msg {
    max-width: 78%; display: flex; flex-direction: column; gap: .25rem;
    animation: msgIn .3s ease both;
  }
  @keyframes msgIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

  .msg.user { align-self: flex-end; }
  .msg.ai   { align-self: flex-start; }

  .msg-bubble {
    padding: .85rem 1.1rem; border-radius: 1.1rem;
    font-size: .88rem; line-height: 1.65; word-break: break-word;
  }
  .msg.user .msg-bubble {
    background: var(--g1); color: #fff;
    border-bottom-right-radius: .3rem;
  }
  .msg.ai .msg-bubble {
    background: var(--bg-card); color: var(--text);
    border: 1px solid var(--border);
    border-bottom-left-radius: .3rem;
  }
  .msg-time { font-size: .7rem; color: var(--muted); }
  .msg.user .msg-time { text-align: right; }

  /* Typing indicator */
  .typing {
    align-self: flex-start; display: none;
    padding: .85rem 1.1rem;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.1rem; border-bottom-left-radius: .3rem;
    gap: .3rem;
  }
  .typing.active { display: flex; align-items: center; }
  .typing span {
    display: inline-block; width: 7px; height: 7px;
    border-radius: 50%; background: var(--muted);
    animation: bounce 1.3s infinite;
  }
  .typing span:nth-child(2) { animation-delay: .18s; }
  .typing span:nth-child(3) { animation-delay: .36s; }
  @keyframes bounce { 0%,60%,100%{transform:translateY(0);opacity:.6} 30%{transform:translateY(-8px);opacity:1} }

  /* ── Input bar ── */
  .chat-input-bar {
    display: flex; align-items: center; gap: .75rem;
    padding: 1.1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: var(--bg-card);
  }

  .chat-input-bar input {
    flex: 1; padding: .75rem 1rem;
    background: var(--bg-input); border: 1px solid var(--border);
    border-radius: .85rem; color: var(--text); font-size: .88rem;
    font-family: 'Inter', sans-serif;
    transition: border-color .25s, box-shadow .25s;
  }
  .chat-input-bar input:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
  }
  .chat-input-bar input::placeholder { color: var(--muted); }
  .chat-input-bar input:disabled { opacity: .5; cursor: not-allowed; }

  .send-btn {
    width: 42px; height: 42px; border-radius: .75rem; flex-shrink: 0;
    background: var(--g1); color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 12px rgba(99,102,241,.35);
    transition: transform .25s, box-shadow .25s, filter .25s;
  }
  .send-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,.5); filter: brightness(1.08); }
  .send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
  .send-btn svg { width: 17px; height: 17px; stroke: #fff; fill: none; }

  @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

  footer { border-top: 1px solid var(--border); padding: 1.5rem 2rem; }
  .footer-inner { max-width: 860px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
  .footer-brand { font-family:'Sora',sans-serif; font-weight:700; font-size:.9rem; background:var(--g1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .footer-copy  { font-size:.78rem; color:var(--muted); }

  @media (max-width: 600px) {
    .wrap { padding: 1rem .75rem 1rem; }
    .hero { padding: 1.25rem; }
    .msg { max-width: 90%; }
    .chat-messages { padding: 1rem; }
    .chat-input-bar { padding: .85rem 1rem; }
  }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/partials/navbar.php'; ?>

<div class="wrap">

  <!-- Hero bar -->
  <div class="hero">
    <div>
      <div class="hero-label"><span class="dot"></span> AI Assistant</div>
      <h1>Chat with <em>Skillsync AI</em></h1>
      <p>Career guidance, skill advice, resume help — ask me anything.</p>
    </div>
    <button class="clear-btn" id="clearBtn">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
      Clear Chat
    </button>
  </div>

  <!-- Chat window -->
  <div class="chat-card">
    <div class="chat-messages" id="chatMessages">

      <!-- Welcome (hidden once chatting) -->
      <div class="welcome" id="welcome">
        <div class="welcome-icon">
          <svg width="26" height="26" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
            <circle cx="12" cy="17" r=".5" fill="#818cf8"/>
          </svg>
        </div>
        <h3>Hey <?= htmlspecialchars($username) ?>! 👋</h3>
        <p>I'm your Skillsync AI career assistant. Ask me anything about your career, skills, or resume.</p>
        <div class="chips" id="chips">
          <span class="chip" data-msg="Help me improve my resume">📄 Improve my resume</span>
          <span class="chip" data-msg="What skills should I learn next?">💡 Skills to learn</span>
          <span class="chip" data-msg="Give me career advice for a software developer">🎯 Career advice</span>
          <span class="chip" data-msg="How do I prepare for a technical interview?">🧠 Interview prep</span>
        </div>
      </div>

      <!-- Typing indicator -->
      <div class="typing" id="typing"><span></span><span></span><span></span></div>

    </div>

    <!-- Input bar -->
    <div class="chat-input-bar">
      <input type="text" id="msgInput" placeholder="Ask me anything about your career…" autocomplete="off">
      <button class="send-btn" id="sendBtn" title="Send">
        <svg viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
      </button>
    </div>
  </div>

</div>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">Skillsync AI</div>
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

<script>
const messagesEl = document.getElementById('chatMessages');
const inputEl    = document.getElementById('msgInput');
const sendBtn    = document.getElementById('sendBtn');
const clearBtn   = document.getElementById('clearBtn');
const typing     = document.getElementById('typing');
const welcome    = document.getElementById('welcome');

let history = JSON.parse(sessionStorage.getItem('sk_chat') || '[]');

// ── Helpers ────────────────────────────────────────────────────────────────
function now() {
  return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function addMsg(text, role) {
  if (welcome) welcome.remove();

  const wrap = document.createElement('div');
  wrap.className = `msg ${role}`;
  wrap.innerHTML = `
    <div class="msg-bubble">${text.replace(/\n/g, '<br>')}</div>
    <div class="msg-time">${now()}</div>`;
  messagesEl.insertBefore(wrap, typing);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

function setLoading(on) {
  inputEl.disabled  = on;
  sendBtn.disabled  = on;
  typing.classList.toggle('active', on);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

// ── Render saved history ───────────────────────────────────────────────────
if (history.length) {
  history.forEach(m => addMsg(m.message, m.sender === 'You' ? 'user' : 'ai'));
}

// ── Send ───────────────────────────────────────────────────────────────────
async function send(text) {
  if (!text.trim()) return;
  inputEl.value = '';

  history.push({ sender: 'You', message: text });
  addMsg(text, 'user');
  setLoading(true);

  try {
    const res  = await fetch('../app/controllers/chat_backend.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text })
    });
    const data = await res.json();

    if (data.history) {
      history = data.history;
      sessionStorage.setItem('sk_chat', JSON.stringify(history));
      const last = history[history.length - 1];
      if (last && last.sender !== 'You') addMsg(last.message, 'ai');
    } else {
      addMsg(data.error || 'Something went wrong. Please try again.', 'ai');
    }
  } catch {
    addMsg("I'm having trouble connecting. Please check your connection and try again.", 'ai');
  } finally {
    setLoading(false);
    inputEl.focus();
  }
}

// ── Events ─────────────────────────────────────────────────────────────────
sendBtn.addEventListener('click', () => send(inputEl.value));
inputEl.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(inputEl.value); } });

// Chips
document.querySelectorAll('.chip').forEach(chip => {
  chip.addEventListener('click', () => send(chip.dataset.msg));
});

// Clear
clearBtn.addEventListener('click', () => {
  if (!confirm('Clear chat history?')) return;
  history = [];
  sessionStorage.removeItem('sk_chat');

  // Remove all messages, put welcome back
  Array.from(messagesEl.children).forEach(c => {
    if (c.id !== 'typing') c.remove();
  });

  const w = document.createElement('div');
  w.className = 'welcome'; w.id = 'welcome';
  w.innerHTML = `
    <div class="welcome-icon">
      <svg width="26" height="26" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <circle cx="12" cy="17" r=".5" fill="#818cf8"/>
      </svg>
    </div>
    <h3>Chat cleared! 👋</h3>
    <p>Start a new conversation below.</p>
    <div class="chips">
      <span class="chip" data-msg="Help me improve my resume">📄 Improve my resume</span>
      <span class="chip" data-msg="What skills should I learn next?">💡 Skills to learn</span>
      <span class="chip" data-msg="Give me career advice for a software developer">🎯 Career advice</span>
      <span class="chip" data-msg="How do I prepare for a technical interview?">🧠 Interview prep</span>
    </div>`;
  messagesEl.insertBefore(w, typing);

  w.querySelectorAll('.chip').forEach(c => {
    c.addEventListener('click', () => send(c.dataset.msg));
  });
});

// Auto-focus
inputEl.focus();
</script>

</body>
</html>