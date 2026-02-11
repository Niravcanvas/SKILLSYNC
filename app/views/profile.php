<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection using PDO (following README best practices)
require_once __DIR__ . '/../config/database.php';

try {
    // Fetch user info
    $user_sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($user_sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../../index.php");
        exit;
    }

    // Fetch education
    $edu_sql = "SELECT * FROM education WHERE user_id = ? ORDER BY start_year DESC";
    $stmt = $pdo->prepare($edu_sql);
    $stmt->execute([$user_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch experience
    $exp_sql = "SELECT * FROM experience WHERE user_id = ? ORDER BY start_date DESC";
    $stmt = $pdo->prepare($exp_sql);
    $stmt->execute([$user_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch skills
    $skill_sql = "SELECT * FROM skills WHERE user_id = ?";
    $stmt = $pdo->prepare($skill_sql);
    $stmt->execute([$user_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch certifications
    $cert_sql = "SELECT * FROM certifications WHERE user_id = ? ORDER BY cert_date DESC";
    $stmt = $pdo->prepare($cert_sql);
    $stmt->execute([$user_id]);
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch projects
    $proj_sql = "SELECT * FROM projects WHERE user_id = ? ORDER BY id DESC";
    $stmt = $pdo->prepare($proj_sql);
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Your Profile – Skillsync AI</title>
  
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
      padding-top: 80px; /* Account for fixed navbar */
    }

    /* Profile Container */
    .profile-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
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
    .section {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-md);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .section:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-4px);
    }

    .section-title {
      font-family: 'Sora', sans-serif;
      font-size: clamp(1.5rem, 4vw, 2rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      background: var(--gradient-1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Personal Info Section */
    .info-grid {
      display: grid;
      grid-template-columns: 150px 1fr;
      gap: 2rem;
      align-items: start;
    }

    .profile-pic {
      position: relative;
      width: 150px;
      height: 150px;
      border-radius: 50%;
      overflow: hidden;
      border: 4px solid var(--primary);
      box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
    }

    .profile-pic img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .info-item {
      margin-bottom: 1rem;
      font-size: 1rem;
    }

    .info-item label {
      font-weight: 600;
      color: var(--primary);
      margin-right: 0.5rem;
    }

    /* About Me Section */
    .section p {
      color: var(--text-gray);
      font-size: 1rem;
      line-height: 1.8;
    }

    /* Section Grid (for education, experience, etc.) */
    .section-grid {
      display: grid;
      gap: 1.5rem;
    }

    .sub-section {
      background: var(--bg-dark);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 1.5rem;
      transition: all 0.3s ease;
    }

    .sub-section:hover {
      background: var(--bg-card-hover);
      border-color: var(--primary);
      transform: translateX(8px);
    }

    .sub-section h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--text-light);
      margin-bottom: 0.5rem;
    }

    .sub-section span {
      display: block;
      color: var(--text-gray);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .sub-section a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }

    .sub-section a:hover {
      color: var(--secondary);
      gap: 0.75rem;
    }

    /* Skills Section */
    .skills-list {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .skill {
      background: var(--gradient-1);
      color: white;
      padding: 0.6rem 1.2rem;
      border-radius: 50px;
      font-weight: 500;
      font-size: 0.9rem;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
      transition: all 0.3s ease;
      cursor: default;
    }

    .skill:hover {
      transform: translateY(-4px) scale(1.05);
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    /* Button Styling */
    .btn-primary {
      background: var(--gradient-1);
      color: white;
      border: none;
      padding: 0.8rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      margin-top: 1rem;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--text-muted);
      font-style: italic;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .profile-container {
        padding: 1rem;
      }

      .info-grid {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 1.5rem;
      }

      .profile-pic {
        margin: 0 auto;
      }

      .section {
        padding: 1.5rem;
      }

      .section-title {
        font-size: 1.5rem;
      }

      .skills-list {
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      body {
        padding-top: 70px;
      }

      .section {
        padding: 1rem;
        border-radius: 12px;
      }

      .btn-primary {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Include Navbar Component (as per README modular approach) -->
  <?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

  <div class="profile-container">

    <!-- Personal Information Section -->
    <section class="section">
      <h2 class="section-title">Personal Information</h2>
      <div class="info-grid">
        <div class="profile-pic">
          <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : '../../public/images/default-profile.svg' ?>" 
               alt="Profile Picture">
        </div>
        <div>
          <div class="info-item">
            <label>Full Name:</label> 
            <?= htmlspecialchars($user['full_name'] ?? 'Not provided') ?>
          </div>
          <div class="info-item">
            <label>Headline:</label> 
            <?= htmlspecialchars($user['headline'] ?? 'Not provided') ?>
          </div>
          <div class="info-item">
            <label>Location:</label> 
            <?= htmlspecialchars($user['location'] ?? 'Not provided') ?>
          </div>
          <div class="info-item">
            <label>Email:</label> 
            <?= htmlspecialchars($user['email']) ?>
          </div>
          <div class="info-item">
            <label>Phone:</label> 
            <?= htmlspecialchars($user['phone'] ?? 'Not provided') ?>
          </div>
          <button class="btn-primary" onclick="window.location.href='form.php'">
            Edit Profile
          </button>
        </div>
      </div>
    </section>

    <!-- About Me Section -->
    <section class="section">
      <h2 class="section-title">About Me</h2>
      <?php if (!empty($user['bio'])): ?>
        <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
      <?php else: ?>
        <p class="empty-state">No bio added yet. Click "Edit Profile" to add one!</p>
      <?php endif; ?>
    </section>

    <!-- Education Section -->
    <section class="section">
      <h2 class="section-title">Education</h2>
      <div class="section-grid">
        <?php if (!empty($education)): ?>
          <?php foreach ($education as $edu): ?>
            <div class="sub-section">
              <h3><?= htmlspecialchars($edu['degree']) ?> in <?= htmlspecialchars($edu['field_of_study']) ?></h3>
              <span><?= htmlspecialchars($edu['institution']) ?></span>
              <span><?= htmlspecialchars($edu['start_year']) ?> - <?= htmlspecialchars($edu['end_year']) ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-state">No education information added yet.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Work Experience Section -->
    <section class="section">
      <h2 class="section-title">Work Experience</h2>
      <div class="section-grid">
        <?php if (!empty($experience)): ?>
          <?php foreach ($experience as $exp): ?>
            <div class="sub-section">
              <h3><?= htmlspecialchars($exp['position']) ?> at <?= htmlspecialchars($exp['company']) ?></h3>
              <span><?= htmlspecialchars($exp['start_date']) ?> - <?= htmlspecialchars($exp['end_date']) ?></span>
              <span><?= nl2br(htmlspecialchars($exp['description'])) ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-state">No work experience added yet.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Skills Section -->
    <section class="section">
      <h2 class="section-title">Skills</h2>
      <div class="skills-list">
        <?php if (!empty($skills)): ?>
          <?php foreach ($skills as $skill): ?>
            <span class="skill"><?= htmlspecialchars($skill['skill_name']) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-state">No skills added yet.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Certifications & Awards Section -->
    <section class="section">
      <h2 class="section-title">Certifications & Awards</h2>
      <div class="section-grid">
        <?php if (!empty($certifications)): ?>
          <?php foreach ($certifications as $cert): ?>
            <div class="sub-section">
              <h3><?= htmlspecialchars($cert['title']) ?></h3>
              <span>Issuer: <?= htmlspecialchars($cert['issuer']) ?></span>
              <span>Date: <?= htmlspecialchars($cert['cert_date']) ?></span>
              <?php if (!empty($cert['url'])): ?>
                <a href="<?= htmlspecialchars($cert['url']) ?>" target="_blank" rel="noopener noreferrer">
                  View Credential →
                </a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-state">No certifications added yet.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Projects / Portfolio Section -->
    <section class="section">
      <h2 class="section-title">Projects / Portfolio</h2>
      <div class="section-grid">
        <?php if (!empty($projects)): ?>
          <?php foreach ($projects as $proj): ?>
            <div class="sub-section">
              <h3><?= htmlspecialchars($proj['project_name']) ?></h3>
              <span>Description: <?= nl2br(htmlspecialchars($proj['description'])) ?></span>
              <span>Technologies: <?= htmlspecialchars($proj['technologies']) ?></span>
              <?php if (!empty($proj['project_url'])): ?>
                <a href="<?= htmlspecialchars($proj['project_url']) ?>" target="_blank" rel="noopener noreferrer">
                  View Project →
                </a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="empty-state">No projects added yet.</p>
        <?php endif; ?>
      </div>
    </section>

  </div>
</body>
</html>