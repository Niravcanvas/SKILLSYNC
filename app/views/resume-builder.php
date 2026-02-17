<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/resume.php";
$username = explode('@', $email)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume Builder – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
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
    --radius: 1.25rem;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

  /* ── Layout ── */
  .wrap { max-width: 1600px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

  /* ── Hero ── */
  .hero {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 1.75rem; padding: 2.75rem 3rem;
    margin-bottom: 2rem; position: relative; overflow: hidden;
  }
  .hero::after {
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
  .hero-label .dot {
    width: 6px; height: 6px; background: var(--primary);
    border-radius: 50%; animation: pulse 2s infinite;
  }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

  .hero h1 {
    font-family: 'Sora', sans-serif;
    font-size: clamp(1.6rem, 3.5vw, 2.3rem);
    font-weight: 700; letter-spacing: -.025em; line-height: 1.25;
    margin-bottom: .6rem;
  }
  .hero h1 em {
    font-style: normal; background: var(--g1);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  }
  .hero p { color: var(--muted); font-size: 1rem; max-width: 500px; }

  /* ── Two-column grid ── */
  .builder-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
  }

  /* ── Card ── */
  .card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 2rem;
    position: relative; overflow: hidden;
    transition: border-color .35s;
  }
  .card:hover { border-color: var(--border-lit); }
  .card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    transform: scaleX(0); transform-origin: left; transition: transform .4s ease;
  }
  .card:hover::before { transform: scaleX(1); }
  .card.c1::before { background: var(--g1); }
  .card.c2::before { background: var(--g2); }

  /* Card title */
  .card-title {
    font-family: 'Sora', sans-serif;
    font-size: 1.1rem; font-weight: 600;
    display: flex; align-items: center; gap: .6rem;
    margin-bottom: 1.75rem; padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
  }
  .card-title-icon {
    width: 32px; height: 32px; border-radius: .6rem;
    background: rgba(99,102,241,.15);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .card.c2 .card-title-icon { background: rgba(236,72,153,.15); }

  /* Section divider inside form */
  .form-section {
    margin: 1.75rem 0 1rem;
    display: flex; align-items: center; gap: .75rem;
  }
  .form-section-label {
    font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
    color: var(--primary); white-space: nowrap;
  }
  .form-section-line { flex: 1; height: 1px; background: var(--border); }

  /* Form elements */
  .form-group { margin-bottom: 1.1rem; }
  .form-group label {
    display: block; margin-bottom: .45rem;
    font-size: .83rem; font-weight: 500; color: var(--text);
  }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

  input[type="text"],
  input[type="email"],
  input[type="number"],
  textarea,
  select {
    width: 100%;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: .75rem;
    padding: .7rem 1rem;
    font-size: .88rem;
    font-family: 'Inter', sans-serif;
    color: var(--text);
    transition: var(--transition);
  }
  input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    background: var(--bg-lift);
  }
  input::placeholder, textarea::placeholder { color: var(--muted); opacity: .8; }
  textarea { resize: vertical; min-height: 90px; }
  select { cursor: pointer; }
  select option { background: var(--bg-card); }

  /* File upload */
  .file-input-wrapper { position: relative; width: 100%; }
  .file-input-wrapper input[type="file"] { position: absolute; left: -9999px; }
  .file-input-label {
    display: flex; align-items: center; gap: .75rem;
    padding: .7rem 1rem;
    background: var(--bg-input); border: 1px dashed var(--border);
    border-radius: .75rem; cursor: pointer;
    font-size: .85rem; color: var(--muted);
    transition: var(--transition);
  }
  .file-input-label:hover { border-color: var(--primary); background: var(--bg-lift); color: var(--text); }
  .file-input-label svg { width: 18px; height: 18px; stroke: var(--primary); flex-shrink: 0; }

  /* Color & range rows */
  .inline-row {
    display: flex; align-items: center; gap: .85rem;
  }
  .inline-row input[type="color"] {
    width: 44px; height: 44px; flex-shrink: 0;
    border-radius: .6rem; border: 1px solid var(--border);
    cursor: pointer; background: var(--bg-input); padding: 3px;
  }
  .inline-row span { font-size: .82rem; color: var(--muted); }
  .inline-row input[type="number"] { width: 80px; flex-shrink: 0; }

  /* Checkbox */
  .checkbox-label {
    display: flex; align-items: center; gap: .7rem;
    cursor: pointer; font-size: .88rem;
  }
  .checkbox-label input[type="checkbox"] {
    width: 18px; height: 18px;
    accent-color: var(--primary); cursor: pointer;
  }

  /* Button group */
  .btn-group { display: flex; gap: .85rem; flex-wrap: wrap; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }

  .btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .75rem 1.5rem;
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .88rem;
    border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
    transition: transform .25s, box-shadow .25s, filter .25s;
  }
  .btn-primary { background: var(--g1); color: #fff; box-shadow: 0 4px 18px rgba(99,102,241,.35); }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(99,102,241,.5); filter: brightness(1.08); }
  .btn-secondary { background: var(--g2); color: #fff; box-shadow: 0 4px 18px rgba(236,72,153,.3); }
  .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(236,72,153,.45); filter: brightness(1.08); }

  /* ── Preview pane ── */
  .preview-pane { position: sticky; top: 100px; }

  .resume-preview {
    background: white; border-radius: .75rem;
    padding: 2.5rem; min-height: 500px;
    font-family: 'Crimson Pro', serif;
    font-size: 14px; line-height: 1.7; color: #222;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
  }
  .resume-preview.customized {
    font-family: var(--font-family, 'Crimson Pro'), serif;
    font-size: calc(14px * var(--font-scale, 1));
    color: var(--text-color, #222);
    background: var(--bg-color, white);
  }
  .resume-preview h1 {
    font-size: calc(2rem * var(--font-scale, 1));
    margin-bottom: 14px;
    color: var(--accent-color, #667eea);
    font-weight: 700;
  }
  .resume-preview h3 {
    font-size: calc(1.2rem * var(--font-scale, 1));
    margin-top: 20px; margin-bottom: 10px;
    color: var(--accent-color, #667eea);
    padding-bottom: 6px;
    border-bottom: var(--divider-thickness, 2px) var(--divider-style, solid) var(--accent-color, #667eea);
    font-weight: 600;
  }
  .resume-preview p { margin-bottom: 6px; color: var(--text-color, #222); }
  .resume-preview strong { color: var(--accent-color, #667eea); font-weight: 600; }

  #previewProfilePic { text-align: center; margin-bottom: 18px; }
  #previewProfilePic img {
    display: inline-block; object-fit: cover;
    border: 3px solid var(--accent-color, #667eea);
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
  }
  .frame-circle  { border-radius: 50% !important; }
  .frame-rounded { border-radius: 14px !important; }
  .frame-square  { border-radius: 4px !important; }

  /* Notification */
  .notification {
    position: fixed; top: 96px; right: 20px;
    background: var(--bg-card); border: 1px solid var(--border);
    padding: .9rem 1.4rem; border-radius: .85rem;
    box-shadow: 0 10px 40px rgba(0,0,0,.3);
    display: none; align-items: center; gap: .75rem;
    z-index: 1000; animation: slideIn .3s ease;
    color: var(--text); font-size: .88rem;
  }
  @keyframes slideIn { from{transform:translateX(400px);opacity:0} to{transform:translateX(0);opacity:1} }
  .notification.show { display: flex; }
  .notification.success { border-left: 3px solid var(--success); }
  .notification.error   { border-left: 3px solid #ef4444; }

  /* Footer */
  footer { border-top: 1px solid var(--border); padding: 2rem; margin-top: 1rem; }
  .footer-inner { max-width: 1600px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
  .footer-brand { font-family:'Sora',sans-serif; font-weight:700; font-size:.95rem; background:var(--g1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .footer-copy  { font-size:.8rem; color:var(--muted); }

  /* Animations */
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .hero          { animation: fadeUp .5s ease both; }
  .builder-grid  { animation: fadeUp .5s .1s ease both; }

  /* Loading */
  .loading { opacity:.6; pointer-events:none; }
  @keyframes spin { to{transform:rotate(360deg)} }

  /* Responsive */
  @media (max-width: 1100px) {
    .builder-grid { grid-template-columns: 1fr; }
    .preview-pane { position: static; }
  }
  @media (max-width: 640px) {
    .wrap { padding: 1.5rem 1rem 3rem; }
    .hero { padding: 2rem 1.5rem; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<div class="wrap">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-label"><span class="dot"></span> Resume Builder</div>
    <h1>Let's build your resume, <em><?= htmlspecialchars($username) ?></em> &#x1F4C4;</h1>
    <p>Fill in your details on the left and watch your resume come together live on the right.</p>
  </div>

  <!-- Builder -->
  <div class="builder-grid">

    <!-- ── Form ── -->
    <div class="card c1">
      <div class="card-title">
        <div class="card-title-icon">
          <svg width="16" height="16" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
        Build Your Resume
      </div>

      <form method="post" enctype="multipart/form-data" id="resumeForm">

        <!-- Personal Info -->
        <div class="form-section">
          <span class="form-section-label">Personal Info</span>
          <div class="form-section-line"></div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="John Doe" required value="<?= htmlspecialchars($name) ?>">
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="City, Country" value="<?= htmlspecialchars($location ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($email) ?>">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="+1 (555) 123-4567" value="<?= htmlspecialchars($phone) ?>">
          </div>
        </div>

        <div class="form-group">
          <label>LinkedIn URL</label>
          <input type="text" name="linkedin" placeholder="https://linkedin.com/in/johndoe" value="<?= htmlspecialchars($linkedin ?? '') ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Profile Picture</label>
            <div class="file-input-wrapper">
              <input type="file" name="profile_pic" id="profilePicInput" accept="image/*">
              <label for="profilePicInput" class="file-input-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span id="fileLabel">Choose image</span>
              </label>
            </div>
          </div>
          <div class="form-group">
            <label>Frame Shape</label>
            <select name="profile_frame" id="profileFrameSelect">
              <option value="circle"  <?= ($profile_frame ?? 'circle') == 'circle'  ? 'selected' : '' ?>>Circle</option>
              <option value="rounded" <?= ($profile_frame ?? '') == 'rounded' ? 'selected' : '' ?>>Rounded Square</option>
              <option value="square"  <?= ($profile_frame ?? '') == 'square'  ? 'selected' : '' ?>>Square</option>
            </select>
          </div>
        </div>

        <!-- Content -->
        <div class="form-section">
          <span class="form-section-label">Content</span>
          <div class="form-section-line"></div>
        </div>

        <div class="form-group">
          <label>Career Objective</label>
          <textarea name="career_objective" rows="3" placeholder="A brief statement about your goals..."><?= htmlspecialchars($career_objective ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label>Education</label>
          <textarea name="education" rows="3" placeholder="BSc Computer Science&#10;University Name, 2020–2024"><?= htmlspecialchars($education) ?></textarea>
        </div>

        <div class="form-group">
          <label>Skills <span style="color:var(--muted);font-weight:400">(comma separated)</span></label>
          <textarea name="skills" rows="2" placeholder="JavaScript, Python, React, Node.js"><?= htmlspecialchars($skills) ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Strengths</label>
            <textarea name="strengths" rows="3" placeholder="Problem-solving, Leadership..."><?= htmlspecialchars($strengths ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Technical Familiarity</label>
            <textarea name="technical" rows="3" placeholder="Languages, Frameworks, Tools..."><?= htmlspecialchars($technical ?? '') ?></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Interests</label>
            <textarea name="interests" rows="3" placeholder="Open source, ML, Web Dev..."><?= htmlspecialchars($interests ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label>Languages Known</label>
            <textarea name="languages" rows="3" placeholder="English (Fluent), Spanish (B2)..."><?= htmlspecialchars($languages ?? '') ?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label>Internship &amp; Experience</label>
          <textarea name="experience" rows="4" placeholder="Software Intern – Company Name&#10;June 2023 – Aug 2023&#10;• Built X using Y"><?= htmlspecialchars($experience) ?></textarea>
        </div>

        <!-- Styling -->
        <div class="form-section">
          <span class="form-section-label">Styling</span>
          <div class="form-section-line"></div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Accent Color</label>
            <div class="inline-row">
              <input type="color" name="accent_color" value="<?= htmlspecialchars($accent_color ?? '#667eea') ?>">
              <span>Headings &amp; highlights</span>
            </div>
          </div>
          <div class="form-group">
            <label>Text Color</label>
            <div class="inline-row">
              <input type="color" name="text_color" value="<?= htmlspecialchars($text_color ?? '#222222') ?>">
              <span>Body text</span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Background Color</label>
            <div class="inline-row">
              <input type="color" name="bg_color" value="<?= htmlspecialchars($bg_color ?? '#ffffff') ?>">
              <span>Page background</span>
            </div>
          </div>
          <div class="form-group">
            <label>Font Family</label>
            <select name="font_family">
              <option value="Crimson Pro"  <?= ($font_family ?? 'Crimson Pro') == 'Crimson Pro'  ? 'selected' : '' ?>>Crimson Pro (Serif)</option>
              <option value="Inter"        <?= ($font_family ?? '') == 'Inter'        ? 'selected' : '' ?>>Inter (Sans-serif)</option>
              <option value="Poppins"      <?= ($font_family ?? '') == 'Poppins'      ? 'selected' : '' ?>>Poppins (Sans-serif)</option>
              <option value="Merriweather" <?= ($font_family ?? '') == 'Merriweather' ? 'selected' : '' ?>>Merriweather (Serif)</option>
              <option value="Georgia"      <?= ($font_family ?? '') == 'Georgia'      ? 'selected' : '' ?>>Georgia (Serif)</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Font Scale</label>
            <div class="inline-row">
              <input type="number" name="font_scale" step="0.05" min="0.8" max="1.5" value="<?= htmlspecialchars($font_scale ?? 1) ?>">
              <span>0.8 – 1.5</span>
            </div>
          </div>
          <div class="form-group">
            <label>Divider Style</label>
            <select name="divider_style">
              <option value="solid"  <?= ($divider_style ?? 'solid') == 'solid'  ? 'selected' : '' ?>>Solid</option>
              <option value="dashed" <?= ($divider_style ?? '') == 'dashed' ? 'selected' : '' ?>>Dashed</option>
              <option value="dotted" <?= ($divider_style ?? '') == 'dotted' ? 'selected' : '' ?>>Dotted</option>
              <option value="double" <?= ($divider_style ?? '') == 'double' ? 'selected' : '' ?>>Double</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Divider Thickness</label>
            <div class="inline-row">
              <input type="number" name="divider_thickness" min="1" max="10" value="<?= htmlspecialchars($divider_thickness ?? 2) ?>">
              <span>1 – 10px</span>
            </div>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:.2rem">
            <label class="checkbox-label">
              <input type="checkbox" name="bw_theme" value="1" <?= !empty($bw_theme) ? 'checked' : '' ?>>
              <span>Black &amp; White Theme</span>
            </label>
          </div>
        </div>

        <div class="btn-group">
          <button type="button" class="btn btn-secondary" id="refreshAiBtn">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path d="M23 4v6h-6"/><path d="M1 20v-6h6"/>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            Refresh with AI
          </button>
          <button type="submit" name="download_pdf" class="btn btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download PDF
          </button>
        </div>

      </form>
    </div>

    <!-- ── Preview ── -->
    <div class="preview-pane">
      <div class="card c2">
        <div class="card-title">
          <div class="card-title-icon">
            <svg width="16" height="16" fill="none" stroke="#f472b6" stroke-width="2" viewBox="0 0 24 24">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
          Live Preview
        </div>

        <div class="resume-preview customized" id="resumePreview" style="
          --accent-color: <?= $accent_color ?? '#667eea' ?>;
          --text-color:   <?= $text_color   ?? '#222' ?>;
          --bg-color:     <?= $bg_color     ?? '#fff' ?>;
          --font-family:  <?= $font_family  ?? 'Crimson Pro' ?>;
          --font-scale:   <?= $font_scale   ?? 1 ?>;
          --divider-style: <?= $divider_style ?? 'solid' ?>;
          --divider-thickness: <?= $divider_thickness ?? 2 ?>px;
        ">
          <div id="previewProfilePic">
            <?php if (!empty($profile_img)): ?>
              <img src="<?= htmlspecialchars($profile_img) ?>" class="frame-<?= $profile_frame ?? 'circle' ?>" style="width:90px;height:90px;">
            <?php endif; ?>
          </div>

          <h1 id="previewName"><?= htmlspecialchars($name ?: 'Your Name') ?></h1>
          <?php if (!empty($location)):  ?><p><strong>Location:</strong> <span id="previewLocation"><?= htmlspecialchars($location) ?></span></p><?php endif; ?>
          <p><strong>Email:</strong> <span id="previewEmail"><?= htmlspecialchars($email ?: 'your@email.com') ?></span></p>
          <?php if (!empty($phone)):    ?><p><strong>Phone:</strong> <span id="previewPhone"><?= htmlspecialchars($phone) ?></span></p><?php endif; ?>
          <?php if (!empty($linkedin)): ?><p><strong>LinkedIn:</strong> <span id="previewLinkedin"><?= htmlspecialchars($linkedin) ?></span></p><?php endif; ?>

          <?php if (!empty($career_objective)): ?><h3>Career Objective</h3><p id="previewIntro"><?= nl2br(htmlspecialchars($career_objective)) ?></p><?php endif; ?>
          <?php if (!empty($education)):        ?><h3>Education</h3><div id="previewEducation"><?= nl2br(htmlspecialchars($education)) ?></div><?php endif; ?>
          <?php if (!empty($skills)):           ?><h3>Skills</h3><div id="previewSkills"><?= nl2br(htmlspecialchars($skills)) ?></div><?php endif; ?>
          <?php if (!empty($strengths)):        ?><h3>Strengths</h3><div id="previewStrengths"><?= nl2br(htmlspecialchars($strengths)) ?></div><?php endif; ?>
          <?php if (!empty($technical)):        ?><h3>Technical Familiarity</h3><div id="previewTechnical"><?= nl2br(htmlspecialchars($technical)) ?></div><?php endif; ?>
          <?php if (!empty($interests)):        ?><h3>Interests</h3><div id="previewInterests"><?= nl2br(htmlspecialchars($interests)) ?></div><?php endif; ?>
          <?php if (!empty($languages)):        ?><h3>Languages Known</h3><div id="previewLanguages"><?= nl2br(htmlspecialchars($languages)) ?></div><?php endif; ?>
          <?php if (!empty($experience)):       ?><h3>Internship &amp; Experience</h3><div id="previewExperience"><?= nl2br(htmlspecialchars($experience)) ?></div><?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Notification toast -->
<div class="notification" id="notification">
  <span id="notificationText"></span>
</div>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">Skillsync AI</div>
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function () {

  /* ── Live field → preview mappings ── */
  const mappings = {
    name:             "previewName",
    location:         "previewLocation",
    email:            "previewEmail",
    phone:            "previewPhone",
    linkedin:         "previewLinkedin",
    career_objective: "previewIntro",
    skills:           "previewSkills",
    education:        "previewEducation",
    strengths:        "previewStrengths",
    technical:        "previewTechnical",
    interests:        "previewInterests",
    languages:        "previewLanguages",
    experience:       "previewExperience"
  };

  const resumePreview = document.getElementById("resumePreview");

  Object.keys(mappings).forEach(field => {
    const input   = document.querySelector(`[name="${field}"]`);
    const preview = document.getElementById(mappings[field]);
    if (!input || !preview) return;

    const update = () => {
      if (field === "skills") {
        const arr = input.value.split(",").map(s => s.trim()).filter(Boolean);
        preview.innerHTML = arr.length
          ? `<ul style="margin-left:18px">${arr.map(s => `<li>${s}</li>`).join("")}</ul>`
          : "";
      } else {
        preview.innerHTML = input.value.replace(/\n/g, "<br>");
      }
    };
    input.addEventListener("input", update);
    update();
  });

  /* ── Profile picture ── */
  const profilePicInput    = document.getElementById("profilePicInput");
  const fileLabel          = document.getElementById("fileLabel");
  const previewProfilePic  = document.getElementById("previewProfilePic");

  profilePicInput?.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (!file) return;
    fileLabel.textContent = file.name;
    const reader = new FileReader();
    reader.onload = e2 => {
      const frame = document.querySelector('[name="profile_frame"]').value;
      previewProfilePic.innerHTML = `<img src="${e2.target.result}" class="frame-${frame}" style="width:90px;height:90px;">`;
    };
    reader.readAsDataURL(file);
  });

  document.getElementById("profileFrameSelect")?.addEventListener("change", e => {
    const img = previewProfilePic.querySelector("img");
    if (img) img.className = `frame-${e.target.value}`;
  });

  /* ── Style controls → CSS vars ── */
  const cssVarMap = {
    accent_color:      "--accent-color",
    text_color:        "--text-color",
    bg_color:          "--bg-color",
    font_family:       "--font-family",
    font_scale:        "--font-scale",
    divider_style:     "--divider-style",
    divider_thickness: "--divider-thickness"
  };

  Object.keys(cssVarMap).forEach(name => {
    const el = document.querySelector(`[name="${name}"]`);
    if (!el) return;
    const evt = el.tagName === "SELECT" ? "change" : "input";
    el.addEventListener(evt, e => {
      const val = name === "divider_thickness" ? e.target.value + "px" : e.target.value;
      resumePreview.style.setProperty(cssVarMap[name], val);
    });
  });

  /* ── Toast ── */
  function toast(msg, type = "success") {
    const el   = document.getElementById("notification");
    const text = document.getElementById("notificationText");
    text.textContent = msg;
    el.className = `notification ${type} show`;
    setTimeout(() => el.classList.remove("show"), 4000);
  }

  /* ── AI refresh ── */
  const refreshBtn = document.getElementById("refreshAiBtn");
  const form       = document.getElementById("resumeForm");

  refreshBtn?.addEventListener("click", function () {
    const fd = new FormData(form);
    fd.append("refresh_ai", 1);

    refreshBtn.classList.add("loading");
    refreshBtn.querySelector("svg").style.animation = "spin .6s linear infinite";

    fetch("resume.php", { method: "POST", body: fd })
      .then(r => { if (!r.ok) throw new Error(); return r.json(); })
      .then(data => {
        if (data.career_objective) {
          const inp  = document.querySelector("[name='career_objective']");
          const prev = document.getElementById("previewIntro");
          inp.value  = data.career_objective;
          prev.innerHTML = data.career_objective.replace(/\n/g, "<br>");
          toast("Career objective updated with AI!", "success");
        } else if (data.error) {
          toast(data.error, "error");
        }
      })
      .catch(() => toast("AI refresh failed. Please try again.", "error"))
      .finally(() => {
        refreshBtn.classList.remove("loading");
        refreshBtn.querySelector("svg").style.animation = "";
      });
  });

  /* ── Form validation ── */
  form.addEventListener("submit", function (e) {
    const name  = document.querySelector('[name="name"]').value.trim();
    const email = document.querySelector('[name="email"]').value.trim();
    if (!name || !email) {
      e.preventDefault();
      toast("Name and Email are required.", "error");
    }
  });
});
</script>

</body>
</html>