<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user     = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user['full_name'] ?? explode('@', $user['email'])[0] ?? 'User';

    // ── Skills — correct column is skill_name ─────────────────────────────
    $stmt   = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ? LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Jobs — all jobs, newest first ────────────────────────────────────
    $stmt = $pdo->query("SELECT title, company, location FROM jobs ORDER BY posted_on DESC LIMIT 5");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $skill_count = count($skills);
    $job_count   = count($jobs);

    // ── Profile completeness ──────────────────────────────────────────────
    $stmt = $pdo->prepare("SELECT full_name, bio, location, headline, phone, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    $profile_pct = 0;
    if ($profile) {
        $fields = ['full_name', 'bio', 'location', 'headline', 'phone', 'profile_picture'];
        $filled = array_sum(array_map(fn($f) => !empty($profile[$f]) ? 1 : 0, $fields));
        $profile_pct = round(($filled / count($fields)) * 100);
    }

} catch (PDOException $e) {
    $skills = $jobs = [];
    $skill_count = $job_count = 0;
    $profile_pct = 0;
    $db_error = true;
}

$hour  = (int)date("H");
$greet = $hour < 12 ? "Good Morning" : ($hour < 18 ? "Good Afternoon" : "Good Evening");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Skillsync AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
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
      --g4: linear-gradient(135deg, #f59e0b, #f97316);
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding-top: 80px; }
    body::before {
      content: ''; position: fixed; inset: 0; z-index: -1;
      background:
        radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.08) 0%, transparent 70%),
        radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.06) 0%, transparent 70%);
    }

    .wrap { max-width: 1200px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

    .db-banner {
      display: flex; align-items: center; gap: .75rem;
      background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25);
      color: #fca5a5; border-radius: .75rem;
      padding: .85rem 1.25rem; font-size: .9rem; margin-bottom: 2rem;
    }

    .hero { margin-bottom: 2rem; }
    .hero-inner {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 1.75rem; padding: 2.75rem 3rem;
      position: relative; overflow: hidden;
    }
    .hero-inner::after {
      content: ''; position: absolute; top: -80px; right: -80px;
      width: 320px; height: 320px;
      background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-label {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: .75rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
      color: var(--primary); background: rgba(99,102,241,.1);
      border: 1px solid rgba(99,102,241,.2); padding: .28rem .85rem;
      border-radius: 999px; margin-bottom: 1.2rem;
    }
    .hero-label .dot { width: 6px; height: 6px; background: var(--primary); border-radius: 50%; animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
    .hero h1 { font-family: 'Sora', sans-serif; font-size: clamp(1.6rem, 3.5vw, 2.4rem); font-weight: 700; letter-spacing: -.025em; line-height: 1.25; margin-bottom: .6rem; }
    .hero h1 em { font-style: normal; background: var(--g1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .hero p { color: var(--muted); font-size: 1rem; max-width: 520px; }

    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
    .stat {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 1.25rem; padding: 1.4rem 1.5rem;
      display: flex; flex-direction: column; gap: .25rem;
      transition: border-color .3s, transform .3s;
    }
    .stat:hover { border-color: var(--border-lit); transform: translateY(-3px); }
    .stat-icon { width: 36px; height: 36px; border-radius: .65rem; display: flex; align-items: center; justify-content: center; margin-bottom: .6rem; }
    .bg-purple { background: rgba(99,102,241,.15); }
    .bg-teal   { background: rgba(20,184,166,.15); }
    .bg-pink   { background: rgba(236,72,153,.15); }
    .bg-amber  { background: rgba(245,158,11,.15); }
    .stat-val  { font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 700; line-height: 1; }
    .c-purple  { color: #818cf8; }
    .c-teal    { color: #2dd4bf; }
    .c-pink    { color: #f472b6; }
    .c-amber   { color: #fbbf24; }
    .stat-label { font-size: .82rem; color: var(--muted); font-weight: 500; }
    .prog-wrap  { width: 100%; height: 4px; background: var(--border); border-radius: 999px; overflow: hidden; margin-top: .6rem; }
    .prog-fill  { height: 100%; background: var(--g4); border-radius: 999px; }

    .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

    .card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 1.5rem; padding: 2rem;
      transition: border-color .35s, transform .35s, box-shadow .35s;
      position: relative; overflow: hidden;
    }
    .card:hover { border-color: var(--border-lit); transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,.3); }
    .card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; transform: scaleX(0); transform-origin: left; transition: transform .4s ease; }
    .card:hover::before { transform: scaleX(1); }
    .c1::before { background: var(--g1); }
    .c2::before { background: var(--g2); }
    .c3::before { background: var(--g3); }

    .card-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
    .card-head-left { display: flex; flex-direction: column; gap: .3rem; }
    .card-title { font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 600; color: var(--text); }
    .card-sub   { font-size: .82rem; color: var(--muted); }
    .card-badge { font-size: .7rem; font-weight: 600; padding: .28rem .7rem; border-radius: 999px; white-space: nowrap; }
    .badge-ai   { background: rgba(99,102,241,.15); color: #818cf8; border: 1px solid rgba(99,102,241,.2); }
    .badge-jobs { background: rgba(236,72,153,.15); color: #f472b6; border: 1px solid rgba(236,72,153,.2); }

    .item-list { list-style: none; display: flex; flex-direction: column; gap: .45rem; }
    .item-list li {
      display: flex; align-items: center; gap: .75rem;
      padding: .65rem .9rem; background: var(--bg); border: 1px solid var(--border);
      border-radius: .7rem; font-size: .88rem;
      transition: border-color .25s, transform .25s, background .25s;
    }
    .item-list li:hover { border-color: var(--border-lit); background: var(--bg-lift); transform: translateX(4px); }
    .item-dot   { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .dot-purple { background: #818cf8; }
    .dot-pink   { background: #f472b6; }
    .job-title  { flex: 1; font-weight: 500; }
    .job-meta   { font-size: .77rem; color: var(--muted); white-space: nowrap; }
    .empty-note { text-align: center; padding: 1.75rem 1rem; color: var(--muted); font-size: .86rem; font-style: italic; }

    .card.wide  { grid-column: 1 / -1; }
    .resume-inner { display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap; }
    .resume-text .card-title { font-size: 1.3rem; margin-bottom: .4rem; }
    .resume-text p { color: var(--muted); max-width: 500px; font-size: .95rem; line-height: 1.65; }

    .btn {
      display: inline-flex; align-items: center; gap: .5rem;
      padding: .85rem 1.75rem;
      font-family: 'Inter', sans-serif; font-weight: 600; font-size: .93rem;
      border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
      transition: transform .25s, box-shadow .25s, filter .25s;
    }
    .btn-primary { background: var(--g1); color: #fff !important; box-shadow: 0 4px 20px rgba(99,102,241,.35); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99,102,241,.55); filter: brightness(1.08); }

    .resume-blobs { display: flex; gap: .75rem; flex-shrink: 0; }
    .blob  { width: 54px; height: 54px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; }
    .blob1 { background: rgba(99,102,241,.15); }
    .blob2 { background: rgba(20,184,166,.12); }

    footer { border-top: 1px solid var(--border); padding: 2rem; }
    .footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .footer-brand { font-family: 'Sora', sans-serif; font-weight: 700; font-size: .95rem; background: var(--g1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .footer-copy  { font-size: .8rem; color: var(--muted); }

    @keyframes fadeUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
    .hero-inner { animation: fadeUp .5s ease both; }
    .stats-row  { animation: fadeUp .5s .1s ease both; }
    .main-grid  { animation: fadeUp .5s .2s ease both; }

    @media (max-width: 900px) {
      .stats-row { grid-template-columns: repeat(2, 1fr); }
      .main-grid { grid-template-columns: 1fr; }
      .card.wide { grid-column: auto; }
    }
    @media (max-width: 580px) {
      .wrap { padding: 1.5rem 1rem 3rem; }
      .hero-inner { padding: 2rem 1.5rem; }
      .resume-inner { flex-direction: column; align-items: flex-start; }
      .resume-blobs { display: none; }
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

  <div class="wrap">

    <?php if (isset($db_error)): ?>
    <div class="db-banner">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
        <circle cx="12" cy="16" r=".5" fill="currentColor"/>
      </svg>
      Database unavailable — some sections may show placeholder data.
    </div>
    <?php endif; ?>

    <section class="hero">
      <div class="hero-inner">
        <div class="hero-label"><span class="dot"></span> Dashboard</div>
        <h1><?= htmlspecialchars($greet) ?>, <em><?= htmlspecialchars($username) ?></em> &#x1F44B;</h1>
        <p>Here's your career snapshot. Pick up where you left off or explore something new.</p>
      </div>
    </section>

    <div class="stats-row">
      <div class="stat">
        <div class="stat-icon bg-purple">
          <svg width="17" height="17" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </div>
        <div class="stat-val c-purple"><?= $profile_pct ?>%</div>
        <div class="stat-label">Profile Complete</div>
        <div class="prog-wrap"><div class="prog-fill" style="width:<?= $profile_pct ?>%"></div></div>
      </div>
      <div class="stat">
        <div class="stat-icon bg-teal">
          <svg width="17" height="17" fill="none" stroke="#2dd4bf" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
        </div>
        <div class="stat-val c-teal"><?= $skill_count ?></div>
        <div class="stat-label">Skills Added</div>
      </div>
      <div class="stat">
        <div class="stat-icon bg-pink">
          <svg width="17" height="17" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          </svg>
        </div>
        <div class="stat-val c-pink"><?= $job_count ?></div>
        <div class="stat-label">Job Matches</div>
      </div>
      <div class="stat">
        <div class="stat-icon bg-amber">
          <svg width="17" height="17" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
        </div>
        <div class="stat-val c-amber">0</div>
        <div class="stat-label">Resumes Built</div>
      </div>
    </div>

    <div class="main-grid">

      <div class="card c1">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-title">AI Skill Suggestions</div>
            <div class="card-sub">Based on your profile &amp; trends</div>
          </div>
          <span class="card-badge badge-ai">AI-Powered</span>
        </div>
        <?php if (!empty($skills)): ?>
          <ul class="item-list">
            <?php foreach ($skills as $s): ?>
              <li><span class="item-dot dot-purple"></span><?= htmlspecialchars($s['skill_name'] ?? '') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-note">Complete your profile to unlock personalised skill suggestions.</div>
        <?php endif; ?>
      </div>

      <div class="card c2">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-title">Recommended Jobs</div>
            <div class="card-sub">Matched to your skills &amp; resume</div>
          </div>
          <span class="card-badge badge-jobs">Live</span>
        </div>
        <?php if (!empty($jobs)): ?>
          <ul class="item-list">
            <?php foreach ($jobs as $j): ?>
              <li>
                <span class="item-dot dot-pink"></span>
                <span class="job-title"><?= htmlspecialchars($j['title']) ?> &middot; <?= htmlspecialchars($j['company']) ?></span>
                <span class="job-meta"><?= htmlspecialchars($j['location']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-note">Update your profile to start seeing job matches.</div>
        <?php endif; ?>
      </div>

      <div class="card c3 wide">
        <div class="resume-inner">
          <div class="resume-text">
            <div class="card-title">Resume Builder</div>
            <p>Create a polished, ATS-friendly resume in minutes. Choose from modern templates and let AI fill in the gaps.</p>
            <a href="resume-builder.php" class="btn btn-primary" style="margin-top:1.4rem">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              Build My Resume
            </a>
          </div>
          <div class="resume-blobs">
            <div class="blob blob1">
              <svg width="26" height="26" fill="none" stroke="#818cf8" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
            </div>
            <div class="blob blob2">
              <svg width="26" height="26" fill="none" stroke="#2dd4bf" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <circle cx="12" cy="17" r=".5" fill="#2dd4bf"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <div class="footer-inner">
      <div class="footer-brand">Skillsync AI</div>
      <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
    </div>
  </footer>

</body>
</html>