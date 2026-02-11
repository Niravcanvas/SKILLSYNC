<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

try {
    // Fetch user info
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user ? explode('@', $user['email'])[0] : "User";

    // Fetch skills from DB
    $skills = [];
    $stmt = $pdo->query("SELECT name FROM skills LIMIT 5");
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch jobs from DB
    $jobs = [];
    $stmt = $pdo->query("SELECT title, company, location FROM jobs ORDER BY posted_on DESC LIMIT 5");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $skills = [];
    $jobs = [];
    $db_error = $e->getMessage();
}

// Greeting based on time
$hour = date("H");
$greet = $hour < 12 ? "Good Morning" : ($hour < 18 ? "Good Afternoon" : "Good Evening");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Skillsync AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --primary: #6366f1;
      --primary-dark: #4f46e5;
      --primary-light: #818cf8;
      --secondary: #ec4899;
      --accent: #14b8a6;
      --success: #10b981;
      --warning: #f59e0b;
      --bg-dark: #0f172a;
      --bg-card: #1e293b;
      --bg-light: #f8fafc;
      --text-light: #f1f5f9;
      --text-gray: #94a3b8;
      --text-dark: #1e293b;
      --border: #334155;
      --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --gradient-4: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
      --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
      --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.2);
      --shadow-glow: 0 0 30px rgba(99, 102, 241, 0.3);
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
        radial-gradient(circle at 80% 80%, var(--secondary) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, var(--accent) 0%, transparent 50%);
      animation: bgMove 20s ease-in-out infinite;
    }

    @keyframes bgMove {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.1) rotate(5deg); }
    }

    /* Dashboard Content */
    .dashboard-content {
      max-width: 1280px;
      margin: 0 auto;
      padding: 2rem;
    }

    .dashboard-welcome {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 3rem;
      margin-bottom: 3rem;
      position: relative;
      overflow: hidden;
    }

    .dashboard-welcome::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient-1);
    }

    .dashboard-welcome h1 {
      font-family: 'Sora', sans-serif;
      font-size: clamp(1.8rem, 4vw, 2.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
      letter-spacing: -0.02em;
    }

    .dashboard-welcome h1 span {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .dashboard-welcome p {
      font-size: 1.1rem;
      color: var(--text-gray);
    }

    /* Dashboard Cards Grid */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 2rem;
    }

    .card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 2rem;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--gradient-1);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.4s ease;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }

    .card:hover::before {
      transform: scaleX(1);
    }

    .card:nth-child(2)::before {
      background: var(--gradient-2);
    }

    .card:nth-child(3)::before {
      background: var(--gradient-3);
    }

    .card:nth-child(4)::before {
      background: var(--gradient-4);
    }

    .card h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text-light);
    }

    .card p {
      color: var(--text-gray);
      margin-bottom: 1.5rem;
      line-height: 1.7;
    }

    .card ul {
      list-style: none;
      padding: 0;
    }

    .card ul li {
      padding: 0.75rem 1rem;
      background: var(--bg-dark);
      border: 1px solid var(--border);
      border-radius: 0.5rem;
      margin-bottom: 0.5rem;
      color: var(--text-light);
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    .card ul li:hover {
      border-color: var(--primary);
      background: rgba(99, 102, 241, 0.05);
      transform: translateX(5px);
    }

    .card ul li:last-child {
      margin-bottom: 0;
    }

    .btn-small {
      padding: 0.75rem 1.5rem;
      background: var(--gradient-1);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }

    .btn-small:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
    }

    /* Perplexity AI Card Specific Styles */
    .card textarea {
      width: 100%;
      padding: 1rem;
      border: 1px solid var(--border);
      border-radius: 0.75rem;
      background: var(--bg-dark);
      color: var(--text-light);
      font-family: 'Inter', sans-serif;
      font-size: 0.95rem;
      resize: vertical;
      min-height: 100px;
      transition: all 0.3s ease;
    }

    .card textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .card textarea::placeholder {
      color: var(--text-gray);
      opacity: 0.7;
    }

    #perplexity-response {
      margin-top: 1.5rem;
      padding: 1.5rem;
      background: var(--bg-dark);
      border: 1px solid var(--border);
      border-radius: 0.75rem;
      color: var(--text-light);
      line-height: 1.7;
      border-left: 4px solid var(--primary);
    }

    /* Footer */
    footer {
      background: var(--bg-card);
      border-top: 1px solid var(--border);
      padding: 3rem 2rem;
      margin-top: 5rem;
    }

    .footer-content {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 2rem;
    }

    .footer-links {
      display: flex;
      gap: 2rem;
    }

    .footer-links a {
      color: var(--text-gray);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-links a:hover {
      color: var(--primary);
    }

    .footer-copy {
      color: var(--text-gray);
    }

    /* Error Message */
    .error-message {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
      padding: 1rem;
      border-radius: 0.75rem;
      margin-bottom: 1.5rem;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: var(--text-gray);
      font-style: italic;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .dashboard-content {
        padding: 1.5rem;
      }

      .dashboard-welcome {
        padding: 2rem;
      }

      .dashboard-cards {
        grid-template-columns: 1fr;
      }

      .footer-content {
        flex-direction: column;
        text-align: center;
      }
    }

    /* Loading Animation */
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

    .fancy-anim {
      animation: fadeInUp 0.6s ease-out;
    }

    .card:nth-child(1) {
      animation-delay: 0.1s;
    }

    .card:nth-child(2) {
      animation-delay: 0.2s;
    }

    .card:nth-child(3) {
      animation-delay: 0.3s;
    }

    .card:nth-child(4) {
      animation-delay: 0.4s;
    }

    /* Smooth scroll behavior */
    html {
      scroll-behavior: smooth;
    }
  </style>
</head>
<body>
  <div class="bg-pattern"></div>

  <!-- Include Navbar -->
  <?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

  <!-- Main Dashboard -->
  <main class="dashboard-content">
    <section class="dashboard-welcome">
      <h1><?= htmlspecialchars($greet) ?>, <span><?= htmlspecialchars($username) ?></span>! Ready to improve your skills?</h1>
      <p>Explore personalized suggestions, career opportunities, and AI-powered insights tailored just for you.</p>
    </section>

    <?php if (isset($db_error)): ?>
      <div class="error-message">
        Database connection issue. Some features may be unavailable.
      </div>
    <?php endif; ?>

    <!-- Dashboard Cards -->
    <section class="dashboard-cards">
      <div class="card fancy-anim" id="ai-skill-card">
        <h3>AI Skill Suggestions</h3>
        <p>Let AI suggest skills to boost your career based on your profile and industry trends.</p>
        <ul id="ai-skill-list">
          <?php if (!empty($skills)): ?>
            <?php foreach ($skills as $s): ?>
              <li><?= htmlspecialchars($s['name']) ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="empty-state">No skills suggested yet. Complete your profile to get personalized recommendations.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card fancy-anim" id="jobs-card">
        <h3>Recommended Jobs & Internships</h3>
        <p>Discover opportunities tailored to your skills and resume profile.</p>
        <ul id="job-list">
          <?php if (!empty($jobs)): ?>
            <?php foreach ($jobs as $j): ?>
              <li><?= htmlspecialchars($j['title']) ?> at <?= htmlspecialchars($j['company']) ?> (<?= htmlspecialchars($j['location']) ?>)</li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="empty-state">No job recommendations available yet. Update your profile to see matches.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card fancy-anim" id="resume-card">
        <h3>Resume Builder</h3>
        <p>Create or update your professional resume with AI-powered suggestions and modern templates.</p>
        <a href="resume-builder.php" class="btn-small">Build Resume</a>
      </div>

      <div class="card fancy-anim" id="perplexity-ai-card">
        <h3>Ask AI Assistant</h3>
        <p>Get instant answers and career insights powered by advanced AI technology.</p>
        <form method="post" action="../../app/controllers/PerplexityController.php">
          <textarea name="query" placeholder="Ask me anything about careers, skills, resumes..." rows="3" required></textarea>
          <div style="margin-top:10px;">
            <button type="submit" class="btn-small">Ask AI</button>
          </div>
        </form>
        <?php if (isset($_SESSION['ai_answer'])): ?>
          <div id="perplexity-response">
            <?= htmlspecialchars($_SESSION['ai_answer']) ?>
          </div>
          <?php unset($_SESSION['ai_answer']); ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-links">
        <a href="about.php">About</a>
        <a href="developers.php">Team</a>
        <a href="#">Contact</a>
        <a href="#">Privacy</a>
      </div>
      <div class="footer-copy">&copy; 2025 Skillsync AI. All Rights Reserved.</div>
    </div>
  </footer>

</body>
</html>