<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../app/config/database.php';

try {
    // Fetch user info
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user ? explode('@', $user['email'])[0] : "User";
} catch (PDOException $e) {
    $username = "User";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Chat – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
<style>
:root {
  --primary: #6366f1;
  --primary-dark: #4f46e5;
  --primary-light: #818cf8;
  --secondary: #ec4899;
  --accent: #14b8a6;
  --success: #10b981;
  --bg-dark: #0f172a;
  --bg-card: #1e293b;
  --bg-light: #f8fafc;
  --text-light: #f1f5f9;
  --text-gray: #94a3b8;
  --text-dark: #1e293b;
  --border: #334155;
  --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
  --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.2);
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--bg-dark);
  color: var(--text-light);
  line-height: 1.6;
  min-height: 100vh;
  padding-top: 80px;
}

/* Animated Background */
.bg-pattern {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
  opacity: 0.03;
  background-image: 
    radial-gradient(circle at 20% 50%, var(--primary) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, var(--secondary) 0%, transparent 50%);
  animation: bgMove 20s ease-in-out infinite;
}

@keyframes bgMove {
  0%, 100% { transform: scale(1) rotate(0deg); }
  50% { transform: scale(1.1) rotate(5deg); }
}

/* Chat Container */
.chat-container {
  width: 900px;
  max-width: 95vw;
  min-height: 650px;
  margin: 2rem auto;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  box-shadow: var(--shadow-lg);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Chat Header */
.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  background: rgba(99, 102, 241, 0.05);
  border-bottom: 1px solid var(--border);
}

.chat-title {
  font-family: 'Sora', sans-serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-light);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.chat-title::before {
  content: '🤖';
  font-size: 1.75rem;
}

/* Clear Chat Button */
.btn-clear {
  background: var(--gradient-2);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-clear::before {
  content: '🗑️';
  font-size: 1.1rem;
}

.btn-clear:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(236, 72, 153, 0.4);
}

/* Chat Box */
.chat-box {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  background: var(--bg-dark);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  min-height: 500px;
}

/* Custom Scrollbar */
.chat-box::-webkit-scrollbar {
  width: 8px;
}

.chat-box::-webkit-scrollbar-track {
  background: var(--bg-card);
}

.chat-box::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 4px;
}

.chat-box::-webkit-scrollbar-thumb:hover {
  background: var(--text-gray);
}

/* Chat Messages */
.chat-message {
  max-width: 75%;
  padding: 1rem 1.25rem;
  border-radius: 1.25rem;
  font-size: 1rem;
  line-height: 1.6;
  word-break: break-word;
  box-shadow: var(--shadow-sm);
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-message.you {
  background: var(--gradient-1);
  color: white;
  align-self: flex-end;
  border-bottom-right-radius: 0.25rem;
}

.chat-message.ai {
  background: var(--bg-card);
  color: var(--text-light);
  align-self: flex-start;
  border: 1px solid var(--border);
  border-bottom-left-radius: 0.25rem;
}

/* Typing Indicator */
.typing-indicator {
  display: none;
  align-self: flex-start;
  padding: 1rem 1.25rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  border-bottom-left-radius: 0.25rem;
}

.typing-indicator.active {
  display: block;
}

.typing-indicator span {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--text-gray);
  margin: 0 2px;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

/* Input Form */
form {
  display: flex;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--border);
  background: var(--bg-card);
}

input[type="text"] {
  flex: 1;
  padding: 1rem 1.25rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  font-size: 1rem;
  background: var(--bg-dark);
  color: var(--text-light);
  outline: none;
  transition: var(--transition);
  font-family: 'Inter', sans-serif;
}

input[type="text"]:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

input[type="text"]::placeholder {
  color: var(--text-gray);
}

button[type="submit"] {
  padding: 1rem 2rem;
  background: var(--gradient-1);
  color: white;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

button[type="submit"]:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}

button[type="submit"]::after {
  content: '➤';
  font-size: 1.1rem;
}

button[type="submit"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-gray);
}

.empty-state h3 {
  font-family: 'Sora', sans-serif;
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: var(--text-light);
}

.empty-state p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.suggestion-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: center;
  margin-top: 1.5rem;
}

.chip {
  padding: 0.625rem 1rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 2rem;
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.9rem;
  color: var(--text-gray);
}

.chip:hover {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 900px) {
  .chat-container {
    width: 97vw;
    margin: 1rem auto;
  }
  
  .chat-box {
    padding: 1rem;
  }
  
  form {
    padding: 1rem;
  }
  
  .chat-message {
    max-width: 85%;
  }
}
</style>
</head>
<body>
<div class="bg-pattern"></div>

<!-- Include Navbar -->
<?php include __DIR__ . '/../includes/partials/navbar.php'; ?>

<div class="chat-container">
  <div class="chat-header">
    <div class="chat-title">SkillSync AI Chat</div>
    <button class="btn-clear" id="clearBtn">Clear Chat</button>
  </div>
  
  <div class="chat-box" id="chatBox">
    <div class="empty-state" id="emptyState">
      <h3>👋 Hi <?php echo htmlspecialchars($username); ?>!</h3>
      <p>How can I help you today?</p>
      <div class="suggestion-chips">
        <div class="chip" data-message="Help me improve my resume">📄 Improve my resume</div>
        <div class="chip" data-message="Suggest skills I should learn">💡 Suggest skills to learn</div>
        <div class="chip" data-message="Find me relevant jobs">💼 Find relevant jobs</div>
        <div class="chip" data-message="Career advice for software engineering">🎯 Career advice</div>
      </div>
    </div>
    <div class="typing-indicator" id="typingIndicator">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  
  <form id="chatForm">
    <input type="text" id="message" placeholder="Type your message..." required autocomplete="off">
    <button type="submit" id="sendBtn">Send</button>
  </form>
</div>

<script>
const chatBox = document.getElementById('chatBox');
const form = document.getElementById('chatForm');
const input = document.getElementById('message');
const sendBtn = document.getElementById('sendBtn');
const clearBtn = document.getElementById('clearBtn');
const emptyState = document.getElementById('emptyState');
const typingIndicator = document.getElementById('typingIndicator');
const chips = document.querySelectorAll('.chip');

// Load chat history from session storage
let chatHistory = JSON.parse(sessionStorage.getItem('chatHistory') || '[]');

function renderChat(history) {
  // Remove empty state and typing indicator
  if (emptyState) emptyState.remove();
  typingIndicator.style.display = 'none';
  
  // Clear chat box except typing indicator
  Array.from(chatBox.children).forEach(child => {
    if (child.id !== 'typingIndicator') child.remove();
  });
  
  // Render messages
  history.forEach(chat => {
    const div = document.createElement('div');
    div.className = `chat-message ${chat.sender === 'You' ? 'you' : 'ai'}`;
    div.textContent = chat.message;
    chatBox.insertBefore(div, typingIndicator);
  });
  
  chatBox.scrollTop = chatBox.scrollHeight;
}

// Initial render
if (chatHistory.length > 0) {
  renderChat(chatHistory);
}

// Handle suggestion chips
chips.forEach(chip => {
  chip.addEventListener('click', () => {
    input.value = chip.dataset.message;
    form.dispatchEvent(new Event('submit'));
  });
});

// Handle form submission
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = input.value.trim();
  if (!msg) return;

  // Disable input
  input.disabled = true;
  sendBtn.disabled = true;
  sendBtn.textContent = 'Sending...';

  // Add user message
  chatHistory.push({ sender: 'You', message: msg });
  renderChat(chatHistory);
  input.value = '';

  // Show typing indicator
  typingIndicator.classList.add('active');
  chatBox.scrollTop = chatBox.scrollHeight;

  try {
    // Send to backend
    const res = await fetch('../../app/controllers/chat_backend.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg })
    });
    
    const data = await res.json();
    
    if (data.history) {
      chatHistory = data.history;
      sessionStorage.setItem('chatHistory', JSON.stringify(chatHistory));
      renderChat(chatHistory);
    } else if (data.error) {
      console.error('Error:', data.error);
      chatHistory.push({ 
        sender: 'AI', 
        message: 'Sorry, I encountered an error. Please try again.' 
      });
      renderChat(chatHistory);
    }
  } catch (err) {
    console.error('Fetch error:', err);
    chatHistory.push({ 
      sender: 'AI', 
      message: 'Sorry, I\'m having trouble connecting. Please check your connection and try again.' 
    });
    renderChat(chatHistory);
  } finally {
    // Re-enable input
    input.disabled = false;
    sendBtn.disabled = false;
    sendBtn.textContent = 'Send';
    typingIndicator.classList.remove('active');
    input.focus();
  }
});

// Clear chat
clearBtn.addEventListener('click', () => {
  if (confirm('Are you sure you want to clear the chat history?')) {
    chatHistory = [];
    sessionStorage.removeItem('chatHistory');
    
    // Clear chat box
    Array.from(chatBox.children).forEach(child => {
      if (child.id !== 'typingIndicator') child.remove();
    });
    
    // Restore empty state
    const emptyStateHTML = `
      <div class="empty-state" id="emptyState">
        <h3>👋 Hi <?php echo htmlspecialchars($username); ?>!</h3>
        <p>How can I help you today?</p>
        <div class="suggestion-chips">
          <div class="chip" data-message="Help me improve my resume">📄 Improve my resume</div>
          <div class="chip" data-message="Suggest skills I should learn">💡 Suggest skills to learn</div>
          <div class="chip" data-message="Find me relevant jobs">💼 Find relevant jobs</div>
          <div class="chip" data-message="Career advice for software engineering">🎯 Career advice</div>
        </div>
      </div>
    `;
    chatBox.insertAdjacentHTML('afterbegin', emptyStateHTML);
    
    // Re-attach chip listeners
    document.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        input.value = chip.dataset.message;
        form.dispatchEvent(new Event('submit'));
      });
    });
  }
});

// Auto-focus input
input.focus();
</script>

</body>
</html>