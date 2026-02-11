<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection using PDO (as per README best practices)
require_once __DIR__ . '/../config/database.php';

try {
    // Fetch user data
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../../index.php");
        exit;
    }

    // Fetch related tables using helper function
    function fetchData($pdo, $table, $user_id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $educations = fetchData($pdo, 'education', $user_id);
    $experiences = fetchData($pdo, 'experience', $user_id);
    $skills = fetchData($pdo, 'skills', $user_id);
    $certifications = fetchData($pdo, 'certifications', $user_id);
    $projects = fetchData($pdo, 'projects', $user_id);

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
  
  <!-- Fonts: Sora for headings, Inter for body (as per README) -->
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  
  <link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
  
  <style>
    /* CSS Variables - Design System (as per README) */
    :root {
      /* Colors */
      --primary: #6366f1;
      --secondary: #ec4899;
      --accent: #14b8a6;
      
      /* Backgrounds */
      --bg-dark: #0f172a;
      --bg-card: #1e293b;
      --bg-card-hover: #334155;
      --bg-input: #0f172a;
      
      /* Text */
      --text-light: #f1f5f9;
      --text-gray: #94a3b8;
      --text-muted: #64748b;
      
      /* Borders & Shadows */
      --border: #334155;
      --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
      --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.2);
      
      /* Gradients */
      --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --gradient-4: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-dark);
      color: var(--text-light);
      line-height: 1.6;
      padding-top: 80px;
    }

    /* Hero Section */
    .hero-section {
      text-align: center;
      padding: 3rem 1.5rem;
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
      border-bottom: 1px solid var(--border);
      animation: fadeInDown 0.6s ease-out;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .hero-title {
      font-family: 'Sora', sans-serif;
      font-size: clamp(1.8rem, 5vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .hero-accent {
      background: var(--gradient-1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-subtitle {
      font-size: 1.1rem;
      color: var(--text-gray);
    }

    /* Form Container */
    #profile-form {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1.5rem 3rem;
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Section Styling */
    #profile-form section {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-md);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #profile-form section:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-4px);
    }

    #profile-form section h1 {
      font-family: 'Sora', sans-serif;
      font-size: clamp(1.3rem, 3vw, 1.8rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      background: var(--gradient-1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      border-bottom: 2px solid var(--border);
      padding-bottom: 0.75rem;
    }

    /* Form Elements */
    label {
      display: block;
      font-weight: 500;
      color: var(--text-light);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="url"],
    input[type="month"],
    input[type="file"],
    textarea {
      width: 100%;
      padding: 0.9rem 1rem;
      margin-bottom: 1.2rem;
      background: var(--bg-input);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      color: var(--text-light);
      font-size: 0.95rem;
      font-family: 'Inter', sans-serif;
      transition: all 0.3s ease;
    }

    input:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
      background: var(--bg-card);
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    /* File Input Styling */
    input[type="file"] {
      padding: 0.6rem;
      cursor: pointer;
    }

    input[type="file"]::file-selector-button {
      background: var(--gradient-1);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      margin-right: 1rem;
      transition: all 0.3s ease;
    }

    input[type="file"]::file-selector-button:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }

    /* Section Grid */
    .section-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    /* Sub-sections (for repeatable items) */
    .sub-section {
      background: var(--bg-input);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.3s ease;
    }

    .sub-section:hover {
      background: var(--bg-card-hover);
      border-color: var(--primary);
    }

    /* Buttons */
    .btn-primary {
      background: var(--gradient-1);
      color: white;
      border: none;
      padding: 1rem 2.5rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      font-family: 'Inter', sans-serif;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }

    .btn-add {
      background: var(--gradient-3);
      color: white;
      border: none;
      padding: 0.7rem 1.5rem;
      border-radius: 50px;
      font-weight: 500;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(79, 172, 254, 0.2);
    }

    .btn-add:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
    }

    .remove-btn {
      background: var(--gradient-2);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 500;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.3s ease;
      position: absolute;
      top: 1rem;
      right: 1rem;
    }

    .remove-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4);
    }

    /* Skills Container */
    #skills-container > div {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    #skills-container input {
      flex: 1;
      margin-bottom: 0;
    }

    #skills-container .remove-btn {
      position: static;
      margin: 0;
    }

    /* Profile Picture Preview */
    .profile-pic-preview {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      margin-top: 0.5rem;
      border: 3px solid var(--primary);
      box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
      object-fit: cover;
    }

    /* Submit Section */
    .submit-section {
      text-align: center;
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
      padding: 2rem 0 !important;
    }

    .submit-section:hover {
      transform: none !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      body {
        padding-top: 70px;
      }

      #profile-form {
        padding: 0 1rem 2rem;
      }

      #profile-form section {
        padding: 1.5rem;
      }

      .section-grid {
        grid-template-columns: 1fr;
      }

      .hero-section {
        padding: 2rem 1rem;
      }

      .btn-primary {
        width: 100%;
        max-width: none;
      }

      .sub-section {
        padding: 1.5rem 1rem 1rem;
      }

      .remove-btn {
        position: static;
        margin-top: 1rem;
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      #profile-form section h1 {
        font-size: 1.3rem;
      }

      input, textarea {
        font-size: 16px; /* Prevents zoom on iOS */
      }
    }

    /* Loading Animation */
    .loading {
      pointer-events: none;
      opacity: 0.6;
    }

    .loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 20px;
      height: 20px;
      margin: -10px 0 0 -10px;
      border: 2px solid var(--primary);
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <!-- Include Navbar Component (as per README modular approach) -->
  <?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

  <!-- Hero Section -->
  <section class="hero-section">
    <h1 class="hero-title">Welcome to Skillsync AI, <span class="hero-accent"><?= htmlspecialchars($user['full_name'] ?? 'New User') ?>!</span></h1>
    <p class="hero-subtitle">Let's complete your profile for personalized job matches and AI-powered recommendations.</p>
  </section>

  <!-- Profile Form -->
  <form id="profile-form" method="POST" action="../../Templates/save-profile.php" enctype="multipart/form-data">

    <!-- Personal Information -->
    <section>
      <h1>Personal Information</h1>
      <div class="section-grid">
        <div>
          <label>Full Name *</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
          
          <label>Headline</label>
          <input type="text" name="headline" value="<?= htmlspecialchars($user['headline'] ?? '') ?>" placeholder="e.g., Full Stack Developer | AI Enthusiast">
          
          <label>Location</label>
          <input type="text" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" placeholder="e.g., San Francisco, CA">
        </div>
        <div>
          <label>Email *</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          
          <label>Phone</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1 (555) 123-4567">
          
          <label>Profile Picture</label>
          <input type="file" name="profile_picture" accept="image/*">
          <?php if (!empty($user['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Preview" class="profile-pic-preview">
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- About Me -->
    <section>
      <h1>About Me</h1>
      <label>Bio / Summary</label>
      <textarea name="bio" placeholder="Tell us about yourself, your career goals, and what makes you unique..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
    </section>

    <!-- Education -->
    <section>
      <h1>Education</h1>
      <div id="education-container">
        <?php if (!empty($educations)): ?>
          <?php foreach ($educations as $edu): ?>
            <div class="sub-section">
              <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
              <label>Institution</label>
              <input type="text" name="education_institution[]" value="<?= htmlspecialchars($edu['institution'] ?? '') ?>">
              
              <label>Degree</label>
              <input type="text" name="education_degree[]" value="<?= htmlspecialchars($edu['degree'] ?? '') ?>" placeholder="e.g., Bachelor's, Master's, PhD">
              
              <label>Field of Study</label>
              <input type="text" name="education_field[]" value="<?= htmlspecialchars($edu['field_of_study'] ?? '') ?>" placeholder="e.g., Computer Science">
              
              <div class="section-grid">
                <div>
                  <label>Start Year</label>
                  <input type="text" name="education_start[]" value="<?= htmlspecialchars($edu['start_year'] ?? '') ?>" placeholder="e.g., 2018">
                </div>
                <div>
                  <label>End Year</label>
                  <input type="text" name="education_end[]" value="<?= htmlspecialchars($edu['end_year'] ?? '') ?>" placeholder="e.g., 2022 or Present">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sub-section">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            <label>Institution</label>
            <input type="text" name="education_institution[]" placeholder="e.g., Stanford University">
            
            <label>Degree</label>
            <input type="text" name="education_degree[]" placeholder="e.g., Bachelor's">
            
            <label>Field of Study</label>
            <input type="text" name="education_field[]" placeholder="e.g., Computer Science">
            
            <div class="section-grid">
              <div>
                <label>Start Year</label>
                <input type="text" name="education_start[]" placeholder="e.g., 2018">
              </div>
              <div>
                <label>End Year</label>
                <input type="text" name="education_end[]" placeholder="e.g., 2022">
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addEducation()">+ Add Education</button>
    </section>

    <!-- Work Experience -->
    <section>
      <h1>Work Experience</h1>
      <div id="experience-container">
        <?php if (!empty($experiences)): ?>
          <?php foreach ($experiences as $exp): ?>
            <div class="sub-section">
              <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
              <label>Company</label>
              <input type="text" name="experience_company[]" value="<?= htmlspecialchars($exp['company'] ?? '') ?>">
              
              <label>Position</label>
              <input type="text" name="experience_position[]" value="<?= htmlspecialchars($exp['position'] ?? '') ?>">
              
              <div class="section-grid">
                <div>
                  <label>Start Date</label>
                  <input type="month" name="experience_start[]" value="<?= htmlspecialchars($exp['start_date'] ?? '') ?>">
                </div>
                <div>
                  <label>End Date</label>
                  <input type="month" name="experience_end[]" value="<?= htmlspecialchars($exp['end_date'] ?? '') ?>">
                </div>
              </div>
              
              <label>Description</label>
              <textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."><?= htmlspecialchars($exp['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sub-section">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            <label>Company</label>
            <input type="text" name="experience_company[]" placeholder="e.g., Google">
            
            <label>Position</label>
            <input type="text" name="experience_position[]" placeholder="e.g., Software Engineer">
            
            <div class="section-grid">
              <div>
                <label>Start Date</label>
                <input type="month" name="experience_start[]">
              </div>
              <div>
                <label>End Date</label>
                <input type="month" name="experience_end[]">
              </div>
            </div>
            
            <label>Description</label>
            <textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."></textarea>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addExperience()">+ Add Experience</button>
    </section>

    <!-- Skills -->
    <section>
      <h1>Skills</h1>
      <div id="skills-container">
        <?php if (!empty($skills)): ?>
          <?php foreach ($skills as $skill): ?>
            <div>
              <input type="text" name="skills[]" value="<?= htmlspecialchars($skill['skill_name'] ?? $skill['skill'] ?? '') ?>">
              <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div>
            <input type="text" name="skills[]" placeholder="e.g., JavaScript, Python, React">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addSkill()">+ Add Skill</button>
    </section>

    <!-- Certifications & Awards -->
    <section>
      <h1>Certifications & Awards</h1>
      <div id="certifications-container">
        <?php if (!empty($certifications)): ?>
          <?php foreach ($certifications as $cert): ?>
            <div class="sub-section">
              <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
              <label>Title</label>
              <input type="text" name="cert_title[]" value="<?= htmlspecialchars($cert['title'] ?? '') ?>">
              
              <label>Issuer</label>
              <input type="text" name="cert_issuer[]" value="<?= htmlspecialchars($cert['issuer'] ?? '') ?>">
              
              <label>Date</label>
              <input type="month" name="cert_date[]" value="<?= htmlspecialchars($cert['cert_date'] ?? $cert['date'] ?? '') ?>">
              
              <label>Credential URL</label>
              <input type="url" name="cert_url[]" value="<?= htmlspecialchars($cert['url'] ?? '') ?>" placeholder="https://...">
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sub-section">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            <label>Title</label>
            <input type="text" name="cert_title[]" placeholder="e.g., AWS Certified Developer">
            
            <label>Issuer</label>
            <input type="text" name="cert_issuer[]" placeholder="e.g., Amazon Web Services">
            
            <label>Date</label>
            <input type="month" name="cert_date[]">
            
            <label>Credential URL</label>
            <input type="url" name="cert_url[]" placeholder="https://...">
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addCert()">+ Add Certification</button>
    </section>

    <!-- Projects / Portfolio -->
    <section>
      <h1>Projects / Portfolio</h1>
      <div id="projects-container">
        <?php if (!empty($projects)): ?>
          <?php foreach ($projects as $proj): ?>
            <div class="sub-section">
              <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
              <label>Project Name</label>
              <input type="text" name="project_name[]" value="<?= htmlspecialchars($proj['project_name'] ?? '') ?>">
              
              <label>Description</label>
              <textarea name="project_description[]" placeholder="Describe what you built and the impact..."><?= htmlspecialchars($proj['description'] ?? '') ?></textarea>
              
              <label>Technologies Used</label>
              <input type="text" name="project_tech[]" value="<?= htmlspecialchars($proj['technologies'] ?? '') ?>" placeholder="e.g., React, Node.js, MongoDB">
              
              <label>Project URL</label>
              <input type="url" name="project_url[]" value="<?= htmlspecialchars($proj['project_url'] ?? '') ?>" placeholder="https://github.com/...">
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sub-section">
            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
            <label>Project Name</label>
            <input type="text" name="project_name[]" placeholder="e.g., E-commerce Platform">
            
            <label>Description</label>
            <textarea name="project_description[]" placeholder="Describe what you built and the impact..."></textarea>
            
            <label>Technologies Used</label>
            <input type="text" name="project_tech[]" placeholder="e.g., React, Node.js, MongoDB">
            
            <label>Project URL</label>
            <input type="url" name="project_url[]" placeholder="https://github.com/...">
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn-add" onclick="addProject()">+ Add Project</button>
    </section>

    <!-- Submit Section -->
    <section class="submit-section">
      <button type="submit" class="btn-primary">Save Profile</button>
    </section>

  </form>

  <script>
    // Dynamic form functions with improved HTML structure
    function addEducation() {
      const container = document.getElementById('education-container');
      const html = `
        <div class="sub-section">
          <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
          <label>Institution</label>
          <input type="text" name="education_institution[]" placeholder="e.g., Stanford University">
          <label>Degree</label>
          <input type="text" name="education_degree[]" placeholder="e.g., Bachelor's">
          <label>Field of Study</label>
          <input type="text" name="education_field[]" placeholder="e.g., Computer Science">
          <div class="section-grid">
            <div>
              <label>Start Year</label>
              <input type="text" name="education_start[]" placeholder="e.g., 2018">
            </div>
            <div>
              <label>End Year</label>
              <input type="text" name="education_end[]" placeholder="e.g., 2022">
            </div>
          </div>
        </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
    }

    function addExperience() {
      const container = document.getElementById('experience-container');
      const html = `
        <div class="sub-section">
          <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
          <label>Company</label>
          <input type="text" name="experience_company[]" placeholder="e.g., Google">
          <label>Position</label>
          <input type="text" name="experience_position[]" placeholder="e.g., Software Engineer">
          <div class="section-grid">
            <div>
              <label>Start Date</label>
              <input type="month" name="experience_start[]">
            </div>
            <div>
              <label>End Date</label>
              <input type="month" name="experience_end[]">
            </div>
          </div>
          <label>Description</label>
          <textarea name="experience_description[]" placeholder="Describe your responsibilities and achievements..."></textarea>
        </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
    }

    function addSkill() {
      const container = document.getElementById('skills-container');
      const html = `
        <div>
          <input type="text" name="skills[]" placeholder="e.g., JavaScript, Python, React">
          <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
        </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
    }

    function addCert() {
      const container = document.getElementById('certifications-container');
      const html = `
        <div class="sub-section">
          <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
          <label>Title</label>
          <input type="text" name="cert_title[]" placeholder="e.g., AWS Certified Developer">
          <label>Issuer</label>
          <input type="text" name="cert_issuer[]" placeholder="e.g., Amazon Web Services">
          <label>Date</label>
          <input type="month" name="cert_date[]">
          <label>Credential URL</label>
          <input type="url" name="cert_url[]" placeholder="https://...">
        </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
    }

    function addProject() {
      const container = document.getElementById('projects-container');
      const html = `
        <div class="sub-section">
          <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
          <label>Project Name</label>
          <input type="text" name="project_name[]" placeholder="e.g., E-commerce Platform">
          <label>Description</label>
          <textarea name="project_description[]" placeholder="Describe what you built and the impact..."></textarea>
          <label>Technologies Used</label>
          <input type="text" name="project_tech[]" placeholder="e.g., React, Node.js, MongoDB">
          <label>Project URL</label>
          <input type="url" name="project_url[]" placeholder="https://github.com/...">
        </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
    }

    // Form submission handling
    document.getElementById('profile-form').addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('.btn-primary');
      submitBtn.textContent = 'Saving...';
      submitBtn.classList.add('loading');
    });
  </script>

</body>
</html>