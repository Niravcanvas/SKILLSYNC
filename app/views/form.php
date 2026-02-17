<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/../config/database.php';

$error_message = '';

// ─── SAVE HANDLER ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    try {
        $full_name = trim($_POST['full_name'] ?? '');
        $headline  = trim($_POST['headline']  ?? '');
        $location  = trim($_POST['location']  ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone']     ?? '');
        $bio       = trim($_POST['bio']       ?? '');

        $profile_picture = null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $upload_dir = __DIR__ . '/../../storage/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $filename)) {
                    $profile_picture = 'storage/uploads/' . $filename;
                }
            }
        }

        if ($profile_picture) {
            $pdo->prepare("UPDATE users SET full_name=?,headline=?,location=?,email=?,phone=?,bio=?,profile_picture=?,profile_complete=1 WHERE id=?")
                ->execute([$full_name,$headline,$location,$email,$phone,$bio,$profile_picture,$user_id]);
        } else {
            $pdo->prepare("UPDATE users SET full_name=?,headline=?,location=?,email=?,phone=?,bio=?,profile_complete=1 WHERE id=?")
                ->execute([$full_name,$headline,$location,$email,$phone,$bio,$user_id]);
        }

        $pdo->prepare("DELETE FROM education WHERE user_id=?")->execute([$user_id]);
        foreach (($_POST['education_institution'] ?? []) as $i => $inst) {
            if (trim($inst) === '') continue;
            $pdo->prepare("INSERT INTO education (user_id,institution,degree,field_of_study,start_year,end_year) VALUES (?,?,?,?,?,?)")
                ->execute([$user_id,trim($inst),trim($_POST['education_degree'][$i]??''),trim($_POST['education_field'][$i]??''),trim($_POST['education_start'][$i]??''),trim($_POST['education_end'][$i]??'')]);
        }

        $pdo->prepare("DELETE FROM experience WHERE user_id=?")->execute([$user_id]);
        foreach (($_POST['experience_company'] ?? []) as $i => $company) {
            if (trim($company) === '') continue;
            $pdo->prepare("INSERT INTO experience (user_id,company,position,start_date,end_date,description) VALUES (?,?,?,?,?,?)")
                ->execute([$user_id,trim($company),trim($_POST['experience_position'][$i]??''),trim($_POST['experience_start'][$i]??'')?:null,trim($_POST['experience_end'][$i]??'')?:null,trim($_POST['experience_description'][$i]??'')]);
        }

        $pdo->prepare("DELETE FROM skills WHERE user_id=?")->execute([$user_id]);
        foreach (($_POST['skills'] ?? []) as $skill) {
            if (trim($skill) === '') continue;
            $pdo->prepare("INSERT INTO skills (user_id,skill_name) VALUES (?,?)")->execute([$user_id,trim($skill)]);
        }

        $pdo->prepare("DELETE FROM certifications WHERE user_id=?")->execute([$user_id]);
        foreach (($_POST['cert_title'] ?? []) as $i => $title) {
            if (trim($title) === '') continue;
            $pdo->prepare("INSERT INTO certifications (user_id,title,issuer,cert_date,url) VALUES (?,?,?,?,?)")
                ->execute([$user_id,trim($title),trim($_POST['cert_issuer'][$i]??''),trim($_POST['cert_date'][$i]??'')?:null,trim($_POST['cert_url'][$i]??'')]);
        }

        $pdo->prepare("DELETE FROM projects WHERE user_id=?")->execute([$user_id]);
        foreach (($_POST['project_name'] ?? []) as $i => $name) {
            if (trim($name) === '') continue;
            $pdo->prepare("INSERT INTO projects (user_id,project_name,description,technologies,project_url) VALUES (?,?,?,?,?)")
                ->execute([$user_id,trim($name),trim($_POST['project_description'][$i]??''),trim($_POST['project_tech'][$i]??''),trim($_POST['project_url'][$i]??'')]);
        }

        header("Location: ../../app/views/dashboard.php?saved=1");
        exit;

    } catch (PDOException $e) {
        error_log("Save profile error: " . $e->getMessage());
        $error_message = "An error occurred while saving. Please try again.";
    }
}

// ─── FETCH DATA ───────────────────────────────────────────────────────────────
try {
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { header("Location: ../../index.php"); exit; }

    function fetchData($pdo, $table, $user_id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $educations     = fetchData($pdo, 'education',      $user_id);
    $experiences    = fetchData($pdo, 'experience',     $user_id);
    $skills         = fetchData($pdo, 'skills',         $user_id);
    $certifications = fetchData($pdo, 'certifications', $user_id);
    $projects       = fetchData($pdo, 'projects',       $user_id);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Your Profile – Skillsync AI</title>
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
      --bg-input:   #0d1424;
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

    /* ── Toast ── */
    .toast {
      position: fixed; top: 90px; right: 1.5rem; z-index: 1000;
      padding: 1rem 1.5rem; border-radius: .85rem; font-weight: 600; font-size: .9rem;
      max-width: 360px; box-shadow: 0 10px 30px rgba(0,0,0,.3);
      animation: toastIn .4s cubic-bezier(.4,0,.2,1), toastOut .4s ease 4.6s forwards;
    }
    .toast-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #fca5a5; }
    @keyframes toastIn  { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:translateX(0)} }
    @keyframes toastOut { to{opacity:0;pointer-events:none} }

    /* ── Wrap ── */
    .wrap { max-width: 900px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

    /* ── Hero ── */
    .hero {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 1.75rem; padding: 2.5rem 3rem; margin-bottom: 2rem;
      position: relative; overflow: hidden;
      animation: fadeUp .5s ease both;
    }
    .hero::after {
      content: ''; position: absolute; top: -80px; right: -80px;
      width: 320px; height: 320px;
      background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-label {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
      color: var(--primary); background: rgba(99,102,241,.1);
      border: 1px solid rgba(99,102,241,.2); padding: .28rem .85rem;
      border-radius: 999px; margin-bottom: 1rem;
    }
    .hero-label .dot {
      width: 6px; height: 6px; background: var(--primary);
      border-radius: 50%; animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
    .hero h1 {
      font-family: 'Sora', sans-serif;
      font-size: clamp(1.5rem, 3vw, 2.1rem);
      font-weight: 700; letter-spacing: -.025em; margin-bottom: .5rem;
    }
    .hero h1 em {
      font-style: normal;
      background: var(--g1);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero p { color: var(--muted); font-size: .95rem; }

    /* ── Cards ── */
    .card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 1.5rem; padding: 2rem; margin-bottom: 1.5rem;
      position: relative; overflow: hidden;
      transition: border-color .3s, transform .3s, box-shadow .3s;
      animation: fadeUp .5s ease both;
    }
    .card:hover { border-color: var(--border-lit); transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,.25); }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      transform: scaleX(0); transform-origin: left; transition: transform .4s ease;
    }
    .card:hover::before { transform: scaleX(1); }
    .card.c1::before { background: var(--g1); }
    .card.c2::before { background: var(--g2); }
    .card.c3::before { background: var(--g3); }
    .card.c4::before { background: var(--g4); }

    .card-title {
      font-family: 'Sora', sans-serif; font-size: 1.1rem; font-weight: 700;
      margin-bottom: 1.5rem; padding-bottom: .85rem; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: .6rem;
    }
    .card-title svg { flex-shrink: 0; }

    /* ── Grid ── */
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

    /* ── Form fields ── */
    .field { margin-bottom: 1.1rem; }
    .field:last-child { margin-bottom: 0; }
    label {
      display: block; font-size: .83rem; font-weight: 500;
      color: var(--text); margin-bottom: .4rem; letter-spacing: .01em;
    }
    input[type="text"],
    input[type="email"],
    input[type="url"],
    input[type="month"],
    textarea {
      width: 100%; padding: .75rem 1rem;
      background: var(--bg-input); border: 1px solid var(--border);
      border-radius: .75rem; color: var(--text);
      font-size: .88rem; font-family: 'Inter', sans-serif;
      transition: border-color .3s, box-shadow .3s, background .3s;
    }
    input:focus, textarea:focus {
      outline: none; border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99,102,241,.12);
      background: var(--bg-lift);
    }
    textarea { min-height: 90px; resize: vertical; line-height: 1.6; }

    input[type="file"] {
      width: 100%; padding: .6rem 1rem;
      background: var(--bg-input); border: 1px solid var(--border);
      border-radius: .75rem; color: var(--muted);
      font-size: .85rem; font-family: 'Inter', sans-serif; cursor: pointer;
      transition: border-color .3s;
    }
    input[type="file"]:hover { border-color: var(--border-lit); }
    input[type="file"]::file-selector-button {
      background: var(--g1); color: #fff; border: none;
      padding: .4rem .9rem; border-radius: .5rem;
      cursor: pointer; font-weight: 600; font-size: .8rem;
      margin-right: .75rem; transition: opacity .2s;
    }
    input[type="file"]::file-selector-button:hover { opacity: .85; }

    .profile-preview {
      width: 72px; height: 72px; border-radius: 50%; margin-top: .6rem;
      border: 2px solid var(--primary); object-fit: cover;
      box-shadow: 0 0 12px rgba(99,102,241,.3);
    }

    /* ── Entries ── */
    .entry {
      background: var(--bg); border: 1px solid var(--border);
      border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem;
      position: relative; transition: border-color .25s, background .25s;
    }
    .entry:hover { border-color: var(--border-lit); background: var(--bg-lift); }

    .btn-remove {
      position: absolute; top: 1rem; right: 1rem;
      display: inline-flex; align-items: center; gap: .3rem;
      background: rgba(239,68,68,.1); color: #fca5a5;
      border: 1px solid rgba(239,68,68,.2); padding: .3rem .75rem;
      border-radius: .5rem; font-size: .75rem; font-weight: 600;
      cursor: pointer; transition: background .2s, border-color .2s;
    }
    .btn-remove:hover { background: rgba(239,68,68,.2); border-color: rgba(239,68,68,.4); }

    /* ── Skills ── */
    .skill-row { display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem; }
    .skill-row input { flex: 1; margin: 0; }
    .skill-row .btn-remove { position: static; }

    /* ── Add / Save buttons ── */
    .btn-add {
      display: inline-flex; align-items: center; gap: .4rem;
      background: transparent; color: var(--accent);
      border: 1px solid rgba(20,184,166,.3); padding: .55rem 1.1rem;
      border-radius: .65rem; font-size: .83rem; font-weight: 600;
      cursor: pointer; transition: background .25s, border-color .25s, transform .2s;
      margin-top: .25rem;
    }
    .btn-add:hover { background: rgba(20,184,166,.08); border-color: rgba(20,184,166,.5); transform: translateY(-1px); }

    .btn-save {
      display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
      background: var(--g1); color: #fff;
      border: none; padding: .95rem 2.5rem;
      border-radius: .85rem; font-size: 1rem; font-weight: 600;
      font-family: 'Inter', sans-serif; cursor: pointer;
      box-shadow: 0 4px 20px rgba(99,102,241,.35);
      transition: transform .25s, box-shadow .25s, filter .25s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99,102,241,.55); filter: brightness(1.08); }
    .btn-save.saving { opacity: .65; pointer-events: none; }

    .submit-wrap { text-align: center; margin-top: 1rem; padding: 1.5rem 0; }

    /* ── Animation delays ── */
    .card:nth-child(1) { animation-delay: .05s; }
    .card:nth-child(2) { animation-delay: .1s; }
    .card:nth-child(3) { animation-delay: .15s; }
    .card:nth-child(4) { animation-delay: .2s; }
    .card:nth-child(5) { animation-delay: .25s; }
    .card:nth-child(6) { animation-delay: .3s; }
    .card:nth-child(7) { animation-delay: .35s; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

    /* ── Footer ── */
    footer { border-top: 1px solid var(--border); padding: 2rem; }
    .footer-inner { max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .footer-brand { font-family: 'Sora', sans-serif; font-weight: 700; font-size: .95rem; background: var(--g1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .footer-copy  { font-size: .8rem; color: var(--muted); }

    /* ── Responsive ── */
    @media (max-width: 640px) {
      .wrap { padding: 1.5rem 1rem 3rem; }
      .hero { padding: 1.75rem 1.5rem; }
      .field-grid { grid-template-columns: 1fr; }
      .card { padding: 1.5rem; }
      .btn-save { width: 100%; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<?php if ($error_message): ?>
  <div class="toast toast-error">✗ <?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="wrap">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-label"><span class="dot"></span> Profile Setup</div>
    <h1>Welcome, <em><?= htmlspecialchars($user['full_name'] ?? 'New User') ?></em> 👋</h1>
    <p>Complete your profile to unlock AI-powered job matches and personalised resume suggestions.</p>
  </div>

  <form id="profile-form" method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="save_profile" value="1">

    <!-- Personal Info -->
    <div class="card c1">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Personal Information
      </div>
      <div class="field-grid">
        <div>
          <div class="field">
            <label>Full Name *</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required placeholder="e.g., Nirav Thakur">
          </div>
          <div class="field">
            <label>Headline</label>
            <input type="text" name="headline" value="<?= htmlspecialchars($user['headline'] ?? '') ?>" placeholder="e.g., Full Stack Developer | AI Enthusiast">
          </div>
          <div class="field">
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" placeholder="e.g., Mumbai, India">
          </div>
        </div>
        <div>
          <div class="field">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
          </div>
          <div class="field">
            <label>Profile Picture</label>
            <input type="file" name="profile_picture" accept="image/*">
            <?php if (!empty($user['profile_picture'])): ?>
              <img src="<?= htmlspecialchars('../../' . $user['profile_picture']) ?>" alt="Profile" class="profile-preview">
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Bio -->
    <div class="card c1">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        About Me
      </div>
      <div class="field">
        <label>Bio / Summary</label>
        <textarea name="bio" placeholder="Tell us about yourself, your career goals, and what makes you unique..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Education -->
    <div class="card c3">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#2dd4bf" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Education
      </div>
      <div id="education-container">
        <?php if (!empty($educations)): foreach ($educations as $edu): ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Institution</label><input type="text" name="education_institution[]" value="<?= htmlspecialchars($edu['institution'] ?? '') ?>" placeholder="e.g., University of Mumbai"></div>
              <div class="field"><label>Degree</label><input type="text" name="education_degree[]" value="<?= htmlspecialchars($edu['degree'] ?? '') ?>" placeholder="e.g., Bachelor of Science"></div>
            </div>
            <div class="field-grid">
              <div class="field"><label>Field of Study</label><input type="text" name="education_field[]" value="<?= htmlspecialchars($edu['field_of_study'] ?? '') ?>" placeholder="e.g., Computer Science"></div>
              <div class="field-grid">
                <div class="field"><label>Start Year</label><input type="text" name="education_start[]" value="<?= htmlspecialchars($edu['start_year'] ?? '') ?>" placeholder="2020"></div>
                <div class="field"><label>End Year</label><input type="text" name="education_end[]" value="<?= htmlspecialchars($edu['end_year'] ?? '') ?>" placeholder="2024"></div>
              </div>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Institution</label><input type="text" name="education_institution[]" placeholder="e.g., University of Mumbai"></div>
              <div class="field"><label>Degree</label><input type="text" name="education_degree[]" placeholder="e.g., Bachelor of Science"></div>
            </div>
            <div class="field-grid">
              <div class="field"><label>Field of Study</label><input type="text" name="education_field[]" placeholder="e.g., Computer Science"></div>
              <div class="field-grid">
                <div class="field"><label>Start Year</label><input type="text" name="education_start[]" placeholder="2020"></div>
                <div class="field"><label>End Year</label><input type="text" name="education_end[]" placeholder="2024"></div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addEducation()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Education
      </button>
    </div>

    <!-- Experience -->
    <div class="card c2">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Work Experience
      </div>
      <div id="experience-container">
        <?php if (!empty($experiences)): foreach ($experiences as $exp): ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Company</label><input type="text" name="experience_company[]" value="<?= htmlspecialchars($exp['company'] ?? '') ?>" placeholder="e.g., Google"></div>
              <div class="field"><label>Position</label><input type="text" name="experience_position[]" value="<?= htmlspecialchars($exp['position'] ?? '') ?>" placeholder="e.g., Software Engineer"></div>
            </div>
            <div class="field-grid">
              <!-- FIX: substr(..., 0, 7) trims full DB date (e.g. 2023-01-15) to YYYY-MM for <input type="month"> -->
              <div class="field"><label>Start Date</label><input type="month" name="experience_start[]" value="<?= htmlspecialchars(substr($exp['start_date'] ?? '', 0, 7)) ?>"></div>
              <div class="field"><label>End Date</label><input type="month" name="experience_end[]" value="<?= htmlspecialchars(substr($exp['end_date'] ?? '', 0, 7)) ?>"></div>
            </div>
            <div class="field"><label>Description</label><textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."><?= htmlspecialchars($exp['description'] ?? '') ?></textarea></div>
          </div>
        <?php endforeach; else: ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Company</label><input type="text" name="experience_company[]" placeholder="e.g., Google"></div>
              <div class="field"><label>Position</label><input type="text" name="experience_position[]" placeholder="e.g., Software Engineer"></div>
            </div>
            <div class="field-grid">
              <div class="field"><label>Start Date</label><input type="month" name="experience_start[]"></div>
              <div class="field"><label>End Date</label><input type="month" name="experience_end[]"></div>
            </div>
            <div class="field"><label>Description</label><textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."></textarea></div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addExperience()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Experience
      </button>
    </div>

    <!-- Skills -->
    <div class="card c1">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Skills
      </div>
      <div id="skills-container">
        <?php if (!empty($skills)): foreach ($skills as $skill): ?>
          <div class="skill-row">
            <input type="text" name="skills[]" value="<?= htmlspecialchars($skill['skill_name'] ?? '') ?>" placeholder="e.g., React, Python, MySQL">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
          </div>
        <?php endforeach; else: ?>
          <div class="skill-row">
            <input type="text" name="skills[]" placeholder="e.g., React, Python, MySQL">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addSkill()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Skill
      </button>
    </div>

    <!-- Certifications -->
    <div class="card c4">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
        Certifications &amp; Awards
      </div>
      <div id="certifications-container">
        <?php if (!empty($certifications)): foreach ($certifications as $cert): ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Title</label><input type="text" name="cert_title[]" value="<?= htmlspecialchars($cert['title'] ?? '') ?>" placeholder="e.g., AWS Certified Developer"></div>
              <div class="field"><label>Issuer</label><input type="text" name="cert_issuer[]" value="<?= htmlspecialchars($cert['issuer'] ?? '') ?>" placeholder="e.g., Amazon Web Services"></div>
            </div>
            <div class="field-grid">
              <!-- FIX: substr(..., 0, 7) trims full DB date to YYYY-MM for <input type="month"> -->
              <div class="field"><label>Date</label><input type="month" name="cert_date[]" value="<?= htmlspecialchars(substr($cert['cert_date'] ?? '', 0, 7)) ?>"></div>
              <div class="field"><label>Credential URL</label><input type="url" name="cert_url[]" value="<?= htmlspecialchars($cert['url'] ?? '') ?>" placeholder="https://..."></div>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Title</label><input type="text" name="cert_title[]" placeholder="e.g., AWS Certified Developer"></div>
              <div class="field"><label>Issuer</label><input type="text" name="cert_issuer[]" placeholder="e.g., Amazon Web Services"></div>
            </div>
            <div class="field-grid">
              <div class="field"><label>Date</label><input type="month" name="cert_date[]"></div>
              <div class="field"><label>Credential URL</label><input type="url" name="cert_url[]" placeholder="https://..."></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addCert()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Certification
      </button>
    </div>

    <!-- Projects -->
    <div class="card c3">
      <div class="card-title">
        <svg width="18" height="18" fill="none" stroke="#2dd4bf" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Projects / Portfolio
      </div>
      <div id="projects-container">
        <?php if (!empty($projects)): foreach ($projects as $proj): ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Project Name</label><input type="text" name="project_name[]" value="<?= htmlspecialchars($proj['project_name'] ?? '') ?>" placeholder="e.g., Skillsync AI"></div>
              <div class="field"><label>Technologies Used</label><input type="text" name="project_tech[]" value="<?= htmlspecialchars($proj['technologies'] ?? '') ?>" placeholder="e.g., PHP, MySQL, React"></div>
            </div>
            <div class="field"><label>Description</label><textarea name="project_description[]" placeholder="Describe what you built and the impact..."><?= htmlspecialchars($proj['description'] ?? '') ?></textarea></div>
            <div class="field"><label>Project URL</label><input type="url" name="project_url[]" value="<?= htmlspecialchars($proj['project_url'] ?? '') ?>" placeholder="https://github.com/..."></div>
          </div>
        <?php endforeach; else: ?>
          <div class="entry">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
            <div class="field-grid">
              <div class="field"><label>Project Name</label><input type="text" name="project_name[]" placeholder="e.g., Skillsync AI"></div>
              <div class="field"><label>Technologies Used</label><input type="text" name="project_tech[]" placeholder="e.g., PHP, MySQL, React"></div>
            </div>
            <div class="field"><label>Description</label><textarea name="project_description[]" placeholder="Describe what you built and the impact..."></textarea></div>
            <div class="field"><label>Project URL</label><input type="url" name="project_url[]" placeholder="https://github.com/..."></div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addProject()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Project
      </button>
    </div>

    <!-- Submit -->
    <div class="submit-wrap">
      <button type="submit" class="btn-save" id="save-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Profile
      </button>
    </div>

  </form>
</div>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">Skillsync AI</div>
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

<script>
const eduTemplate = () => `
  <div class="entry">
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
    <div class="field-grid">
      <div class="field"><label>Institution</label><input type="text" name="education_institution[]" placeholder="e.g., University of Mumbai"></div>
      <div class="field"><label>Degree</label><input type="text" name="education_degree[]" placeholder="e.g., Bachelor of Science"></div>
    </div>
    <div class="field-grid">
      <div class="field"><label>Field of Study</label><input type="text" name="education_field[]" placeholder="e.g., Computer Science"></div>
      <div class="field-grid">
        <div class="field"><label>Start Year</label><input type="text" name="education_start[]" placeholder="2020"></div>
        <div class="field"><label>End Year</label><input type="text" name="education_end[]" placeholder="2024"></div>
      </div>
    </div>
  </div>`;

const expTemplate = () => `
  <div class="entry">
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
    <div class="field-grid">
      <div class="field"><label>Company</label><input type="text" name="experience_company[]" placeholder="e.g., Google"></div>
      <div class="field"><label>Position</label><input type="text" name="experience_position[]" placeholder="e.g., Software Engineer"></div>
    </div>
    <div class="field-grid">
      <div class="field"><label>Start Date</label><input type="month" name="experience_start[]"></div>
      <div class="field"><label>End Date</label><input type="month" name="experience_end[]"></div>
    </div>
    <div class="field"><label>Description</label><textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."></textarea></div>
  </div>`;

const certTemplate = () => `
  <div class="entry">
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
    <div class="field-grid">
      <div class="field"><label>Title</label><input type="text" name="cert_title[]" placeholder="e.g., AWS Certified Developer"></div>
      <div class="field"><label>Issuer</label><input type="text" name="cert_issuer[]" placeholder="e.g., Amazon Web Services"></div>
    </div>
    <div class="field-grid">
      <div class="field"><label>Date</label><input type="month" name="cert_date[]"></div>
      <div class="field"><label>Credential URL</label><input type="url" name="cert_url[]" placeholder="https://..."></div>
    </div>
  </div>`;

const projTemplate = () => `
  <div class="entry">
    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕ Remove</button>
    <div class="field-grid">
      <div class="field"><label>Project Name</label><input type="text" name="project_name[]" placeholder="e.g., Skillsync AI"></div>
      <div class="field"><label>Technologies Used</label><input type="text" name="project_tech[]" placeholder="e.g., PHP, MySQL, React"></div>
    </div>
    <div class="field"><label>Description</label><textarea name="project_description[]" placeholder="Describe what you built and the impact..."></textarea></div>
    <div class="field"><label>Project URL</label><input type="url" name="project_url[]" placeholder="https://github.com/..."></div>
  </div>`;

function addEducation() { document.getElementById('education-container').insertAdjacentHTML('beforeend', eduTemplate()); }
function addExperience() { document.getElementById('experience-container').insertAdjacentHTML('beforeend', expTemplate()); }
function addCert()       { document.getElementById('certifications-container').insertAdjacentHTML('beforeend', certTemplate()); }
function addProject()    { document.getElementById('projects-container').insertAdjacentHTML('beforeend', projTemplate()); }
function addSkill() {
  document.getElementById('skills-container').insertAdjacentHTML('beforeend', `
    <div class="skill-row">
      <input type="text" name="skills[]" placeholder="e.g., React, Python, MySQL">
      <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
    </div>`);
}

document.getElementById('profile-form').addEventListener('submit', function() {
  const btn = document.getElementById('save-btn');
  btn.innerHTML = '<svg width="16" height="16" style="animation:spin 1s linear infinite" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving...';
  btn.classList.add('saving');
});

setTimeout(() => document.querySelectorAll('.toast').forEach(t => t.remove()), 5000);
</script>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>