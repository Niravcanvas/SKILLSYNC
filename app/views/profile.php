<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { header("Location: ../../index.php"); exit; }

    $stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY start_year DESC");
    $stmt->execute([$user_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM experience WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$user_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM skills WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY cert_date DESC");
    $stmt->execute([$user_id]);
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}

// Profile completeness
$fields = ['full_name','bio','location','headline','phone','profile_picture'];
$filled = array_sum(array_map(fn($f) => !empty($user[$f]) ? 1 : 0, $fields));
$profile_pct = round(($filled / count($fields)) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
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
    --g4: linear-gradient(135deg, #f59e0b, #f97316);
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

  .wrap { max-width: 1100px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

  /* ── Hero / Profile header ── */
  .profile-hero {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.75rem; padding: 2.5rem;
    margin-bottom: 1.5rem; position: relative; overflow: hidden;
    animation: fadeUp .5s ease both;
  }
  .profile-hero::after {
    content: ''; position: absolute; top: -80px; right: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(99,102,241,.09) 0%, transparent 70%);
    pointer-events: none;
  }
  .hero-label {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
    color: var(--primary); background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.2); padding: .25rem .8rem;
    border-radius: 999px; margin-bottom: 1.5rem;
  }
  .hero-label .dot { width:6px; height:6px; background:var(--primary); border-radius:50%; animation:pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

  .profile-top {
    display: flex; align-items: flex-start; gap: 2rem; flex-wrap: wrap;
  }

  .avatar-wrap {
    position: relative; flex-shrink: 0;
    width: 110px; height: 110px;
  }
  .avatar-wrap img {
    width: 110px; height: 110px;
    border-radius: 50%; object-fit: cover;
    border: 3px solid var(--primary);
    box-shadow: 0 0 0 4px rgba(99,102,241,.15);
  }
  .avatar-online {
    position: absolute; bottom: 6px; right: 6px;
    width: 14px; height: 14px; background: var(--success);
    border-radius: 50%; border: 2px solid var(--bg-card);
  }

  .profile-info { flex: 1; min-width: 220px; }
  .profile-name {
    font-family: 'Sora', sans-serif;
    font-size: clamp(1.4rem, 3vw, 2rem);
    font-weight: 700; letter-spacing: -.025em; line-height: 1.2;
    margin-bottom: .35rem;
  }
  .profile-headline {
    font-size: .95rem; color: var(--muted); margin-bottom: .9rem;
  }
  .profile-meta {
    display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.1rem;
  }
  .meta-pill {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .78rem; color: var(--muted);
    background: var(--bg); border: 1px solid var(--border);
    padding: .22rem .7rem; border-radius: 999px;
  }
  .meta-pill svg { width: 11px; height: 11px; stroke: var(--muted); }

  /* Completeness bar */
  .completeness {
    margin-top: 1rem;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 1rem; padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 1rem;
  }
  .comp-label { font-size: .8rem; color: var(--muted); white-space: nowrap; }
  .comp-bar { flex: 1; height: 5px; background: var(--border); border-radius: 999px; overflow: hidden; }
  .comp-fill { height: 100%; background: var(--g4); border-radius: 999px; transition: width 1s ease; }
  .comp-pct { font-size: .82rem; font-weight: 600; color: #fbbf24; white-space: nowrap; }

  .edit-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .65rem 1.35rem;
    background: var(--g1); color: #fff;
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .85rem;
    border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
    box-shadow: 0 4px 16px rgba(99,102,241,.35);
    transition: transform .25s, box-shadow .25s, filter .25s;
    margin-top: 1.25rem;
  }
  .edit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,.5); filter: brightness(1.08); }

  /* ── Section cards ── */
  .section {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.5rem; padding: 2rem;
    margin-bottom: 1.25rem;
    transition: border-color .35s, transform .35s, box-shadow .35s;
    position: relative; overflow: hidden;
    animation: fadeUp .5s ease both;
  }
  .section:hover { border-color: var(--border-lit); transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,.25); }
  .section::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    transform: scaleX(0); transform-origin: left; transition: transform .4s ease;
  }
  .section:hover::before { transform: scaleX(1); }
  .s1::before { background: var(--g1); }
  .s2::before { background: var(--g3); }
  .s3::before { background: var(--g2); }
  .s4::before { background: var(--g4); }

  .section-header {
    display: flex; align-items: center; gap: .65rem;
    margin-bottom: 1.5rem; padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
  }
  .section-icon {
    width: 34px; height: 34px; border-radius: .6rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .si-purple { background: rgba(99,102,241,.15); }
  .si-teal   { background: rgba(20,184,166,.15); }
  .si-pink   { background: rgba(236,72,153,.15); }
  .si-amber  { background: rgba(245,158,11,.15); }

  .section-title {
    font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 600;
  }
  .section-count {
    margin-left: auto; font-size: .72rem; font-weight: 600;
    padding: .18rem .6rem; border-radius: 999px;
    background: rgba(99,102,241,.1); color: #818cf8;
    border: 1px solid rgba(99,102,241,.2);
  }

  /* ── Bio ── */
  .bio-text { color: var(--muted); font-size: .93rem; line-height: 1.75; }

  /* ── Sub items ── */
  .items-grid { display: flex; flex-direction: column; gap: .75rem; }

  .item-card {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 1rem; padding: 1.1rem 1.25rem;
    transition: border-color .25s, transform .25s, background .25s;
  }
  .item-card:hover { border-color: var(--border-lit); background: var(--bg-lift); transform: translateX(5px); }

  .item-title { font-family: 'Sora', sans-serif; font-size: .95rem; font-weight: 600; margin-bottom: .25rem; }
  .item-sub   { font-size: .83rem; color: var(--muted); margin-bottom: .15rem; }
  .item-date  { font-size: .78rem; color: var(--muted); opacity: .7; }
  .item-desc  { font-size: .83rem; color: var(--muted); margin-top: .5rem; line-height: 1.6; }

  .item-link {
    display: inline-flex; align-items: center; gap: .3rem;
    margin-top: .6rem; font-size: .8rem; font-weight: 600;
    color: var(--accent); text-decoration: none;
    transition: gap .2s, color .2s;
  }
  .item-link:hover { color: var(--secondary); gap: .5rem; }
  .item-link svg { width: 12px; height: 12px; stroke: currentColor; }

  /* ── Skills pills ── */
  .skills-wrap { display: flex; flex-wrap: wrap; gap: .6rem; }
  .skill-pill {
    display: inline-flex; align-items: center;
    padding: .4rem 1rem; border-radius: 999px;
    font-size: .82rem; font-weight: 600;
    background: rgba(99,102,241,.12); color: #818cf8;
    border: 1px solid rgba(99,102,241,.2);
    transition: transform .2s, box-shadow .2s, background .2s;
    cursor: default;
  }
  .skill-pill:hover { transform: translateY(-3px) scale(1.05); background: rgba(99,102,241,.22); box-shadow: 0 4px 14px rgba(99,102,241,.25); }

  /* ── Empty state ── */
  .empty { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: .87rem; font-style: italic; }

  /* Animations */
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .section:nth-child(1) { animation-delay: .05s; }
  .section:nth-child(2) { animation-delay: .10s; }
  .section:nth-child(3) { animation-delay: .15s; }
  .section:nth-child(4) { animation-delay: .20s; }
  .section:nth-child(5) { animation-delay: .25s; }
  .section:nth-child(6) { animation-delay: .30s; }

  footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 1rem; }
  .footer-inner { max-width: 1100px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
  .footer-brand { font-family:'Sora',sans-serif; font-weight:700; font-size:.95rem; background:var(--g1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .footer-copy  { font-size:.8rem; color:var(--muted); }

  @media (max-width: 680px) {
    .wrap { padding: 1.5rem 1rem 3rem; }
    .profile-top { flex-direction: column; align-items: center; text-align: center; }
    .profile-meta { justify-content: center; }
    .edit-btn { width: 100%; justify-content: center; }
  }
</style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<div class="wrap">

  <!-- Profile Hero -->
  <div class="profile-hero">
    <div class="hero-label"><span class="dot"></span> Your Profile</div>
    <div class="profile-top">
      <div class="avatar-wrap">
        <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : '../../public/images/default-profile.svg' ?>" alt="Profile">
        <div class="avatar-online"></div>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($user['full_name'] ?? 'No Name Set') ?></div>
        <div class="profile-headline"><?= htmlspecialchars($user['headline'] ?? 'No headline yet') ?></div>
        <div class="profile-meta">
          <?php if (!empty($user['location'])): ?>
          <span class="meta-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= htmlspecialchars($user['location']) ?>
          </span>
          <?php endif; ?>
          <span class="meta-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <?= htmlspecialchars($user['email']) ?>
          </span>
          <?php if (!empty($user['phone'])): ?>
          <span class="meta-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.8a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.6a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.44 18l.48-1.08z"/></svg>
            <?= htmlspecialchars($user['phone']) ?>
          </span>
          <?php endif; ?>
        </div>
        <div class="completeness">
          <span class="comp-label">Profile Complete</span>
          <div class="comp-bar"><div class="comp-fill" style="width:<?= $profile_pct ?>%"></div></div>
          <span class="comp-pct"><?= $profile_pct ?>%</span>
        </div>
        <a href="form.php" class="edit-btn">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Profile
        </a>
      </div>
    </div>
  </div>

  <!-- About -->
  <section class="section s1">
    <div class="section-header">
      <div class="section-icon si-purple">
        <svg width="16" height="16" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      </div>
      <div class="section-title">About Me</div>
    </div>
    <?php if (!empty($user['bio'])): ?>
      <p class="bio-text"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    <?php else: ?>
      <p class="empty">No bio added yet — click Edit Profile to add one.</p>
    <?php endif; ?>
  </section>

  <!-- Education -->
  <section class="section s2">
    <div class="section-header">
      <div class="section-icon si-teal">
        <svg width="16" height="16" fill="none" stroke="#2dd4bf" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      </div>
      <div class="section-title">Education</div>
      <?php if (!empty($education)): ?><span class="section-count"><?= count($education) ?></span><?php endif; ?>
    </div>
    <div class="items-grid">
      <?php if (!empty($education)): ?>
        <?php foreach ($education as $edu): ?>
        <div class="item-card">
          <div class="item-title"><?= htmlspecialchars($edu['degree']) ?> in <?= htmlspecialchars($edu['field_of_study']) ?></div>
          <div class="item-sub"><?= htmlspecialchars($edu['institution']) ?></div>
          <div class="item-date"><?= htmlspecialchars($edu['start_year']) ?> – <?= htmlspecialchars($edu['end_year']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">No education added yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Experience -->
  <section class="section s3">
    <div class="section-header">
      <div class="section-icon si-pink">
        <svg width="16" height="16" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
      </div>
      <div class="section-title">Work Experience</div>
      <?php if (!empty($experience)): ?><span class="section-count"><?= count($experience) ?></span><?php endif; ?>
    </div>
    <div class="items-grid">
      <?php if (!empty($experience)): ?>
        <?php foreach ($experience as $exp): ?>
        <div class="item-card">
          <div class="item-title"><?= htmlspecialchars($exp['position']) ?> · <?= htmlspecialchars($exp['company']) ?></div>
          <div class="item-date">
            <?= $exp['start_date'] ? date('M Y', strtotime($exp['start_date'])) : '' ?>
            <?= $exp['end_date'] ? ' – ' . date('M Y', strtotime($exp['end_date'])) : ' – Present' ?>
          </div>
          <?php if (!empty($exp['description'])): ?>
          <div class="item-desc"><?= nl2br(htmlspecialchars($exp['description'])) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">No work experience added yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Skills -->
  <section class="section s1">
    <div class="section-header">
      <div class="section-icon si-purple">
        <svg width="16" height="16" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>
      <div class="section-title">Skills</div>
      <?php if (!empty($skills)): ?><span class="section-count"><?= count($skills) ?></span><?php endif; ?>
    </div>
    <?php if (!empty($skills)): ?>
      <div class="skills-wrap">
        <?php foreach ($skills as $sk): ?>
          <span class="skill-pill"><?= htmlspecialchars($sk['skill_name']) ?></span>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty">No skills added yet.</p>
    <?php endif; ?>
  </section>

  <!-- Certifications -->
  <section class="section s4">
    <div class="section-header">
      <div class="section-icon si-amber">
        <svg width="16" height="16" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
      </div>
      <div class="section-title">Certifications</div>
      <?php if (!empty($certifications)): ?><span class="section-count"><?= count($certifications) ?></span><?php endif; ?>
    </div>
    <div class="items-grid">
      <?php if (!empty($certifications)): ?>
        <?php foreach ($certifications as $cert): ?>
        <div class="item-card">
          <div class="item-title"><?= htmlspecialchars($cert['title']) ?></div>
          <div class="item-sub"><?= htmlspecialchars($cert['issuer']) ?></div>
          <?php if ($cert['cert_date']): ?>
          <div class="item-date"><?= date('M Y', strtotime($cert['cert_date'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($cert['url'])): ?>
          <a class="item-link" href="<?= htmlspecialchars($cert['url']) ?>" target="_blank" rel="noopener">
            View Credential
            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">No certifications added yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Projects -->
  <section class="section s2">
    <div class="section-header">
      <div class="section-icon si-teal">
        <svg width="16" height="16" fill="none" stroke="#2dd4bf" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
      </div>
      <div class="section-title">Projects</div>
      <?php if (!empty($projects)): ?><span class="section-count"><?= count($projects) ?></span><?php endif; ?>
    </div>
    <div class="items-grid">
      <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $proj): ?>
        <div class="item-card">
          <div class="item-title"><?= htmlspecialchars($proj['project_name']) ?></div>
          <?php if (!empty($proj['technologies'])): ?>
          <div class="item-sub"><?= htmlspecialchars($proj['technologies']) ?></div>
          <?php endif; ?>
          <?php if (!empty($proj['description'])): ?>
          <div class="item-desc"><?= nl2br(htmlspecialchars($proj['description'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($proj['project_url'])): ?>
          <a class="item-link" href="<?= htmlspecialchars($proj['project_url']) ?>" target="_blank" rel="noopener">
            View Project
            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">No projects added yet.</p>
      <?php endif; ?>
    </div>
  </section>

</div>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">Skillsync AI</div>
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

</body>
</html>