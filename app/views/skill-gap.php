<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

// Get user's skills
try {
    $stmt = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $user_skills = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Skill Gap Analysis – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --primary: #6366f1; --secondary: #ec4899; --accent: #14b8a6; --success: #10b981;
  --bg: #080e1a; --bg-card: #111827; --bg-lift: #161f31; --bg-input: #0d1424;
  --border: #1f2d45; --border-lit: #2e3f5e; --text: #f1f5f9; --muted: #64748b;
  --g1: linear-gradient(135deg, #6366f1, #8b5cf6);
  --g2: linear-gradient(135deg, #ec4899, #f43f5e);
  --g3: linear-gradient(135deg, #14b8a6, #06b6d4);
}
html { scroll-behavior: smooth; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding-top: 80px; }
body::before {
  content: ''; position: fixed; inset: 0; z-index: -1;
  background: radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%),
              radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.05) 0%, transparent 70%);
}
.wrap { max-width: 1200px; margin: 0 auto; padding: 2.5rem 2rem; }
.hero {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2rem 2.5rem; margin-bottom: 2rem;
  position: relative; overflow: hidden;
}
.hero::after {
  content: ''; position: absolute; top: -60px; right: -60px; width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(20,184,166,.1) 0%, transparent 70%); pointer-events: none;
}
.hero-label {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
  color: var(--accent); background: rgba(20,184,166,.1);
  border: 1px solid rgba(20,184,166,.2); padding: .25rem .8rem;
  border-radius: 999px; margin-bottom: 1rem;
}
.hero-label .dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
.hero h1 { font-family: 'Sora', sans-serif; font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700; margin-bottom: .6rem; }
.hero h1 em { font-style: normal; background: var(--g3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero p { color: var(--muted); font-size: .95rem; }

.card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.25rem; padding: 2rem; margin-bottom: 2rem;
  position: relative; overflow: hidden; transition: border-color .3s;
}
.card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--g3); transform: scaleX(0); transform-origin: left; transition: transform .4s;
}
.card:hover { border-color: var(--border-lit); }
.card:hover::before { transform: scaleX(1); }

.card-title {
  font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 600;
  margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
}

.form-group { margin-bottom: 1.1rem; }
.form-group label {
  display: block; margin-bottom: .45rem;
  font-size: .85rem; font-weight: 500; color: var(--text);
}
textarea {
  width: 100%; background: var(--bg-input); border: 1px solid var(--border);
  border-radius: .75rem; padding: .7rem 1rem; font-size: .88rem;
  font-family: 'Inter', sans-serif; color: var(--text);
  transition: all .3s; resize: vertical; min-height: 180px;
}
textarea:focus {
  outline: none; border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(20,184,166,.12);
}

.btn {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .8rem 1.6rem; font-weight: 600; font-size: .88rem;
  border: none; border-radius: .85rem; cursor: pointer;
  transition: transform .25s, box-shadow .25s;
}
.btn-primary { background: var(--g3); color: #fff; box-shadow: 0 4px 16px rgba(20,184,166,.3); width: 100%; justify-content: center; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(20,184,166,.5); }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn svg { width: 16px; height: 16px; }

.results-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;
}
.result-section {
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 1rem; padding: 1.75rem;
}
.result-header {
  font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 600;
  margin-bottom: 1.25rem; display: flex; align-items: center; gap: .6rem;
}
.icon-check { color: var(--success); }
.icon-cross { color: #ef4444; }

.skill-list {
  display: flex; flex-wrap: wrap; gap: .65rem;
}
.skill-tag {
  padding: .5rem 1rem; border-radius: .6rem; font-size: .82rem; font-weight: 500;
  background: rgba(99,102,241,.12); color: #a5b4fc;
  border: 1px solid rgba(99,102,241,.2);
}
.skill-tag.has { background: rgba(16,185,129,.12); color: #6ee7b7; border-color: rgba(16,185,129,.2); }
.skill-tag.missing { background: rgba(239,68,68,.12); color: #fca5a5; border-color: rgba(239,68,68,.2); }

.empty-state {
  text-align: center; padding: 3rem 1.5rem; color: var(--muted);
}

footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 2rem; text-align: center; }
footer p { color: var(--muted); font-size: .85rem; }

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.hero { animation: fadeUp .5s ease both; }
.card { animation: fadeUp .5s .1s ease both; }

@media (max-width: 900px) {
  .results-grid { grid-template-columns: 1fr; }
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
    <div class="hero-label"><span class="dot"></span> Skill Gap Analysis</div>
    <h1>Identify Your <em>Skill Gaps</em></h1>
    <p>Compare your skills with job requirements. Know exactly what to learn next.</p>
  </div>

  <div class="card">
    <div class="card-title">Analyze Job Requirements</div>
    
    <form id="analysisForm">
      <div class="form-group">
        <label>Job Description *</label>
        <textarea name="job_description" placeholder="Paste the full job description here..." required></textarea>
      </div>

      <button type="submit" class="btn btn-primary" id="analyzeBtn">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Analyze Skills
      </button>
    </form>
  </div>

  <div id="results" style="display:none;">
    <div class="results-grid">
      <div class="result-section">
        <div class="result-header">
          <svg class="icon-check" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Skills You Have
        </div>
        <div class="skill-list" id="matchedSkills">
          <div class="empty-state">Analyzing...</div>
        </div>
      </div>

      <div class="result-section">
        <div class="result-header">
          <svg class="icon-cross" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
          Skills to Learn
        </div>
        <div class="skill-list" id="missingSkills">
          <div class="empty-state">Analyzing...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer><p>&copy; 2025 Skillsync AI. All Rights Reserved.</p></footer>

<script>
const userSkills = <?= json_encode($user_skills) ?>;
const form = document.getElementById('analysisForm');
const analyzeBtn = document.getElementById('analyzeBtn');
const results = document.getElementById('results');
const matchedSkills = document.getElementById('matchedSkills');
const missingSkills = document.getElementById('missingSkills');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(form);
  const btnText = analyzeBtn.innerHTML;
  
  analyzeBtn.disabled = true;
  analyzeBtn.innerHTML = '<svg style="animation:spin 1s linear infinite" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Analyzing...';
  results.style.display = 'block';
  matchedSkills.innerHTML = '<div class="empty-state">Analyzing...</div>';
  missingSkills.innerHTML = '<div class="empty-state">Analyzing...</div>';
  
  try {
    const response = await fetch('../../app/controllers/skill_gap_backend.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success && data.required_skills) {
      const required = data.required_skills;
      const matched = required.filter(skill => 
        userSkills.some(us => us.toLowerCase() === skill.toLowerCase())
      );
      const missing = required.filter(skill => 
        !userSkills.some(us => us.toLowerCase() === skill.toLowerCase())
      );
      
      matchedSkills.innerHTML = matched.length 
        ? matched.map(s => `<span class="skill-tag has">${s}</span>`).join('')
        : '<div class="empty-state">No matching skills found</div>';
      
      missingSkills.innerHTML = missing.length
        ? missing.map(s => `<span class="skill-tag missing">${s}</span>`).join('')
        : '<div class="empty-state">You have all required skills! 🎉</div>';
    } else {
      matchedSkills.innerHTML = '<div class="empty-state" style="color:#ef4444">Analysis failed</div>';
      missingSkills.innerHTML = '<div class="empty-state" style="color:#ef4444">' + (data.error || 'Error') + '</div>';
    }
  } catch (error) {
    matchedSkills.innerHTML = '<div class="empty-state" style="color:#ef4444">Network error</div>';
    missingSkills.innerHTML = '<div class="empty-state" style="color:#ef4444">Please try again</div>';
  } finally {
    analyzeBtn.disabled = false;
    analyzeBtn.innerHTML = btnText;
  }
});
</script>

</body>
</html>