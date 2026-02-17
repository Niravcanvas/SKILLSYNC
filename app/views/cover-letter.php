<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

// Get user data for context
try {
    $stmt = $pdo->prepare("SELECT full_name, headline FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $user['full_name'] ?? 'Your Name';
} catch (PDOException $e) {
    $name = 'Your Name';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cover Letter Generator – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --primary: #6366f1; --secondary: #ec4899; --accent: #14b8a6;
  --bg: #080e1a; --bg-card: #111827; --bg-lift: #161f31; --bg-input: #0d1424;
  --border: #1f2d45; --border-lit: #2e3f5e; --text: #f1f5f9; --muted: #64748b;
  --g1: linear-gradient(135deg, #6366f1, #8b5cf6);
  --g2: linear-gradient(135deg, #ec4899, #f43f5e);
}
html { scroll-behavior: smooth; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding-top: 80px; }
body::before {
  content: ''; position: fixed; inset: 0; z-index: -1;
  background: radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%),
              radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.05) 0%, transparent 70%);
}
.wrap { max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem; }
.hero {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2rem 2.5rem; margin-bottom: 2rem;
  position: relative; overflow: hidden;
}
.hero::after {
  content: ''; position: absolute; top: -60px; right: -60px; width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(236,72,153,.1) 0%, transparent 70%); pointer-events: none;
}
.hero-label {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
  color: var(--secondary); background: rgba(236,72,153,.1);
  border: 1px solid rgba(236,72,153,.2); padding: .25rem .8rem;
  border-radius: 999px; margin-bottom: 1rem;
}
.hero-label .dot { width: 6px; height: 6px; background: var(--secondary); border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
.hero h1 { font-family: 'Sora', sans-serif; font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700; margin-bottom: .6rem; }
.hero h1 em { font-style: normal; background: var(--g2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero p { color: var(--muted); font-size: .95rem; }

.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; }
.card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.25rem; padding: 2rem; position: relative; overflow: hidden;
  transition: border-color .3s;
}
.card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  transform: scaleX(0); transform-origin: left; transition: transform .4s;
}
.card:hover { border-color: var(--border-lit); }
.card:hover::before { transform: scaleX(1); }
.card.c1::before { background: var(--g1); }
.card.c2::before { background: var(--g2); }

.card-title {
  font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 600;
  display: flex; align-items: center; gap: .6rem;
  margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
}
.card-icon {
  width: 32px; height: 32px; border-radius: .6rem;
  background: rgba(99,102,241,.15);
  display: flex; align-items: center; justify-content: center;
}
.card.c2 .card-icon { background: rgba(236,72,153,.15); }

.form-group { margin-bottom: 1.1rem; }
.form-group label {
  display: block; margin-bottom: .45rem;
  font-size: .85rem; font-weight: 500; color: var(--text);
}
input[type="text"], textarea, select {
  width: 100%; background: var(--bg-input); border: 1px solid var(--border);
  border-radius: .75rem; padding: .7rem 1rem; font-size: .88rem;
  font-family: 'Inter', sans-serif; color: var(--text);
  transition: all .3s;
}
input:focus, textarea:focus, select:focus {
  outline: none; border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
textarea { resize: vertical; min-height: 150px; }
select { cursor: pointer; }

.btn {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .8rem 1.6rem; font-weight: 600; font-size: .88rem;
  border: none; border-radius: .85rem; cursor: pointer;
  transition: transform .25s, box-shadow .25s;
}
.btn-primary { background: var(--g2); color: #fff; box-shadow: 0 4px 16px rgba(236,72,153,.3); width: 100%; justify-content: center; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(236,72,153,.5); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn svg { width: 16px; height: 16px; }

.output-area {
  background: #fff; border-radius: .75rem; padding: 2rem;
  min-height: 400px; color: #222; font-size: .92rem; line-height: 1.8;
  white-space: pre-wrap; font-family: 'Georgia', serif;
}
.output-placeholder {
  color: #999; font-style: italic; text-align: center;
  padding: 4rem 2rem; font-family: 'Inter', sans-serif;
}

.copy-btn {
  background: var(--g1); margin-top: 1rem;
}
.copy-btn:hover { background: linear-gradient(135deg, #7c3aed, #a855f7); }

.loading-spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 2rem; text-align: center; }
footer p { color: var(--muted); font-size: .85rem; }

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.hero { animation: fadeUp .5s ease both; }
.grid { animation: fadeUp .5s .1s ease both; }

@media (max-width: 1100px) {
  .grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .wrap { padding: 1.5rem 1rem; }
  .hero { padding: 1.5rem; }
}
</style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<div class="wrap">
  <div class="hero">
    <div class="hero-label"><span class="dot"></span> AI Cover Letter</div>
    <h1>Generate <em>Tailored Cover Letters</em></h1>
    <p>Let AI craft the perfect cover letter for each job. Personalized, professional, and ready in seconds.</p>
  </div>

  <div class="grid">
    <div class="card c1">
      <div class="card-title">
        <div class="card-icon">
          <svg width="16" height="16" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
        </div>
        Job Details
      </div>

      <form id="coverLetterForm">
        <div class="form-group">
          <label>Job Title *</label>
          <input type="text" name="job_title" placeholder="e.g., Senior Frontend Developer" required>
        </div>

        <div class="form-group">
          <label>Company Name *</label>
          <input type="text" name="company" placeholder="e.g., Google" required>
        </div>

        <div class="form-group">
          <label>Job Description *</label>
          <textarea name="job_description" placeholder="Paste the job description here..." required></textarea>
        </div>

        <div class="form-group">
          <label>Tone</label>
          <select name="tone">
            <option value="professional">Professional</option>
            <option value="enthusiastic">Enthusiastic</option>
            <option value="formal">Formal</option>
            <option value="creative">Creative</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary" id="generateBtn">
          <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Generate Cover Letter
        </button>
      </form>
    </div>

    <div class="card c2">
      <div class="card-title">
        <div class="card-icon">
          <svg width="16" height="16" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
        </div>
        Your Cover Letter
      </div>

      <div class="output-area" id="output">
        <div class="output-placeholder">
          Your AI-generated cover letter will appear here. Fill in the job details and click "Generate".
        </div>
      </div>

      <button type="button" class="btn btn-primary copy-btn" id="copyBtn" style="display:none;">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
        Copy to Clipboard
      </button>
    </div>
  </div>
</div>

<footer><p>&copy; 2025 Skillsync AI. All Rights Reserved.</p></footer>

<script>
const form = document.getElementById('coverLetterForm');
const generateBtn = document.getElementById('generateBtn');
const output = document.getElementById('output');
const copyBtn = document.getElementById('copyBtn');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(form);
  const btnText = generateBtn.innerHTML;
  
  generateBtn.disabled = true;
  generateBtn.innerHTML = '<svg class="loading-spinner" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Generating...';
  output.innerHTML = '<div class="output-placeholder">AI is crafting your cover letter...</div>';
  copyBtn.style.display = 'none';
  
  try {
    const response = await fetch('../../app/controllers/cover_letter_backend.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success && data.cover_letter) {
      output.textContent = data.cover_letter;
      copyBtn.style.display = 'inline-flex';
    } else {
      output.innerHTML = `<div class="output-placeholder" style="color:#ef4444">${data.error || 'Failed to generate cover letter'}</div>`;
    }
  } catch (error) {
    output.innerHTML = '<div class="output-placeholder" style="color:#ef4444">Network error. Please try again.</div>';
  } finally {
    generateBtn.disabled = false;
    generateBtn.innerHTML = btnText;
  }
});

copyBtn.addEventListener('click', () => {
  navigator.clipboard.writeText(output.textContent);
  const original = copyBtn.innerHTML;
  copyBtn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
  setTimeout(() => { copyBtn.innerHTML = original; }, 2000);
});
</script>

</body>
</html>