<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

// Get applications
try {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY date_applied DESC");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $stats = [
        'total' => count($applications),
        'applied' => count(array_filter($applications, fn($a) => $a['status'] === 'Applied')),
        'interview' => count(array_filter($applications, fn($a) => $a['status'] === 'Interview')),
        'offer' => count(array_filter($applications, fn($a) => $a['status'] === 'Offer')),
        'rejected' => count(array_filter($applications, fn($a) => $a['status'] === 'Rejected'))
    ];
} catch (PDOException $e) {
    $applications = [];
    $stats = ['total' => 0, 'applied' => 0, 'interview' => 0, 'offer' => 0, 'rejected' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications Tracker – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --primary: #6366f1; --secondary: #ec4899; --accent: #14b8a6; --success: #10b981;
  --bg: #080e1a; --bg-card: #111827; --bg-lift: #161f31;
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
.wrap { max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem; }
.hero {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.5rem; padding: 2rem 2.5rem; margin-bottom: 2rem;
  position: relative; overflow: hidden;
}
.hero::after {
  content: ''; position: absolute; top: -60px; right: -60px; width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%); pointer-events: none;
}
.hero-label {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
  color: var(--primary); background: rgba(99,102,241,.1);
  border: 1px solid rgba(99,102,241,.2); padding: .25rem .8rem;
  border-radius: 999px; margin-bottom: 1rem;
}
.hero-label .dot { width: 6px; height: 6px; background: var(--primary); border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
.hero h1 { font-family: 'Sora', sans-serif; font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700; margin-bottom: .6rem; }
.hero h1 em { font-style: normal; background: var(--g1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero p { color: var(--muted); font-size: .95rem; }

.stats-row {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;
}
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem;
  position: relative; overflow: hidden; transition: transform .3s, border-color .3s;
}
.stat-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  transform: scaleX(0); transform-origin: left; transition: transform .4s;
}
.stat-card:hover { transform: translateY(-3px); border-color: var(--border-lit); }
.stat-card:hover::before { transform: scaleX(1); }
.stat-card:nth-child(1)::before { background: var(--g1); }
.stat-card:nth-child(2)::before { background: var(--g2); }
.stat-card:nth-child(3)::before { background: var(--g3); }
.stat-card:nth-child(4)::before { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card:nth-child(5)::before { background: linear-gradient(135deg, #ef4444, #dc2626); }
.stat-label { font-size: .8rem; color: var(--muted); margin-bottom: .3rem; font-weight: 500; }
.stat-value { font-family: 'Sora', sans-serif; font-size: 2rem; font-weight: 700; }

.actions-bar {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
}
.btn {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .75rem 1.4rem; font-weight: 600; font-size: .88rem;
  border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
  transition: transform .25s, box-shadow .25s;
}
.btn-primary { background: var(--g1); color: #fff; box-shadow: 0 4px 16px rgba(99,102,241,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(99,102,241,.5); }
.btn svg { width: 16px; height: 16px; }

.apps-grid {
  display: grid; gap: 1.25rem;
}
.app-card {
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.75rem;
  position: relative; overflow: hidden; transition: transform .3s, border-color .3s;
}
.app-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--g2); transform: scaleX(0); transform-origin: left; transition: transform .4s;
}
.app-card:hover { transform: translateY(-3px); border-color: var(--border-lit); }
.app-card:hover::before { transform: scaleX(1); }
.app-header {
  display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;
}
.app-title { font-family: 'Sora', sans-serif; font-size: 1.2rem; font-weight: 600; margin-bottom: .3rem; }
.app-company { color: var(--muted); font-size: .88rem; }
.status-badge {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .4rem .9rem; border-radius: .6rem; font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
}
.status-applied { background: rgba(99,102,241,.15); color: #a5b4fc; }
.status-interview { background: rgba(236,72,153,.15); color: #f9a8d4; }
.status-offer { background: rgba(16,185,129,.15); color: #6ee7b7; }
.status-rejected { background: rgba(239,68,68,.15); color: #fca5a5; }
.app-meta {
  display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);
  font-size: .85rem; color: var(--muted);
}
.app-meta span { display: flex; align-items: center; gap: .4rem; }
.app-notes { margin-top: 1rem; color: var(--muted); font-size: .88rem; line-height: 1.6; }

.empty-state {
  text-align: center; padding: 4rem 2rem;
  background: var(--bg-card); border: 1px dashed var(--border); border-radius: 1.5rem;
}
.empty-icon {
  width: 80px; height: 80px; margin: 0 auto 1.5rem;
  background: rgba(99,102,241,.1); border-radius: 1.25rem;
  display: flex; align-items: center; justify-content: center;
}
.empty-state h3 { font-family: 'Sora', sans-serif; font-size: 1.4rem; margin-bottom: .6rem; }
.empty-state p { color: var(--muted); margin-bottom: 2rem; }

footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 2rem; text-align: center; }
footer p { color: var(--muted); font-size: .85rem; }

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.hero { animation: fadeUp .5s ease both; }
.stats-row { animation: fadeUp .5s .1s ease both; }
.apps-grid { animation: fadeUp .5s .2s ease both; }

@media (max-width: 768px) {
  .wrap { padding: 1.5rem 1rem; }
  .hero { padding: 1.5rem; }
  .stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<div class="wrap">
  <div class="hero">
    <div class="hero-label"><span class="dot"></span> Applications Tracker</div>
    <h1>Track Your <em>Job Applications</em></h1>
    <p>Keep all your applications organized in one place. Never lose track of where you applied.</p>
  </div>

  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Applications</div>
      <div class="stat-value"><?= $stats['total'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Applied</div>
      <div class="stat-value"><?= $stats['applied'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Interviews</div>
      <div class="stat-value"><?= $stats['interview'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Offers</div>
      <div class="stat-value"><?= $stats['offer'] ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Rejected</div>
      <div class="stat-value"><?= $stats['rejected'] ?></div>
    </div>
  </div>

  <div class="actions-bar">
    <h2 style="font-family: 'Sora', sans-serif; font-size: 1.3rem; font-weight: 600;">Your Applications</h2>
    <a href="../../app/controllers/application_form.php" class="btn btn-primary">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M12 5v14M5 12h14"/>
      </svg>
      Add Application
    </a>
  </div>

  <?php if (empty($applications)): ?>
  <div class="empty-state">
    <div class="empty-icon">
      <svg width="40" height="40" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24">
        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
      </svg>
    </div>
    <h3>No Applications Yet</h3>
    <p>Start tracking your job applications to stay organized and boost your success rate.</p>
    <a href="../../app/controllers/application_form.php" class="btn btn-primary">Add Your First Application</a>
  </div>
  <?php else: ?>
  <div class="apps-grid">
    <?php foreach ($applications as $app): ?>
    <div class="app-card">
      <div class="app-header">
        <div>
          <div class="app-title"><?= htmlspecialchars($app['job_title']) ?></div>
          <div class="app-company"><?= htmlspecialchars($app['company']) ?></div>
        </div>
        <span class="status-badge status-<?= strtolower($app['status']) ?>">
          <?= htmlspecialchars($app['status']) ?>
        </span>
      </div>
      <?php if (!empty($app['notes'])): ?>
      <div class="app-notes"><?= nl2br(htmlspecialchars($app['notes'])) ?></div>
      <?php endif; ?>
      <div class="app-meta">
        <span>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Applied: <?= date('M d, Y', strtotime($app['date_applied'])) ?>
        </span>
        <?php if ($app['salary']): ?>
        <span>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
          <?= htmlspecialchars($app['salary']) ?>
        </span>
        <?php endif; ?>
        <?php if ($app['location']): ?>
        <span>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
          <?= htmlspecialchars($app['location']) ?>
        </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<footer><p>&copy; 2025 Skillsync AI. All Rights Reserved.</p></footer>

</body>
</html>