<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require __DIR__ . '/app/config/database.php';

$login_message = '';
$signup_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_submit'])) {
        $email = strtolower(trim($_POST['login_email']));
        $password = trim($_POST['login_password']);
        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT id, password, profile_complete FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: ' . ($user['profile_complete'] == 0 ? 'app/views/form.php' : 'app/views/dashboard.php'));
                exit;
            } else {
                $login_message = "Incorrect email or password";
            }
        } else {
            $login_message = "Email and password required";
        }
    }
    if (isset($_POST['signup_submit'])) {
        $email = strtolower(trim($_POST['signup_email']));
        $password = trim($_POST['signup_password']);
        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $signup_message = "Email already registered";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                if ($stmt->execute([$email, $password])) {
                    $signup_message = "Signup successful! You can now login.";
                } else {
                    $signup_message = "Signup failed";
                }
            }
        } else {
            $signup_message = "Email and password required";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Skillsync AI – Build Your Perfect Resume with AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="public/images/favicon.svg">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --primary:    #6366f1;
  --secondary:  #ec4899;
  --accent:     #14b8a6;
  --bg:         #080e1a;
  --bg-card:    #111827;
  --bg-lift:    #161f31;
  --border:     #1f2d45;
  --border-lit: #2e3f5e;
  --text:       #f1f5f9;
  --muted:      #64748b;
  --g1: linear-gradient(135deg, #6366f1, #8b5cf6);
  --g2: linear-gradient(135deg, #ec4899, #f43f5e);
  --g3: linear-gradient(135deg, #14b8a6, #06b6d4);
  --g4: linear-gradient(135deg, #fbbf24, #f97316);
}

html { scroll-behavior: smooth; }

body {
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: var(--text);
  line-height: 1.6;
  overflow-x: hidden;
}

body::before {
  content: ''; position: fixed; inset: 0; z-index: -1;
  background:
    radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%),
    radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.05) 0%, transparent 70%);
}

/* ── Navbar ── */
.navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  background: rgba(17, 24, 39, 0.85); backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  height: 80px; transition: background .3s;
}
.navbar.scrolled { background: rgba(17, 24, 39, 0.95); }

.nav-container {
  max-width: 1400px; margin: 0 auto; height: 100%;
  padding: 0 2rem;
  display: flex; align-items: center; justify-content: space-between;
}

.logo {
  font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.3rem;
  background: var(--g1);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; cursor: pointer;
  transition: transform .3s;
}
.logo:hover { transform: translateY(-2px); }

.nav-links {
  display: flex; align-items: center; gap: .5rem; list-style: none;
}
.nav-links a:not(.btn) {
  padding: .65rem 1.2rem; color: var(--muted);
  font-size: .88rem; font-weight: 500;
  text-decoration: none; border-radius: .75rem;
  transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
}
.nav-links a:not(.btn):hover { color: var(--text); background: var(--bg-lift); }

.btn {
  padding: .65rem 1.2rem; border-radius: .75rem;
  font-weight: 600; font-size: .88rem;
  cursor: pointer; transition: all .3s;
  border: none; text-decoration: none;
  display: inline-flex; align-items: center; gap: .4rem;
}
.btn-primary {
  background: var(--g1); 
  color: #fff !important;
  box-shadow: 0 3px 12px rgba(99,102,241,.35);
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99,102,241,.5);
  filter: brightness(1.08);
  color: #fff !important;
}
.btn-outline {
  background: transparent; color: var(--primary);
  border: 1px solid var(--primary);
}
.btn-outline:hover {
  background: var(--primary); color: #fff;
  border-color: var(--primary);
  transform: translateY(-2px);
}

/* ── Hero ── */
.hero {
  min-height: 100vh; display: flex; align-items: center;
  padding: 8rem 2rem 4rem;
  position: relative;
}
.hero::after {
  content: ''; position: absolute; top: 10%; right: 10%;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 70%);
  pointer-events: none; animation: float 8s ease-in-out infinite;
}
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-30px); }
}

.hero-content {
  max-width: 1200px; margin: 0 auto;
  text-align: center; z-index: 1;
  animation: fadeUp .6s ease both;
}

.hero-label {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
  color: var(--primary); background: rgba(99,102,241,.1);
  border: 1px solid rgba(99,102,241,.2); padding: .25rem .8rem;
  border-radius: 999px; margin-bottom: 1.5rem;
}
.hero-label .dot {
  width: 6px; height: 6px; background: var(--primary);
  border-radius: 50%; animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .5; transform: scale(.8); }
}

.hero-title {
  font-family: 'Sora', sans-serif;
  font-size: clamp(2.5rem, 6vw, 4.5rem);
  font-weight: 800; line-height: 1.1;
  letter-spacing: -.03em; margin-bottom: 1.5rem;
}
.hero-title em {
  font-style: normal; background: var(--g1);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: clamp(1rem, 2vw, 1.25rem);
  color: var(--muted); margin-bottom: 3rem;
  max-width: 700px; margin-left: auto; margin-right: auto;
}

.hero-buttons {
  display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
  animation: fadeUp .6s .2s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(18px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Sections ── */
section {
  padding: 5rem 2rem; position: relative;
}
.container { max-width: 1200px; margin: 0 auto; }

.section-header {
  text-align: center; margin-bottom: 4rem;
  animation: fadeUp .5s ease both;
}
.section-title {
  font-family: 'Sora', sans-serif;
  font-size: clamp(2rem, 4vw, 2.8rem);
  font-weight: 700; margin-bottom: 1rem;
  letter-spacing: -.02em;
}
.section-subtitle {
  font-size: 1.1rem; color: var(--muted);
  max-width: 600px; margin: 0 auto;
}

/* ── Feature Cards (with accent strips) ── */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2rem;
  position: relative; overflow: hidden;
  transition: all .4s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fadeUp .5s ease both;
}
.feature-card:nth-child(1) { animation-delay: .1s; }
.feature-card:nth-child(2) { animation-delay: .2s; }
.feature-card:nth-child(3) { animation-delay: .3s; }
.feature-card:nth-child(4) { animation-delay: .4s; }

/* Top accent strip */
.feature-card::before {
  content: ''; position: absolute;
  top: 0; left: 0; right: 0; height: 3px;
  background: var(--g1);
  transform: scaleX(0); transform-origin: left;
  transition: transform .4s ease;
}
.feature-card:nth-child(2)::before { background: var(--g2); }
.feature-card:nth-child(3)::before { background: var(--g3); }
.feature-card:nth-child(4)::before { background: var(--g4); }

.feature-card:hover {
  transform: translateY(-5px);
  border-color: var(--border-lit);
}
.feature-card:hover::before { transform: scaleX(1); }

.feature-icon {
  width: 64px; height: 64px;
  background: var(--g1);
  border-radius: 1rem;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem;
  transition: transform .3s;
}
.feature-card:hover .feature-icon { transform: scale(1.05) rotate(3deg); }
.feature-card:nth-child(2) .feature-icon { background: var(--g2); }
.feature-card:nth-child(3) .feature-icon { background: var(--g3); }
.feature-card:nth-child(4) .feature-icon { background: var(--g4); }

.feature-icon svg { width: 32px; height: 32px; fill: #fff; }

.feature-card h3 {
  font-family: 'Sora', sans-serif; font-size: 1.4rem;
  font-weight: 600; margin-bottom: 1rem;
}
.feature-card p { color: var(--muted); line-height: 1.7; margin-bottom: 1rem; }
.feature-card a {
  color: var(--primary); text-decoration: none; font-weight: 600;
  display: inline-flex; align-items: center; gap: .3rem;
  transition: gap .3s;
}
.feature-card a:hover { gap: .6rem; }

/* ── About Section ── */
.about-section {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}

.about-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem; margin-top: 3rem;
}

.about-card {
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2.5rem;
  position: relative; overflow: hidden;
  transition: all .3s;
  animation: fadeUp .5s ease both;
}
.about-card:nth-child(1) { animation-delay: .1s; }
.about-card:nth-child(2) { animation-delay: .2s; }
.about-card:nth-child(3) { animation-delay: .3s; }

.about-card::before {
  content: ''; position: absolute;
  top: 0; left: 0; right: 0; height: 3px;
  background: var(--g1);
  transform: scaleX(0); transform-origin: left;
  transition: transform .4s ease;
}
.about-card:nth-child(2)::before { background: var(--g2); }
.about-card:nth-child(3)::before { background: var(--g3); }

.about-card:hover {
  transform: translateY(-5px);
  border-color: var(--border-lit);
}
.about-card:hover::before { transform: scaleX(1); }

.about-card h3 {
  font-family: 'Sora', sans-serif; font-size: 1.6rem;
  font-weight: 600; margin-bottom: 1.5rem;
  color: var(--primary);
}
.about-card p { color: var(--muted); line-height: 1.8; }

/* ── How It Works ── */
.how-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 2rem; margin-top: 3rem;
}

.how-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2rem;
  position: relative; text-align: center;
  transition: all .3s; overflow: hidden;
  animation: fadeUp .5s ease both;
}
.how-card:nth-child(1) { animation-delay: .1s; }
.how-card:nth-child(2) { animation-delay: .2s; }
.how-card:nth-child(3) { animation-delay: .3s; }
.how-card:nth-child(4) { animation-delay: .4s; }

.how-card::before {
  content: ''; position: absolute;
  top: 0; left: 0; right: 0; height: 3px;
  background: var(--g1);
  transform: scaleX(0); transform-origin: left;
  transition: transform .4s ease;
}
.how-card:hover::before { transform: scaleX(1); }
.how-card:hover { transform: translateY(-5px); border-color: var(--border-lit); }

.how-number {
  width: 48px; height: 48px;
  background: var(--g1);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.5rem;
  font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.2rem;
}

.how-card h3 {
  font-family: 'Sora', sans-serif; font-size: 1.2rem;
  font-weight: 600; margin-bottom: .8rem;
}
.how-card p { color: var(--muted); line-height: 1.7; font-size: .92rem; }

/* ── Developer Section ── */
.developer-section {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
}

.developer-card {
  max-width: 600px; margin: 3rem auto 0;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 3rem;
  text-align: center; position: relative; overflow: hidden;
  transition: all .4s;
}

.developer-card::before {
  content: ''; position: absolute;
  top: 0; left: 0; right: 0; height: 5px;
  background: var(--g1);
}

.developer-card:hover {
  transform: translateY(-10px);
  border-color: var(--primary);
  box-shadow: 0 20px 60px rgba(99,102,241,.3);
}

.developer-avatar {
  width: 120px; height: 120px;
  background: var(--g1);
  border-radius: 50%;
  margin: 0 auto 2rem;
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; font-weight: 700;
  font-family: 'Sora', sans-serif;
  box-shadow: 0 10px 40px rgba(99,102,241,.3);
}

.developer-card h3 {
  font-family: 'Sora', sans-serif; font-size: 1.8rem;
  font-weight: 700; margin-bottom: .5rem;
}

.developer-role {
  color: var(--primary); font-weight: 600;
  margin-bottom: 1.5rem; font-size: 1.1rem;
}

.developer-card p {
  color: var(--muted); line-height: 1.8;
  margin-bottom: 2rem;
}

/* ── CTA Section ── */
.cta-section {
  text-align: center; padding: 6rem 2rem;
  background: linear-gradient(135deg, rgba(99,102,241,.05) 0%, rgba(236,72,153,.05) 100%);
}
.cta-content { max-width: 700px; margin: 0 auto; }
.cta-title {
  font-family: 'Sora', sans-serif;
  font-size: clamp(2rem, 4vw, 2.8rem);
  font-weight: 700; margin-bottom: 1.5rem;
  letter-spacing: -.02em;
}
.cta-subtitle {
  font-size: 1.1rem; color: var(--muted); margin-bottom: 2.5rem;
}

/* ── Footer ── */
footer {
  background: var(--bg-card); border-top: 1px solid var(--border);
  padding: 2rem; text-align: center;
}
footer p { color: var(--muted); font-size: .88rem; }
footer a {
  color: var(--primary); text-decoration: none;
  transition: color .3s;
}
footer a:hover { color: #818cf8; }

/* ── Modals ── */
.auth-modal {
  position: fixed; inset: 0; z-index: 2000;
  background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  opacity: 1; transition: opacity .3s;
}
.auth-modal.hidden { opacity: 0; pointer-events: none; }

.auth-content {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 3rem;
  max-width: 460px; width: 90%;
  position: relative;
  animation: modalSlide .4s ease;
}
@keyframes modalSlide {
  from { opacity: 0; transform: scale(.9) translateY(-20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.close {
  position: absolute; top: 1.5rem; right: 1.5rem;
  font-size: 2rem; color: var(--muted); cursor: pointer;
  transition: all .3s; line-height: 1;
}
.close:hover { color: var(--text); transform: rotate(90deg); }

.auth-form h2 {
  font-family: 'Sora', sans-serif; font-size: 1.8rem;
  font-weight: 700; margin-bottom: 2rem; text-align: center;
}

.input-group { margin-bottom: 1.5rem; }
.input-group label {
  display: block; margin-bottom: .5rem;
  color: var(--muted); font-weight: 500; font-size: .88rem;
}
.input-group input {
  width: 100%; padding: .85rem 1.1rem;
  border: 1px solid var(--border); border-radius: .75rem;
  background: var(--bg); color: var(--text); font-size: .92rem;
  transition: all .3s;
}
.input-group input:focus {
  outline: none; border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

.auth-form button[type="submit"] {
  width: 100%; padding: .95rem; font-size: 1rem;
}
.auth-form > p {
  text-align: center; margin-top: 1.5rem;
  color: var(--muted); font-size: .88rem;
}
.auth-form > p a {
  color: var(--primary); text-decoration: none; font-weight: 600;
  transition: color .3s;
}
.auth-form > p a:hover { color: #818cf8; }

.message {
  padding: .85rem; border-radius: .75rem;
  margin-bottom: 1.5rem; font-weight: 500; text-align: center;
  font-size: .88rem;
}
.message-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}
.message-success {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86efac;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .nav-links { gap: .3rem; }
  .nav-links a { padding: .5rem .8rem; font-size: .82rem; }
  .hero { padding: 6rem 1.5rem 3rem; }
  .hero-buttons { flex-direction: column; align-items: stretch; }
  .hero-buttons .btn { justify-content: center; }
  section { padding: 3rem 1.5rem; }
  .features-grid, .about-grid, .how-grid { grid-template-columns: 1fr; }
  .auth-content { padding: 2.5rem 1.8rem; }
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
  <div class="nav-container">
    <div class="logo">Skillsync AI</div>
    <ul class="nav-links">
      <li><a href="#features">Features</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#how">How It Works</a></li>
      <li><a href="#team">Team</a></li>
      <li><a href="#" class="btn btn-outline" id="open-login">Login</a></li>
      <li><a href="#" class="btn btn-primary" id="open-signup">Get Started</a></li>
    </ul>
  </div>
</nav>

<!-- Hero -->
<section class="hero" id="home">
  <div class="hero-content">
    <div class="hero-label"><span class="dot"></span> AI-Powered Resume Builder</div>
    <h1 class="hero-title">Build Your Perfect Resume with <em>AI Intelligence</em></h1>
    <p class="hero-subtitle">
      Transform your career journey with cutting-edge AI technology. Get personalized skill suggestions, 
      job recommendations, and professional resume templates.
    </p>
    <div class="hero-buttons">
      <a href="#" class="btn btn-primary" id="hero-signup">
        Get Started Free
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
      <a href="#features" class="btn btn-outline">Learn More</a>
    </div>
  </div>
</section>

<!-- Features -->
<section id="features">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Powerful Features for Success</h2>
      <p class="section-subtitle">
        Everything you need to create an outstanding resume and land your dream job
      </p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24"><path d="M12 2L2 7L12 12L22 7L12 2Z"/><path d="M2 17L12 22L22 17"/><path d="M2 12L12 17L22 12"/></svg>
        </div>
        <h3>AI Skill Suggestions</h3>
        <p>Our intelligent engine analyzes industry trends to suggest personalized skills that make your resume stand out.</p>
        <a href="#">Learn More →</a>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3>Job Recommendations</h3>
        <p>Discover curated opportunities that perfectly match your profile and hidden potential.</p>
        <a href="#">View Jobs →</a>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3>Resume Templates</h3>
        <p>Choose from professional templates designed to highlight your strengths and impress recruiters.</p>
        <a href="#">Browse Templates →</a>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="17" r=".5" fill="#fff"/></svg>
        </div>
        <h3>AI Chat Assistant</h3>
        <p>Get instant career advice, resume tips, and personalized guidance from our AI chatbot.</p>
        <a href="#">Try Now →</a>
      </div>
    </div>
  </div>
</section>

<!-- About -->
<section class="about-section" id="about">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">About Skillsync AI</h2>
      <p class="section-subtitle">
        Revolutionizing the way you create your professional identity
      </p>
    </div>
    <div class="about-grid">
      <div class="about-card">
        <h3>Our Mission</h3>
        <p>
          At Skillsync AI, we believe every individual has untapped potential. Our mission is to unlock 
          that potential by combining cutting-edge AI with creative resume design. We bring imagination 
          to reality, helping you get recognized before you even walk into the room.
        </p>
      </div>
      <div class="about-card">
        <h3>Our Vision</h3>
        <p>
          We envision a world where career growth is guided by insight, not luck. Skillsync AI strives 
          to be the bridge between talent and opportunity, making professional success accessible to everyone.
        </p>
      </div>
      <div class="about-card">
        <h3>Our Technology</h3>
        <p>
          Powered by advanced AI algorithms and machine learning, we analyze industry trends, job markets, 
          and professional profiles to give you the competitive edge you deserve in your career journey.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- How It Works -->
<section id="how">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">How It Works</h2>
      <p class="section-subtitle">Get started in minutes with our simple process</p>
    </div>
    <div class="how-grid">
      <div class="how-card">
        <div class="how-number">1</div>
        <h3>Create Account</h3>
        <p>Sign up for free and complete your profile with your career information.</p>
      </div>
      <div class="how-card">
        <div class="how-number">2</div>
        <h3>Get AI Suggestions</h3>
        <p>Our AI analyzes your profile and provides personalized recommendations.</p>
      </div>
      <div class="how-card">
        <div class="how-number">3</div>
        <h3>Build Your Resume</h3>
        <p>Choose a template and let AI help you create a standout resume.</p>
      </div>
      <div class="how-card">
        <div class="how-number">4</div>
        <h3>Land Your Job</h3>
        <p>Get matched with opportunities and take your career to the next level.</p>
      </div>
    </div>
  </div>
</section>

<!-- Developer -->
<section class="developer-section" id="team">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Meet the Developer</h2>
      <p class="section-subtitle">
        The mind behind Skillsync AI – crafting innovative features and seamless experiences
      </p>
    </div>
    <div class="developer-card">
      <div class="developer-avatar">LJN</div>
      <h3>Lawrance Johnwilson Nadar</h3>
      <p class="developer-role">Full Stack Developer & AI Specialist</p>
      <p>
        Passionate about coding, design, and making AI accessible for everyone. Specializing in 
        AI-powered tools and intuitive user experiences that make a real difference in people's careers.
      </p>
      <a href="mailto:lawrencejohnwilson28624@gmail.com" class="btn btn-primary">Get in Touch</a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="cta-content">
    <h2 class="cta-title">Ready to Transform Your Career?</h2>
    <p class="cta-subtitle">
      Join thousands of professionals who have elevated their careers with Skillsync AI
    </p>
    <div class="hero-buttons">
      <a href="#" class="btn btn-primary" id="cta-signup">
        Start Building Now
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <p>&copy; 2025 Skillsync AI. All rights reserved.</p>
</footer>

<!-- Login Modal -->
<div id="login-modal" class="auth-modal <?php echo $login_message ? '' : 'hidden'; ?>">
  <div class="auth-content">
    <span id="login-close" class="close">&times;</span>
    <form method="POST" class="auth-form">
      <h2>Welcome Back</h2>
      <?php if ($login_message) echo "<div class='message message-error'>$login_message</div>"; ?>
      <div class="input-group">
        <label>Email Address</label>
        <input type="email" name="login_email" required placeholder="you@example.com">
      </div>
      <div class="input-group">
        <label>Password</label>
        <input type="password" name="login_password" required placeholder="••••••••">
      </div>
      <button class="btn btn-primary" type="submit" name="login_submit">Login</button>
      <p>Don't have an account? <a href="#" id="switch-to-signup">Sign up</a></p>
    </form>
  </div>
</div>

<!-- Signup Modal -->
<div id="signup-modal" class="auth-modal <?php echo $signup_message ? '' : 'hidden'; ?>">
  <div class="auth-content">
    <span id="signup-close" class="close">&times;</span>
    <form method="POST" class="auth-form">
      <h2>Create Account</h2>
      <?php if ($signup_message) {
        $cls = strpos($signup_message, 'successful') !== false ? 'message-success' : 'message-error';
        echo "<div class='message $cls'>$signup_message</div>";
      } ?>
      <div class="input-group">
        <label>Email Address</label>
        <input type="email" name="signup_email" required placeholder="you@example.com">
      </div>
      <div class="input-group">
        <label>Password</label>
        <input type="password" name="signup_password" required placeholder="••••••••">
      </div>
      <button class="btn btn-primary" type="submit" name="signup_submit">Sign Up</button>
      <p>Already have an account? <a href="#" id="switch-to-login">Login</a></p>
    </form>
  </div>
</div>

<script>
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 50);
});

document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const href = a.getAttribute('href');
    if (href !== '#' && document.querySelector(href)) {
      e.preventDefault();
      document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
    }
  });
});

const loginModal = document.getElementById('login-modal');
const signupModal = document.getElementById('signup-modal');

function openModal(modal) {
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeModal(modal) {
  modal.classList.add('hidden');
  document.body.style.overflow = 'auto';
}

document.getElementById('open-login')?.addEventListener('click', e => {
  e.preventDefault(); openModal(loginModal);
});
[document.getElementById('open-signup'), document.getElementById('hero-signup'), document.getElementById('cta-signup')].forEach(btn => {
  btn?.addEventListener('click', e => {
    e.preventDefault(); openModal(signupModal);
  });
});

document.getElementById('login-close')?.addEventListener('click', () => closeModal(loginModal));
document.getElementById('signup-close')?.addEventListener('click', () => closeModal(signupModal));

document.getElementById('switch-to-signup')?.addEventListener('click', e => {
  e.preventDefault();
  closeModal(loginModal);
  openModal(signupModal);
});
document.getElementById('switch-to-login')?.addEventListener('click', e => {
  e.preventDefault();
  closeModal(signupModal);
  openModal(loginModal);
});

window.addEventListener('click', e => {
  if (e.target === loginModal) closeModal(loginModal);
  if (e.target === signupModal) closeModal(signupModal);
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeModal(loginModal);
    closeModal(signupModal);
  }
});
</script>
</body>
</html>