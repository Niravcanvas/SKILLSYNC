<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../app/config/database.php';

// Fetch jobs
$jobs = [];
try {
    $stmt = $pdo->query("
        SELECT j.*, u.full_name AS poster
        FROM jobs j
        LEFT JOIN users u ON j.user_id = u.id
        ORDER BY j.posted_on DESC, j.id DESC
    ");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = true;
}

$job_count = count($jobs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobs & Internships – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="images/favicon.svg">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --primary:    #6366f1;
    --secondary:  #ec4899;
    --accent:     #14b8a6;
    --success:    #10b981;
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
  }

  body::before {
    content: ''; position: fixed; inset: 0; z-index: -1;
    background:
      radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%),
      radial-gradient(ellipse 50% 40% at 90% 80%, rgba(236,72,153,.05) 0%, transparent 70%);
  }

  .wrap { max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

  /* ── Hero ── */
  .hero {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.75rem; padding: 2.5rem 3rem;
    margin-bottom: 2rem; position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1.5rem;
  }
  .hero::after {
    content: ''; position: absolute; top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(236,72,153,.08) 0%, transparent 70%);
    pointer-events: none;
  }
  .hero-label {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .75rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
    color: var(--secondary); background: rgba(236,72,153,.1);
    border: 1px solid rgba(236,72,153,.2); padding: .28rem .85rem;
    border-radius: 999px; margin-bottom: 1rem;
  }
  .hero-label .dot { width:6px; height:6px; background:var(--secondary); border-radius:50%; animation: pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

  .hero h1 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(1.5rem, 3vw, 2.2rem);
    font-weight: 700; letter-spacing: -.025em; line-height: 1.25;
    margin-bottom: .5rem;
  }
  .hero h1 em {
    font-style: normal; background: var(--g2);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  }
  .hero p { color: var(--muted); font-size: .95rem; }
  .hero-stat {
    display: flex; align-items: center; gap: .6rem;
    background: var(--bg-lift); border: 1px solid var(--border);
    border-radius: 1rem; padding: .85rem 1.25rem; flex-shrink: 0;
  }
  .hero-stat-val { font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 700; color: #f472b6; }
  .hero-stat-label { font-size: .8rem; color: var(--muted); }

  /* ── Controls bar ── */
  .controls {
    display: flex; align-items: center; gap: 1rem;
    flex-wrap: wrap; margin-bottom: 1.75rem;
  }
  .search-wrap {
    flex: 1; min-width: 220px; position: relative;
  }
  .search-wrap svg {
    position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px; stroke: var(--muted); pointer-events: none;
  }
  .search-wrap input {
    width: 100%; padding: .7rem 1rem .7rem 2.4rem;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: .85rem; color: var(--text); font-size: .88rem;
    font-family: 'Inter', sans-serif;
    transition: border-color .25s, box-shadow .25s;
  }
  .search-wrap input:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
  }
  .search-wrap input::placeholder { color: var(--muted); }

  select.filter {
    padding: .7rem 1rem; background: var(--bg-card); border: 1px solid var(--border);
    border-radius: .85rem; color: var(--text); font-size: .85rem;
    font-family: 'Inter', sans-serif; cursor: pointer;
    transition: border-color .25s;
  }
  select.filter:focus { outline: none; border-color: var(--primary); }
  select.filter option { background: var(--bg-card); }

  .btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .7rem 1.4rem;
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .85rem;
    border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
    transition: transform .25s, box-shadow .25s, filter .25s; white-space: nowrap;
  }
  .btn-primary { background: var(--g1); color: #fff; box-shadow: 0 4px 16px rgba(99,102,241,.35); }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.5); filter: brightness(1.08); }
  .btn-secondary { background: var(--g2); color: #fff; box-shadow: 0 4px 16px rgba(236,72,153,.3); }
  .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(236,72,153,.45); filter: brightness(1.08); }

  /* ── Jobs grid ── */
  .jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 1.25rem;
  }

  /* ── Job card ── */
  .job-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.5rem; padding: 1.75rem;
    display: flex; flex-direction: column; gap: .85rem;
    transition: border-color .35s, transform .35s, box-shadow .35s;
    position: relative; overflow: hidden;
    animation: fadeUp .5s ease both;
  }
  .job-card:hover {
    border-color: var(--border-lit); transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(0,0,0,.3);
  }
  .job-card::before {
    content: ''; position: absolute; top:0; left:0; right:0; height:3px;
    background: var(--g2); transform: scaleX(0); transform-origin: left;
    transition: transform .4s ease;
  }
  .job-card:hover::before { transform: scaleX(1); }

  .job-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
  .job-title { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 600; line-height: 1.3; }
  .job-type-badge {
    font-size: .7rem; font-weight: 600; padding: .25rem .65rem;
    border-radius: 999px; white-space: nowrap; flex-shrink: 0;
  }
  .type-full    { background: rgba(99,102,241,.15); color: #818cf8; border: 1px solid rgba(99,102,241,.2); }
  .type-intern  { background: rgba(20,184,166,.15); color: #2dd4bf; border: 1px solid rgba(20,184,166,.2); }
  .type-part    { background: rgba(245,158,11,.15); color: #fbbf24; border: 1px solid rgba(245,158,11,.2); }

  .job-company { font-size: .88rem; font-weight: 600; color: var(--text); }
  .job-meta-row {
    display: flex; flex-wrap: wrap; gap: .5rem;
  }
  .job-meta {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .78rem; color: var(--muted);
    background: var(--bg); border: 1px solid var(--border);
    padding: .22rem .65rem; border-radius: 999px;
  }
  .job-meta svg { width: 11px; height: 11px; stroke: var(--muted); flex-shrink: 0; }

  .job-desc {
    font-size: .85rem; color: var(--muted); line-height: 1.6;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
  }

  .job-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: .75rem; border-top: 1px solid var(--border);
    margin-top: auto;
  }
  .job-posted { font-size: .75rem; color: var(--muted); }
  .job-vacancies {
    font-size: .75rem; font-weight: 600;
    color: #f472b6; background: rgba(236,72,153,.1);
    border: 1px solid rgba(236,72,153,.2);
    padding: .2rem .6rem; border-radius: 999px;
  }

  .apply-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.1rem;
    background: var(--g1); color: #fff;
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .82rem;
    border: none; border-radius: .7rem; cursor: pointer;
    box-shadow: 0 3px 12px rgba(99,102,241,.3);
    transition: transform .25s, box-shadow .25s, filter .25s;
  }
  .apply-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,.45); filter: brightness(1.08); }

  /* ── Empty state ── */
  .empty-state {
    grid-column: 1 / -1; text-align: center;
    padding: 4rem 2rem; color: var(--muted);
  }
  .empty-state svg { width: 48px; height: 48px; stroke: var(--border); margin-bottom: 1rem; display: block; margin: 0 auto 1rem; }
  .empty-state p { font-size: .95rem; }

  /* ── Modal ── */
  .modal-bg {
    position: fixed; inset: 0; background: rgba(0,0,0,.75);
    backdrop-filter: blur(6px);
    display: none; justify-content: center; align-items: center; z-index: 9999;
  }
  .modal-bg.active { display: flex; }
  .modal {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.5rem; padding: 2.25rem;
    width: 90%; max-width: 520px; max-height: 88vh; overflow-y: auto;
    position: relative; box-shadow: 0 30px 80px rgba(0,0,0,.5);
    animation: fadeUp .3s ease;
  }
  .modal-title {
    font-family: 'Sora', sans-serif; font-size: 1.2rem; font-weight: 700;
    margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: .6rem;
  }
  .modal-title-icon {
    width: 32px; height: 32px; border-radius: .6rem;
    background: rgba(236,72,153,.15);
    display: flex; align-items: center; justify-content: center;
  }
  .close-modal {
    position: absolute; top: 1.25rem; right: 1.25rem;
    width: 30px; height: 30px; border-radius: .5rem;
    background: var(--bg-lift); border: 1px solid var(--border);
    color: var(--muted); font-size: 1.1rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: color .2s, background .2s;
  }
  .close-modal:hover { color: var(--text); background: var(--border); }

  .form-section {
    margin: 1.25rem 0 .75rem;
    display: flex; align-items: center; gap: .75rem;
  }
  .form-section-label { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--secondary); white-space: nowrap; }
  .form-section-line  { flex: 1; height: 1px; background: var(--border); }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
  .form-group { margin-bottom: .85rem; }
  .form-group label { display: block; margin-bottom: .4rem; font-size: .82rem; font-weight: 500; color: var(--text); }

  .modal input, .modal textarea, .modal select {
    width: 100%; padding: .7rem 1rem;
    background: var(--bg-input); border: 1px solid var(--border);
    border-radius: .75rem; color: var(--text); font-size: .87rem;
    font-family: 'Inter', sans-serif;
    transition: border-color .25s, box-shadow .25s;
  }
  .modal input:focus, .modal textarea:focus, .modal select:focus {
    outline: none; border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(236,72,153,.1);
  }
  .modal input::placeholder, .modal textarea::placeholder { color: var(--muted); opacity: .8; }
  .modal textarea { resize: vertical; min-height: 90px; }
  .modal select option { background: var(--bg-card); }

  .modal-footer { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }
  .modal-footer button { width: 100%; }

  /* DB error banner */
  .db-banner {
    display: flex; align-items: center; gap: .75rem;
    background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25);
    color: #fca5a5; border-radius: .75rem;
    padding: .85rem 1.25rem; font-size: .9rem; margin-bottom: 2rem;
  }

  /* Footer */
  footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 1rem; }
  .footer-inner { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
  .footer-brand { font-family:'Sora',sans-serif; font-weight:700; font-size:.95rem; background:var(--g1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .footer-copy  { font-size:.8rem; color:var(--muted); }

  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .hero       { animation: fadeUp .5s ease both; }
  .controls   { animation: fadeUp .5s .08s ease both; }
  .jobs-grid  { animation: fadeUp .5s .15s ease both; }

  @media (max-width: 900px) {
    .jobs-grid { grid-template-columns: 1fr; }
    .form-row  { grid-template-columns: 1fr; }
    .hero      { padding: 2rem 1.5rem; }
  }
  @media (max-width: 580px) {
    .wrap { padding: 1.5rem 1rem 3rem; }
    .controls { flex-direction: column; align-items: stretch; }
  }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/partials/navbar.php'; ?>

<div class="wrap">

  <?php if (isset($db_error)): ?>
  <div class="db-banner">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r=".5" fill="currentColor"/></svg>
    Database unavailable — job listings could not be loaded.
  </div>
  <?php endif; ?>

  <!-- Hero -->
  <div class="hero">
    <div>
      <div class="hero-label"><span class="dot"></span> Jobs & Internships</div>
      <h1>Find your next <em>opportunity</em></h1>
      <p>Browse live listings, filter by type, and post openings for your team.</p>
    </div>
    <div class="hero-stat">
      <div>
        <div class="hero-stat-val"><?= $job_count ?></div>
        <div class="hero-stat-label">Active Listings</div>
      </div>
    </div>
  </div>

  <!-- Controls -->
  <div class="controls">
    <div class="search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" id="job-search" placeholder="Search by title, company or location…">
    </div>
    <select class="filter" id="filter-type">
      <option value="">All Types</option>
      <option value="Full-time">Full-time</option>
      <option value="Internship">Internship</option>
      <option value="Part-time">Part-time</option>
    </select>
    <select class="filter" id="sort-jobs">
      <option value="recent">Newest First</option>
      <option value="title">Title A–Z</option>
    </select>
    <button class="btn btn-secondary" id="post-job-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Post a Job
    </button>
  </div>

  <!-- Grid -->
  <div class="jobs-grid" id="jobs-grid">
    <?php if (!empty($jobs)): ?>
      <?php foreach ($jobs as $i => $j):
        $type_class = match(strtolower($j['type'] ?? '')) {
          'internship'          => 'type-intern',
          'part-time', 'part'   => 'type-part',
          default               => 'type-full'
        };
        $posted = $j['posted_on'] ? date('d M Y', strtotime($j['posted_on'])) : '';
        $poster = $j['poster'] ?? 'Skillsync';
      ?>
      <div class="job-card" style="animation-delay:<?= $i * 0.04 ?>s"
           data-title="<?= htmlspecialchars(strtolower($j['title'])) ?>"
           data-company="<?= htmlspecialchars(strtolower($j['company'])) ?>"
           data-location="<?= htmlspecialchars(strtolower($j['location'] ?? '')) ?>"
           data-type="<?= htmlspecialchars($j['type'] ?? '') ?>">

        <div class="job-card-top">
          <div class="job-title"><?= htmlspecialchars($j['title']) ?></div>
          <span class="job-type-badge <?= $type_class ?>"><?= htmlspecialchars($j['type'] ?? 'Full-time') ?></span>
        </div>

        <div class="job-company"><?= htmlspecialchars($j['company']) ?></div>

        <div class="job-meta-row">
          <?php if (!empty($j['location'])): ?>
          <span class="job-meta">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= htmlspecialchars($j['location']) ?>
          </span>
          <?php endif; ?>
          <?php if (!empty($j['working_hours'])): ?>
          <span class="job-meta">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <?= htmlspecialchars($j['working_hours']) ?>
          </span>
          <?php endif; ?>
          <?php if (!empty($j['salary']) && $j['salary'] !== 'Negotiable'): ?>
          <span class="job-meta">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <?= htmlspecialchars($j['salary']) ?>
          </span>
          <?php endif; ?>
        </div>

        <p class="job-desc"><?= htmlspecialchars($j['description']) ?></p>

        <div class="job-footer">
          <div>
            <div class="job-posted">Posted by <?= htmlspecialchars($poster) ?><?= $posted ? ' · ' . $posted : '' ?></div>
          </div>
          <span class="job-vacancies"><?= (int)$j['vacancies'] ?> vacancy<?= $j['vacancies'] > 1 ? 's' : '' ?></span>
        </div>

        <button class="apply-btn" onclick="handleApply('<?= htmlspecialchars($j['contact']) ?>', '<?= htmlspecialchars($j['title']) ?>')">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          Apply Now
        </button>

      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        <p>No listings yet — be the first to post one!</p>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Post Job Modal -->
<div class="modal-bg" id="job-modal">
  <div class="modal">
    <button class="close-modal" id="close-modal">&times;</button>
    <div class="modal-title">
      <div class="modal-title-icon">
        <svg width="16" height="16" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </div>
      Post a Job
    </div>

    <form id="job-form">
      <div class="form-section"><span class="form-section-label">Role Details</span><div class="form-section-line"></div></div>

      <div class="form-row">
        <div class="form-group">
          <label>Job Title *</label>
          <input type="text" name="title" placeholder="e.g. Frontend Developer" required>
        </div>
        <div class="form-group">
          <label>Company *</label>
          <input type="text" name="company" placeholder="Company name" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Your Position *</label>
          <input type="text" name="position" placeholder="e.g. HR Manager" required>
        </div>
        <div class="form-group">
          <label>Vacancies *</label>
          <input type="number" name="vacancies" placeholder="1" min="1" required>
        </div>
      </div>

      <div class="form-section"><span class="form-section-label">Details</span><div class="form-section-line"></div></div>

      <div class="form-row">
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" placeholder="City or Remote">
        </div>
        <div class="form-group">
          <label>Type</label>
          <select name="type">
            <option>Full-time</option>
            <option>Internship</option>
            <option>Part-time</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Working Hours</label>
          <input type="text" name="working_hours" placeholder="9AM–6PM">
        </div>
        <div class="form-group">
          <label>Salary</label>
          <input type="text" name="salary" placeholder="₹6L–₹9L or Negotiable">
        </div>
      </div>

      <div class="form-group">
        <label>Contact *</label>
        <input type="text" name="contact" placeholder="Phone number or email" required>
      </div>

      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" rows="4" placeholder="Role responsibilities, requirements…" required></textarea>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-secondary" id="submit-job-btn">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Post Job
        </button>
      </div>
    </form>
  </div>
</div>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">Skillsync AI</div>
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

<script>
const modal      = document.getElementById('job-modal');
const openBtn    = document.getElementById('post-job-btn');
const closeBtn   = document.getElementById('close-modal');
const form       = document.getElementById('job-form');
const grid       = document.getElementById('jobs-grid');
const searchInp  = document.getElementById('job-search');
const filterType = document.getElementById('filter-type');
const sortSel    = document.getElementById('sort-jobs');

// ── Modal ──────────────────────────────────────────────────────────────────
openBtn.addEventListener('click', () => { modal.classList.add('active'); document.body.style.overflow = 'hidden'; });
closeBtn.addEventListener('click', closeModal);
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
function closeModal() { modal.classList.remove('active'); document.body.style.overflow = ''; }

// ── Filter & Search ────────────────────────────────────────────────────────
function applyFilters() {
  const q    = searchInp.value.toLowerCase();
  const type = filterType.value.toLowerCase();
  document.querySelectorAll('.job-card').forEach(card => {
    const matchQ    = !q    || card.dataset.title.includes(q) || card.dataset.company.includes(q) || card.dataset.location.includes(q);
    const matchType = !type || card.dataset.type.toLowerCase() === type;
    card.style.display = matchQ && matchType ? '' : 'none';
  });
}
searchInp.addEventListener('input', applyFilters);
filterType.addEventListener('change', applyFilters);

// ── Sort ───────────────────────────────────────────────────────────────────
sortSel.addEventListener('change', () => {
  const cards = [...document.querySelectorAll('.job-card')];
  if (sortSel.value === 'title') {
    cards.sort((a, b) => a.dataset.title.localeCompare(b.dataset.title));
    cards.forEach(c => grid.appendChild(c));
  }
});

// ── Apply ──────────────────────────────────────────────────────────────────
function handleApply(contact, title) {
  const msg = contact.includes('@')
    ? `mailto:${contact}?subject=Application for ${encodeURIComponent(title)}`
    : `tel:${contact}`;
  window.location.href = msg;
}

// ── Post job AJAX ──────────────────────────────────────────────────────────
form.addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('submit-job-btn');
  btn.textContent = 'Posting…'; btn.disabled = true;

  fetch('job_post.php', { method: 'POST', body: new FormData(form) })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        closeModal(); form.reset();
        const j = data.job;
        const typeClass = j.type === 'Internship' ? 'type-intern' : j.type === 'Part-time' ? 'type-part' : 'type-full';
        const card = document.createElement('div');
        card.className = 'job-card';
        card.dataset.title    = (j.title    || '').toLowerCase();
        card.dataset.company  = (j.company  || '').toLowerCase();
        card.dataset.location = (j.location || '').toLowerCase();
        card.dataset.type     = j.type || 'Full-time';
        card.innerHTML = `
          <div class="job-card-top">
            <div class="job-title">${j.title}</div>
            <span class="job-type-badge ${typeClass}">${j.type || 'Full-time'}</span>
          </div>
          <div class="job-company">${j.company}</div>
          <div class="job-meta-row">
            ${j.location ? `<span class="job-meta">${j.location}</span>` : ''}
            ${j.working_hours ? `<span class="job-meta">${j.working_hours}</span>` : ''}
          </div>
          <p class="job-desc">${j.description}</p>
          <div class="job-footer">
            <div class="job-posted">Just posted</div>
            <span class="job-vacancies">${j.vacancies} vacancy</span>
          </div>
          <button class="apply-btn" onclick="handleApply('${j.contact}','${j.title}')">
            Apply Now
          </button>`;
        grid.prepend(card);
      } else {
        alert(data.error || 'Something went wrong.');
      }
    })
    .catch(() => alert('Failed to post. Please try again.'))
    .finally(() => { btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Post Job'; btn.disabled = false; });
});
</script>

</body>
</html>