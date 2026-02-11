<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the main resume handling logic
require_once __DIR__ . "/resume.php";

// Get username for greeting
$username = explode('@', $email)[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume Builder – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
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
  box-sizing: border-box;
  margin: 0;
  padding: 0;
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

/* Welcome Section */
.resume-welcome {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  padding: 3rem;
  margin: 0 2rem 3rem;
  position: relative;
  overflow: hidden;
}

.resume-welcome::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--gradient-1);
}

.resume-welcome h1 {
  font-family: 'Sora', sans-serif;
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  font-weight: 700;
  margin-bottom: 1rem;
  letter-spacing: -0.02em;
}

.resume-welcome h1 span {
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.resume-welcome p {
  font-size: 1.1rem;
  color: var(--text-gray);
}

/* Dashboard Grid */
.dashboard-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  padding: 0 2rem 2rem;
  max-width: 1600px;
  margin: 0 auto;
}

@media(max-width: 1100px){
  .dashboard-content { 
    grid-template-columns: 1fr;
  }
}

/* Card Styles */
.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  padding: 2.5em;
  border-radius: 1.5rem;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--gradient-1);
  opacity: 0;
  transition: var(--transition);
}

.card:hover::before {
  opacity: 1;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

.card h3 {
  font-family: 'Sora', sans-serif;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 24px;
  color: var(--text-light);
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--text-light);
  font-size: 0.95rem;
}

.card input[type="text"],
.card input[type="email"],
.card textarea,
.card select {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  padding: 12px 16px;
  font-size: 15px;
  font-family: 'Inter', sans-serif;
  transition: var(--transition);
  background: var(--bg-dark);
  color: var(--text-light);
}

.card input:focus,
.card textarea:focus,
.card select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  outline: none;
}

.card textarea {
  resize: vertical;
  min-height: 100px;
}

.card textarea::placeholder,
.card input::placeholder {
  color: var(--text-gray);
  opacity: 0.7;
}

/* File Input Styling */
.file-input-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
}

.file-input-wrapper input[type="file"] {
  position: absolute;
  left: -9999px;
}

.file-input-label {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--bg-dark);
  border: 1px dashed var(--border);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: var(--transition);
  font-size: 14px;
  color: var(--text-gray);
}

.file-input-label:hover {
  border-color: var(--primary);
  background: rgba(99, 102, 241, 0.05);
}

.file-input-label svg {
  width: 20px;
  height: 20px;
  stroke: var(--primary);
}

/* Color Input */
.color-input {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-input input[type="color"] {
  width: 50px;
  height: 50px;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  cursor: pointer;
  transition: var(--transition);
  background: var(--bg-dark);
}

.color-input input[type="color"]:hover {
  transform: scale(1.05);
  box-shadow: var(--shadow-sm);
}

.color-input span {
  color: var(--text-gray);
  font-size: 14px;
}

/* Range Input */
.range-input {
  display: flex;
  align-items: center;
  gap: 12px;
}

.range-input input[type="number"] {
  width: 80px;
}

.range-input span {
  color: var(--text-gray);
  font-size: 14px;
}

/* Checkbox Styling */
.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.checkbox-label input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: var(--primary);
}

/* Button Styles */
.button-group {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 24px;
}

.card button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: var(--gradient-1);
  color: white;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
}

.btn-secondary {
  background: var(--gradient-2);
  color: white;
  box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(236, 72, 153, 0.5);
}

/* Loading State */
.loading {
  opacity: 0.6;
  pointer-events: none;
  position: relative;
}

.loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  margin: -10px 0 0 -10px;
  border: 2px solid white;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Resume Preview */
.resume-preview {
  min-height: 400px;
  padding: 40px;
  background: white;
  border-radius: 0.75rem;
  font-family: 'Crimson Pro', serif;
  font-size: 14px;
  line-height: 1.7;
  color: #222;
  border: 1px solid #e5e7eb;
}

.resume-preview.customized {
  font-family: var(--font-family, 'Crimson Pro'), serif;
  font-size: calc(14px * var(--font-scale, 1));
  color: var(--text-color, #222);
  background: var(--bg-color, white);
}

.resume-preview h1 {
  font-size: calc(2rem * var(--font-scale, 1));
  margin-bottom: 16px;
  color: var(--accent-color, #667eea);
  font-weight: 700;
}

.resume-preview h3 {
  font-size: calc(1.3rem * var(--font-scale, 1));
  margin-top: 24px;
  margin-bottom: 12px;
  color: var(--accent-color, #667eea);
  padding-bottom: 8px;
  border-bottom: var(--divider-thickness, 2px) var(--divider-style, solid) var(--accent-color, #667eea);
  font-weight: 600;
}

.resume-preview p {
  margin-bottom: 8px;
  color: var(--text-color, #222);
}

.resume-preview strong {
  color: var(--accent-color, #667eea);
  font-weight: 600;
}

#previewProfilePic {
  text-align: center;
  margin-bottom: 20px;
}

#previewProfilePic img {
  display: inline-block;
  object-fit: cover;
  border: 4px solid var(--accent-color, #667eea);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Profile Frame Styles */
.frame-circle {
  border-radius: 50% !important;
}

.frame-rounded {
  border-radius: 16px !important;
}

.frame-square {
  border-radius: 4px !important;
}

/* Section Headers */
.section-header {
  background: rgba(99, 102, 241, 0.05);
  padding: 16px 20px;
  border-radius: 0.75rem;
  margin: 32px 0 20px 0;
  border-left: 4px solid var(--primary);
}

.section-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: var(--text-light);
}

/* Notification */
.notification {
  position: fixed;
  top: 100px;
  right: 20px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  padding: 16px 24px;
  border-radius: 0.75rem;
  box-shadow: var(--shadow-lg);
  display: none;
  align-items: center;
  gap: 12px;
  z-index: 1000;
  animation: slideIn 0.3s ease;
  color: var(--text-light);
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.notification.show {
  display: flex;
}

.notification.success {
  border-left: 4px solid var(--success);
}

.notification.error {
  border-left: 4px solid #ef4444;
}

/* Footer */
footer {
  background: var(--bg-card);
  border-top: 1px solid var(--border);
  padding: 3rem 2rem;
  margin-top: 5rem;
}

.footer-content {
  max-width: 1600px;
  margin: 0 auto;
  text-align: center;
  color: var(--text-gray);
}

/* Responsive */
@media (max-width: 768px) {
  .resume-welcome {
    margin: 0 1rem 2rem;
    padding: 2rem;
  }
  
  .dashboard-content {
    padding: 0 1rem 2rem;
  }
}

/* Animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card {
  animation: fadeInUp 0.6s ease-out;
}
</style>
</head>
<body>
<div class="bg-pattern"></div>

<!-- Include Navbar -->
<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<section class="resume-welcome">
  <h1>Hi <span><?php echo htmlspecialchars($username); ?></span>, ready to build your resume?</h1>
  <p>Fill out your details to create a professional, standout resume with AI-powered suggestions.</p>
</section>

<main class="dashboard-content">
  <!-- Build Resume Form -->
  <div class="card">
    <h3>📝 Build Your Resume</h3>
    <form method="post" enctype="multipart/form-data" id="resumeForm">
      
      <!-- Personal Information -->
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" placeholder="John Doe" required value="<?php echo htmlspecialchars($name); ?>">
      </div>
      
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" placeholder="City, Country" value="<?php echo htmlspecialchars($location ?? ''); ?>">
      </div>
      
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" placeholder="john.doe@example.com" required value="<?php echo htmlspecialchars($email); ?>">
      </div>
      
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="+1 (555) 123-4567" value="<?php echo htmlspecialchars($phone); ?>">
      </div>
      
      <div class="form-group">
        <label>LinkedIn URL</label>
        <input type="text" name="linkedin" placeholder="https://linkedin.com/in/johndoe" value="<?php echo htmlspecialchars($linkedin ?? ''); ?>">
      </div>

      <!-- Profile Picture -->
      <div class="form-group">
        <label>Upload Profile Picture</label>
        <div class="file-input-wrapper">
          <input type="file" name="profile_pic" id="profilePicInput" accept="image/*">
          <label for="profilePicInput" class="file-input-label">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span id="fileLabel">Choose file or drag here</span>
          </label>
        </div>
      </div>
      
      <div class="form-group">
        <label>Profile Frame Shape</label>
        <select name="profile_frame" id="profileFrameSelect">
          <option value="circle" <?php if(($profile_frame ?? 'circle')=='circle') echo 'selected'; ?>>Circle</option>
          <option value="rounded" <?php if(($profile_frame ?? '')=='rounded') echo 'selected'; ?>>Rounded Square</option>
          <option value="square" <?php if(($profile_frame ?? '')=='square') echo 'selected'; ?>>Square</option>
        </select>
      </div>

      <!-- Resume Content -->
      <div class="section-header">
        <h3>Resume Content</h3>
      </div>

      <div class="form-group">
        <label>Career Objective</label>
        <textarea name="career_objective" rows="3" placeholder="A brief statement about your career goals and what you bring to the role..."><?php echo htmlspecialchars($career_objective ?? ''); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Education</label>
        <textarea name="education" rows="3" placeholder="Bachelor of Science in Computer Science&#10;University Name, 2020-2024"><?php echo htmlspecialchars($education); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Skills (comma separated)</label>
        <textarea name="skills" rows="3" placeholder="JavaScript, Python, React, Node.js, SQL"><?php echo htmlspecialchars($skills); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Strengths</label>
        <textarea name="strengths" rows="3" placeholder="Problem-solving, Team collaboration, Leadership..."><?php echo htmlspecialchars($strengths ?? ''); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Technical Familiarity</label>
        <textarea name="technical" rows="3" placeholder="Programming Languages, Frameworks, Tools..."><?php echo htmlspecialchars($technical ?? ''); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Interests</label>
        <textarea name="interests" rows="3" placeholder="Open source contribution, Machine Learning, Web Development..."><?php echo htmlspecialchars($interests ?? ''); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Languages Known</label>
        <textarea name="languages" rows="3" placeholder="English (Fluent), Spanish (Intermediate)..."><?php echo htmlspecialchars($languages ?? ''); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Internship & Experience</label>
        <textarea name="experience" rows="3" placeholder="Software Engineering Intern&#10;Company Name, June 2023 - August 2023&#10;• Developed features using React and Node.js"><?php echo htmlspecialchars($experience); ?></textarea>
      </div>

      <!-- Styling Options -->
      <div class="section-header">
        <h3>Resume Styling</h3>
      </div>

      <div class="form-group color-input">
        <label>Accent Color</label>
        <input type="color" name="accent_color" value="<?php echo htmlspecialchars($accent_color ?? '#667eea'); ?>">
        <span>Headings & highlights</span>
      </div>
      
      <div class="form-group color-input">
        <label>Text Color</label>
        <input type="color" name="text_color" value="<?php echo htmlspecialchars($text_color ?? '#222222'); ?>">
        <span>Body text</span>
      </div>
      
      <div class="form-group color-input">
        <label>Background Color</label>
        <input type="color" name="bg_color" value="<?php echo htmlspecialchars($bg_color ?? '#ffffff'); ?>">
        <span>Page background</span>
      </div>

      <div class="form-group">
        <label>Font Family</label>
        <select name="font_family">
          <option value="Crimson Pro" <?php if(($font_family ?? 'Crimson Pro')=="Crimson Pro") echo "selected"; ?>>Crimson Pro (Serif)</option>
          <option value="Inter" <?php if(($font_family ?? '')=="Inter") echo "selected"; ?>>Inter (Sans-serif)</option>
          <option value="Poppins" <?php if(($font_family ?? '')=="Poppins") echo "selected"; ?>>Poppins (Sans-serif)</option>
          <option value="Merriweather" <?php if(($font_family ?? '')=="Merriweather") echo "selected"; ?>>Merriweather (Serif)</option>
          <option value="Georgia" <?php if(($font_family ?? '')=="Georgia") echo "selected"; ?>>Georgia (Serif)</option>
        </select>
      </div>

      <div class="form-group range-input">
        <label>Font Size Scale</label>
        <input type="number" name="font_scale" step="0.05" min="0.8" max="1.5" value="<?php echo htmlspecialchars($font_scale ?? 1); ?>">
        <span>0.8 - 1.5</span>
      </div>

      <div class="form-group">
        <label>Divider Style</label>
        <select name="divider_style">
          <option value="solid" <?php if(($divider_style ?? 'solid')=='solid') echo 'selected'; ?>>Solid</option>
          <option value="dashed" <?php if(($divider_style ?? '')=='dashed') echo 'selected'; ?>>Dashed</option>
          <option value="dotted" <?php if(($divider_style ?? '')=='dotted') echo 'selected'; ?>>Dotted</option>
          <option value="double" <?php if(($divider_style ?? '')=='double') echo 'selected'; ?>>Double</option>
        </select>
      </div>

      <div class="form-group range-input">
        <label>Divider Thickness (px)</label>
        <input type="number" name="divider_thickness" min="1" max="10" value="<?php echo htmlspecialchars($divider_thickness ?? 2); ?>">
        <span>1 - 10px</span>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="bw_theme" value="1" <?php if(!empty($bw_theme)) echo "checked"; ?>>
          <span>Black & White Theme</span>
        </label>
      </div>

      <div class="button-group">
        <button type="button" name="refresh_ai" class="btn-secondary" id="refreshAiBtn">
          ✨ Refresh with AI
        </button>
        <button type="submit" name="download_pdf" class="btn-primary">
          📄 Download PDF
        </button>
      </div>
    </form>
  </div>

  <!-- Live Preview -->
  <div class="card">
    <h3>👁️ Live Preview</h3>
    <div class="resume-preview customized" id="resumePreview" style="
      --accent-color: <?php echo $accent_color ?? '#667eea'; ?>;
      --text-color: <?php echo $text_color ?? '#222'; ?>;
      --bg-color: <?php echo $bg_color ?? '#fff'; ?>;
      --font-family: <?php echo $font_family ?? 'Crimson Pro'; ?>;
      --font-scale: <?php echo $font_scale ?? 1; ?>;
      --divider-style: <?php echo $divider_style ?? 'solid'; ?>;
      --divider-thickness: <?php echo $divider_thickness ?? 2; ?>px;
    ">
      <div id="previewProfilePic">
        <?php if(!empty($profile_img)): ?>
          <img src="<?php echo htmlspecialchars($profile_img); ?>" class="frame-<?php echo $profile_frame ?? 'circle'; ?>" style="width:96px;height:96px;">
        <?php endif; ?>
      </div>

      <h1 id="previewName"><?php echo htmlspecialchars($name ?: 'Your Name'); ?></h1>
      <?php if(!empty($location)): ?>
      <p><strong>Location:</strong> <span id="previewLocation"><?php echo htmlspecialchars($location); ?></span></p>
      <?php endif; ?>
      <p><strong>Email:</strong> <span id="previewEmail"><?php echo htmlspecialchars($email ?: 'your@email.com'); ?></span></p>
      <?php if(!empty($phone)): ?>
      <p><strong>Phone:</strong> <span id="previewPhone"><?php echo htmlspecialchars($phone); ?></span></p>
      <?php endif; ?>
      <?php if(!empty($linkedin)): ?>
      <p><strong>LinkedIn:</strong> <span id="previewLinkedin"><?php echo htmlspecialchars($linkedin); ?></span></p>
      <?php endif; ?>

      <?php if(!empty($career_objective)): ?>
      <h3>Career Objective</h3>
      <p id="previewIntro"><?php echo nl2br(htmlspecialchars($career_objective)); ?></p>
      <?php endif; ?>

      <?php if(!empty($education)): ?>
      <h3>Education</h3>
      <div id="previewEducation"><?php echo nl2br(htmlspecialchars($education)); ?></div>
      <?php endif; ?>

      <?php if(!empty($skills)): ?>
      <h3>Skills</h3>
      <div id="previewSkills"><?php echo nl2br(htmlspecialchars($skills)); ?></div>
      <?php endif; ?>

      <?php if(!empty($strengths)): ?>
      <h3>Strengths</h3>
      <div id="previewStrengths"><?php echo nl2br(htmlspecialchars($strengths)); ?></div>
      <?php endif; ?>

      <?php if(!empty($technical)): ?>
      <h3>Technical Familiarity</h3>
      <div id="previewTechnical"><?php echo nl2br(htmlspecialchars($technical)); ?></div>
      <?php endif; ?>

      <?php if(!empty($interests)): ?>
      <h3>Interests</h3>
      <div id="previewInterests"><?php echo nl2br(htmlspecialchars($interests)); ?></div>
      <?php endif; ?>

      <?php if(!empty($languages)): ?>
      <h3>Languages Known</h3>
      <div id="previewLanguages"><?php echo nl2br(htmlspecialchars($languages)); ?></div>
      <?php endif; ?>

      <?php if(!empty($experience)): ?>
      <h3>Internship & Experience</h3>
      <div id="previewExperience"><?php echo nl2br(htmlspecialchars($experience)); ?></div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Notification -->
<div class="notification" id="notification">
  <span id="notificationText"></span>
</div>

<!-- Footer -->
<footer>
  <div class="footer-content">
    <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
  </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const mappings = {
    name: "previewName",
    location: "previewLocation",
    email: "previewEmail",
    phone: "previewPhone",
    linkedin: "previewLinkedin",
    career_objective: "previewIntro",
    skills: "previewSkills",
    education: "previewEducation",
    strengths: "previewStrengths",
    technical: "previewTechnical",
    interests: "previewInterests",
    languages: "previewLanguages",
    experience: "previewExperience"
  };

  const resumePreview = document.getElementById("resumePreview");

  Object.keys(mappings).forEach(field => {
    const input = document.querySelector(`[name="${field}"]`);
    const preview = document.getElementById(mappings[field]);
    
    if(input && preview) {
      const updatePreview = () => {
        if(field === "skills") {
          const skillsArr = input.value.split(",").map(s => s.trim()).filter(Boolean);
          preview.innerHTML = skillsArr.length 
            ? `<ul style="margin-left: 20px;">${skillsArr.map(s => `<li>${s}</li>`).join('')}</ul>` 
            : "";
        } else {
          preview.innerHTML = input.value.replace(/\n/g, '<br>');
        }
      };
      
      input.addEventListener("input", updatePreview);
      updatePreview();
    }
  });

  const profilePicInput = document.getElementById("profilePicInput");
  const fileLabel = document.getElementById("fileLabel");
  const previewProfilePic = document.getElementById("previewProfilePic");
  
  if(profilePicInput) {
    profilePicInput.addEventListener("change", function(e) {
      const file = e.target.files[0];
      if(file) {
        fileLabel.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
          const currentFrame = document.querySelector('[name="profile_frame"]').value;
          previewProfilePic.innerHTML = `<img src="${e.target.result}" class="frame-${currentFrame}" style="width:96px;height:96px;">`;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  const profileFrameSelect = document.getElementById("profileFrameSelect");
  if(profileFrameSelect) {
    profileFrameSelect.addEventListener("change", function(e) {
      const img = previewProfilePic.querySelector('img');
      if(img) {
        img.className = `frame-${e.target.value}`;
      }
    });
  }

  const colorInputs = ['accent_color','text_color','bg_color'];
  colorInputs.forEach(name => {
    const inp = document.querySelector(`[name="${name}"]`);
    if(inp) {
      inp.addEventListener('input', e => {
        resumePreview.style.setProperty(`--${name.replace('_','-')}`, e.target.value);
      });
    }
  });

  const fontSelect = document.querySelector(`[name="font_family"]`);
  if(fontSelect) {
    fontSelect.addEventListener('change', e => {
      resumePreview.style.setProperty('--font-family', e.target.value);
    });
  }

  const fontScale = document.querySelector(`[name="font_scale"]`);
  if(fontScale) {
    fontScale.addEventListener('input', e => {
      resumePreview.style.setProperty('--font-scale', e.target.value);
    });
  }

  const dividerStyle = document.querySelector(`[name="divider_style"]`);
  if(dividerStyle) {
    dividerStyle.addEventListener('change', e => {
      resumePreview.style.setProperty('--divider-style', e.target.value);
    });
  }

  const dividerThickness = document.querySelector(`[name="divider_thickness"]`);
  if(dividerThickness) {
    dividerThickness.addEventListener('input', e => {
      resumePreview.style.setProperty('--divider-thickness', e.target.value + 'px');
    });
  }

  function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const notificationText = document.getElementById('notificationText');
    
    notificationText.textContent = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => {
      notification.classList.remove('show');
    }, 4000);
  }

  const refreshBtn = document.getElementById("refreshAiBtn");
  const form = document.getElementById("resumeForm");

  if(refreshBtn) {
    refreshBtn.addEventListener("click", function() {
      const formData = new FormData(form);
      formData.append("refresh_ai", 1);

      refreshBtn.classList.add('loading');
      refreshBtn.textContent = 'Generating...';

      fetch("resume.php", {
        method: "POST",
        body: formData
      })
      .then(res => {
        if(!res.ok) throw new Error('Network response was not ok');
        return res.json();
      })
      .then(data => {
        if(data.career_objective) {
          const input = document.querySelector("[name='career_objective']");
          const preview = document.getElementById("previewIntro");
          input.value = data.career_objective;
          preview.innerHTML = data.career_objective.replace(/\n/g, "<br>");
          showNotification('Career objective updated with AI!', 'success');
        } else if(data.error) {
          showNotification(data.error, 'error');
        }
      })
      .catch(err => {
        console.error("AI Refresh failed:", err);
        showNotification('Failed to refresh with AI. Please try again.', 'error');
      })
      .finally(() => {
        refreshBtn.classList.remove('loading');
        refreshBtn.textContent = '✨ Refresh with AI';
      });
    });
  }

  form.addEventListener('submit', function(e) {
    const name = document.querySelector('[name="name"]').value.trim();
    const email = document.querySelector('[name="email"]').value.trim();
    
    if(!name || !email) {
      e.preventDefault();
      showNotification('Please fill in all required fields (Name and Email)', 'error');
    }
  });
});
</script>
</body>
</html>