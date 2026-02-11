<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require __DIR__ . '/app/config/database.php';

$login_message = '';
$signup_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // LOGIN
    if (isset($_POST['login_submit'])) {
        $email = strtolower(trim($_POST['login_email']));
        $password = trim($_POST['login_password']);

        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT id, password, profile_complete FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];

                // Redirect based on profile completion
                if ($user['profile_complete'] == 0) {
                    header('Location: app/views/form.php');
                    exit;
                } else {
                    header('Location: app/views/dashboard.php');
                    exit;
                }
            } else {
                $login_message = "Incorrect email or password";
            }
        } else {
            $login_message = "Email and password are required";
        }
    }

    // SIGNUP
    if (isset($_POST['signup_submit'])) {
        $email = strtolower(trim($_POST['signup_email']));
        $password = trim($_POST['signup_password']);

        if ($email && $password) {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $signup_message = "Email already registered";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                if ($stmt->execute([$email, $password])) {
                    $signup_message = "Signup successful! You can now login.";
                } else {
                    $signup_message = "Signup failed";
                }
            }
        } else {
            $signup_message = "Email and password are required";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skillsync AI – Build Your Perfect Resume with AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="public/images/favicon.svg">
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
      overflow-x: hidden;
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

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      padding: 1.2rem 0;
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .navbar.scrolled {
      background: rgba(15, 23, 42, 0.95);
      box-shadow: var(--shadow-md);
      padding: 1rem 0;
    }

    .nav-container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-family: 'Sora', sans-serif;
      font-size: 1.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.02em;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .nav-links {
      display: flex;
      gap: 1rem;
      list-style: none;
      align-items: center;
    }

    .nav-links > li > a:not(.btn) {
      color: var(--text-gray);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      position: relative;
      padding: 0.5rem 1rem;
      display: inline-block;
    }

    .nav-links > li > a:not(.btn)::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 1rem;
      right: 1rem;
      height: 2px;
      background: var(--primary);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .nav-links > li > a:not(.btn):hover {
      color: var(--text-light);
    }

    .nav-links > li > a:not(.btn):hover::after {
      transform: scaleX(1);
    }

    .nav-links > li > a:not(.btn).active {
      color: var(--primary);
    }

    /* Enhanced Button Styles */
    .btn {
      padding: 0.85rem 2rem;
      border-radius: 0.75rem;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      text-decoration: none;
      display: inline-block;
      white-space: nowrap;
    }

    .btn-primary {
      background: var(--gradient-1);
      color: white;
      box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
      position: relative;
      overflow: hidden;
    }

    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s ease;
    }

    .btn-primary:hover::before {
      left: 100%;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 30px rgba(99, 102, 241, 0.6);
    }

    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .btn-outline::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0;
      height: 100%;
      background: var(--primary);
      transition: width 0.4s ease;
      z-index: -1;
    }

    .btn-outline:hover::before {
      width: 100%;
    }

    .btn-outline:hover {
      color: white;
      border-color: var(--primary);
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
    }

    /* Hero Section */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8rem 2rem 4rem;
      position: relative;
      overflow: hidden;
    }

    .hero-content {
      max-width: 1200px;
      text-align: center;
      z-index: 1;
      animation: fadeInUp 1s ease;
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

    .hero-title {
      font-family: 'Sora', sans-serif;
      font-size: clamp(2.5rem, 6vw, 5rem);
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 1.5rem;
      letter-spacing: -0.03em;
    }

    .gradient-text {
      background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 50%, var(--accent) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: gradientFlow 3s ease infinite;
      background-size: 200% auto;
    }

    @keyframes gradientFlow {
      0%, 100% { background-position: 0% center; }
      50% { background-position: 100% center; }
    }

    .hero-subtitle {
      font-size: clamp(1.1rem, 2vw, 1.5rem);
      color: var(--text-gray);
      margin-bottom: 3rem;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
      animation: fadeInUp 1s ease 0.2s both;
    }

    .hero-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      animation: fadeInUp 1s ease 0.4s both;
    }

    .scroll-indicator {
      position: absolute;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%);
      animation: bounce 2s infinite;
    }

    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(-10px); }
    }

    .scroll-indicator svg {
      width: 24px;
      height: 24px;
      opacity: 0.5;
    }

    /* Section Styling */
    section {
      padding: 5rem 2rem;
      position: relative;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-header {
      text-align: center;
      margin-bottom: 4rem;
    }

    .section-title {
      font-family: 'Sora', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 700;
      margin-bottom: 1rem;
      letter-spacing: -0.02em;
    }

    .section-subtitle {
      font-size: 1.2rem;
      color: var(--text-gray);
      max-width: 600px;
      margin: 0 auto;
    }

    /* Features Grid */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }

    .feature-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 2rem;
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .feature-card::before {
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

    /* Unique Hover Animation 1: Scale & Rotate */
    .feature-card:nth-child(1) {
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .feature-card:nth-child(1):hover {
      transform: scale(1.05) rotate(2deg);
      box-shadow: 0 20px 60px rgba(99, 102, 241, 0.4);
      border-color: var(--primary);
    }

    .feature-card:nth-child(1):hover::before {
      transform: scaleX(1);
    }

    /* Unique Hover Animation 2: Slide & Lift */
    .feature-card:nth-child(2)::before {
      background: var(--gradient-2);
    }

    .feature-card:nth-child(2) {
      transition: all 0.4s ease;
    }

    .feature-card:nth-child(2):hover {
      transform: translateY(-15px) translateX(5px);
      box-shadow: -10px 20px 60px rgba(236, 72, 153, 0.4);
      border-color: var(--secondary);
    }

    .feature-card:nth-child(2):hover::before {
      transform: scaleX(1);
    }

    /* Unique Hover Animation 3: 3D Flip Effect */
    .feature-card:nth-child(3) {
      transition: all 0.6s ease;
      transform-style: preserve-3d;
    }

    .feature-card:nth-child(3)::before {
      background: var(--gradient-3);
    }

    .feature-card:nth-child(3):hover {
      transform: perspective(1000px) rotateY(10deg) translateY(-10px);
      box-shadow: 15px 15px 60px rgba(20, 184, 166, 0.4);
      border-color: var(--accent);
    }

    .feature-card:nth-child(3):hover::before {
      transform: scaleX(1);
    }

    /* Unique Hover Animation 4: Glow Pulse */
    .feature-card:nth-child(4)::before {
      background: var(--gradient-4);
    }

    .feature-card:nth-child(4) {
      transition: all 0.4s ease;
    }

    .feature-card:nth-child(4):hover {
      transform: translateY(-12px);
      box-shadow: 0 0 60px rgba(250, 112, 154, 0.6), 
                  0 20px 40px rgba(250, 112, 154, 0.3);
      border-color: #fa709a;
      animation: glowPulse 1.5s ease-in-out infinite;
    }

    @keyframes glowPulse {
      0%, 100% {
        box-shadow: 0 0 60px rgba(250, 112, 154, 0.6), 
                    0 20px 40px rgba(250, 112, 154, 0.3);
      }
      50% {
        box-shadow: 0 0 80px rgba(250, 112, 154, 0.8), 
                    0 20px 50px rgba(250, 112, 154, 0.5);
      }
    }

    .feature-card:nth-child(4):hover::before {
      transform: scaleX(1);
    }

    /* Feature Icon Styles - No Emojis */
    .feature-icon {
      width: 70px;
      height: 70px;
      background: var(--gradient-1);
      border-radius: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.4s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .feature-icon svg {
      width: 35px;
      height: 35px;
      fill: white;
    }

    .feature-card:nth-child(2) .feature-icon {
      background: var(--gradient-2);
    }

    .feature-card:nth-child(3) .feature-icon {
      background: var(--gradient-3);
    }

    .feature-card:nth-child(4) .feature-icon {
      background: var(--gradient-4);
    }

    .feature-card h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text-light);
    }

    .feature-card p {
      color: var(--text-gray);
      line-height: 1.7;
      margin-bottom: 1.5rem;
    }

    .feature-card a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: gap 0.3s ease;
    }

    .feature-card a:hover {
      gap: 0.8rem;
    }

    /* About Section */
    .about-section {
      background: var(--bg-card);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }

    .about-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 3rem;
      margin-top: 3rem;
    }

    .about-card {
      background: var(--bg-dark);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 2.5rem;
      transition: all 0.3s ease;
    }

    .about-card:hover {
      border-color: var(--primary);
      box-shadow: var(--shadow-md);
    }

    .about-card h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--primary);
    }

    .about-card p {
      color: var(--text-gray);
      line-height: 1.8;
    }

    /* How It Works Section */
    .how-it-works-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-top: 3rem;
    }

    .how-card {
      text-align: center;
      padding: 2rem;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .how-card::before {
      content: attr(data-step);
      position: absolute;
      top: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 40px;
      height: 40px;
      background: var(--gradient-1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-family: 'Sora', sans-serif;
      font-size: 1.2rem;
      box-shadow: var(--shadow-md);
    }

    .how-card:hover {
      transform: translateY(-5px);
      border-color: var(--primary);
    }

    .how-card h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.3rem;
      font-weight: 600;
      margin: 2rem 0 1rem;
    }

    .how-card p {
      color: var(--text-gray);
      line-height: 1.7;
    }

    /* Developer Section */
    .developer-section {
      background: var(--bg-card);
      border-top: 1px solid var(--border);
    }

    .developer-card {
      max-width: 600px;
      margin: 3rem auto 0;
      background: var(--bg-dark);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 3rem;
      text-align: center;
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
    }

    .developer-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: var(--gradient-1);
    }

    .developer-card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary);
    }

    .developer-avatar {
      width: 120px;
      height: 120px;
      background: var(--gradient-1);
      border-radius: 50%;
      margin: 0 auto 2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      font-weight: 700;
      font-family: 'Sora', sans-serif;
      box-shadow: var(--shadow-md);
    }

    .developer-card h3 {
      font-family: 'Sora', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .developer-role {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 1.5rem;
      font-size: 1.1rem;
    }

    .developer-card p {
      color: var(--text-gray);
      line-height: 1.8;
      margin-bottom: 2rem;
    }

    /* CTA Section */
    .cta-section {
      text-align: center;
      padding: 6rem 2rem;
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);
    }

    .cta-content {
      max-width: 700px;
      margin: 0 auto;
    }

    .cta-title {
      font-family: 'Sora', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 700;
      margin-bottom: 1.5rem;
      letter-spacing: -0.02em;
    }

    .cta-subtitle {
      font-size: 1.2rem;
      color: var(--text-gray);
      margin-bottom: 2.5rem;
    }

    /* Footer */
    footer {
      background: var(--bg-card);
      border-top: 1px solid var(--border);
      padding: 3rem 2rem;
      text-align: center;
    }

    footer p {
      color: var(--text-gray);
    }

    footer a {
      color: var(--primary);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    footer a:hover {
      color: var(--primary-light);
    }

    /* Modal Styles */
    .auth-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      backdrop-filter: blur(8px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .auth-modal.hidden {
      opacity: 0;
      pointer-events: none;
    }

    .auth-content {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 1.5rem;
      padding: 3rem;
      max-width: 480px;
      width: 90%;
      position: relative;
      animation: modalSlideIn 0.4s ease;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .close {
      position: absolute;
      top: 1.5rem;
      right: 1.5rem;
      font-size: 2rem;
      color: var(--text-gray);
      cursor: pointer;
      transition: all 0.3s ease;
      line-height: 1;
    }

    .close:hover {
      color: var(--text-light);
      transform: rotate(90deg);
    }

    .auth-form h2 {
      font-family: 'Sora', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 2rem;
      text-align: center;
    }

    .input-group {
      margin-bottom: 1.5rem;
    }

    .input-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--text-gray);
      font-weight: 500;
      font-size: 0.9rem;
    }

    .input-group input {
      width: 100%;
      padding: 1rem 1.25rem;
      border: 1px solid var(--border);
      border-radius: 0.75rem;
      background: var(--bg-dark);
      color: var(--text-light);
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .input-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }

    .form-options label {
      color: var(--text-gray);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: pointer;
    }

    .form-options a {
      color: var(--primary);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .form-options a:hover {
      color: var(--primary-light);
    }

    .auth-form button[type="submit"] {
      width: 100%;
      padding: 1.1rem;
      font-size: 1.05rem;
    }

    .auth-form > p {
      text-align: center;
      margin-top: 1.5rem;
      color: var(--text-gray);
    }

    .auth-form > p a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .auth-form > p a:hover {
      color: var(--primary-light);
    }

    .message {
      padding: 1rem;
      border-radius: 0.75rem;
      margin-bottom: 1.5rem;
      font-weight: 500;
      text-align: center;
    }

    .message-error {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
    }

    .message-success {
      background: rgba(34, 197, 94, 0.1);
      border: 1px solid rgba(34, 197, 94, 0.3);
      color: #86efac;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .nav-links {
        gap: 0.5rem;
      }

      .nav-links > li > a:not(.btn) {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
      }

      .btn {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
      }

      .hero {
        padding: 6rem 1.5rem 3rem;
      }

      .hero-buttons {
        flex-direction: column;
        align-items: stretch;
      }

      .features-grid,
      .about-content,
      .how-it-works-grid {
        grid-template-columns: 1fr;
      }

      section {
        padding: 3rem 1.5rem;
      }

      .auth-content {
        padding: 2.5rem 2rem;
      }
    }

    /* Loading Animation */
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .loading {
      animation: pulse 2s ease-in-out infinite;
    }
  </style>
</head>
<body>
  <div class="bg-pattern"></div>

  <!-- Navbar -->
  <nav class="navbar" id="navbar">
    <div class="nav-container">
      <div class="logo">Skillsync AI</div>
      <ul class="nav-links">
        <li><a href="#home" class="active">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#developer">Team</a></li>
        <li><a href="#" class="btn btn-outline" id="open-login">Login</a></li>
        <li><a href="#" class="btn btn-primary" id="open-signup">Sign Up</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-content">
      <h1 class="hero-title">
        Build Your Perfect Resume with <span class="gradient-text">AI Intelligence</span>
      </h1>
      <p class="hero-subtitle">
        Transform your career journey with cutting-edge AI technology. Get personalized skill suggestions, 
        job recommendations, and professional resume templates that make you stand out.
      </p>
      <div class="hero-buttons">
        <a href="#" class="btn btn-primary" id="hero-signup">Get Started Free</a>
        <a href="#features" class="btn btn-outline">Explore Features</a>
      </div>
    </div>
    <div class="scroll-indicator">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Powerful Features for Your Success</h2>
        <p class="section-subtitle">
          Everything you need to create an outstanding resume and land your dream job
        </p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 7L12 12L22 7L12 2Z"/>
              <path d="M2 17L12 22L22 17"/>
              <path d="M2 12L12 17L22 12"/>
            </svg>
          </div>
          <h3>AI Skill Suggestions</h3>
          <p>Our intelligent engine scans thousands of industry profiles and emerging trends to suggest personalized skills that make your resume stand out.</p>
          <a href="#">Learn More →</a>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="3" width="18" height="18" rx="2"/>
              <path d="M3 9H21"/>
              <path d="M9 21V9"/>
            </svg>
          </div>
          <h3>Job Recommendations</h3>
          <p>Discover curated opportunities that perfectly match your resume and hidden potential. Your next dream job is waiting.</p>
          <a href="#">View Jobs →</a>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z"/>
              <path d="M14 2V8H20"/>
              <path d="M16 13H8"/>
              <path d="M16 17H8"/>
              <path d="M10 9H8"/>
            </svg>
          </div>
          <h3>Resume Templates</h3>
          <p>Choose from our library of sleek, professional templates designed to highlight your strengths and impress recruiters.</p>
          <a href="#">Browse Templates →</a>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M21.21 15.89C20.5738 17.3945 19.5788 18.7202 18.3119 19.7513C17.0449 20.7824 15.5447 21.4874 13.9424 21.8048C12.34 22.1221 10.6843 22.0421 9.12006 21.5718C7.55578 21.1015 6.13054 20.2551 4.96893 19.1067C3.80733 17.9582 2.94473 16.5428 2.45655 14.984C1.96837 13.4251 1.86948 11.7705 2.16803 10.1646C2.46659 8.55878 3.15507 7.05063 4.17187 5.77203C5.18868 4.49342 6.50299 3.48332 8.00002 2.83"/>
              <path d="M22 12C22 10.6868 21.7413 9.38642 21.2388 8.17317C20.7362 6.95991 19.9997 5.85752 19.0711 4.92893C18.1425 4.00035 17.0401 3.26375 15.8268 2.7612C14.6136 2.25866 13.3132 2 12 2V12H22Z"/>
            </svg>
          </div>
          <h3>Resume Analysis</h3>
          <p>Get actionable insights and AI-powered feedback to enhance your resume. Know exactly how recruiters will perceive it.</p>
          <a href="#">Analyze Now →</a>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="about-section" id="about">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">About Skillsync AI</h2>
        <p class="section-subtitle">
          Revolutionizing the way you create your professional identity
        </p>
      </div>
      <div class="about-content">
        <div class="about-card">
          <h3>Our Mission</h3>
          <p>
            At Skillsync AI, we believe every individual has untapped potential. Our mission is to unlock 
            that potential by combining cutting-edge AI with creative resume design. We bring imagination 
            to reality, helping you get recognized before you even walk into the room.
          </p>
        </div>
        <div class="about-card">
          <h3>Our Vision</h3>
          <p>
            We envision a world where career growth is guided by insight, not luck. Skillsync AI strives 
            to be the bridge between talent and opportunity, making professional success accessible to everyone.
          </p>
        </div>
        <div class="about-card">
          <h3>Our Technology</h3>
          <p>
            Powered by advanced AI algorithms and machine learning, we analyze industry trends, job markets, 
            and professional profiles to give you the competitive edge you deserve in your career journey.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section>
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">
          Get started in minutes with our simple, powerful process
        </p>
      </div>
      <div class="how-it-works-grid">
        <div class="how-card" data-step="1">
          <h3>Create Account</h3>
          <p>Sign up for free and complete your profile with your professional information and career goals.</p>
        </div>
        <div class="how-card" data-step="2">
          <h3>Get AI Suggestions</h3>
          <p>Our AI analyzes your profile and provides personalized skill recommendations and insights.</p>
        </div>
        <div class="how-card" data-step="3">
          <h3>Build Your Resume</h3>
          <p>Choose from professional templates and let AI help you create a standout resume.</p>
        </div>
        <div class="how-card" data-step="4">
          <h3>Land Your Dream Job</h3>
          <p>Get matched with relevant opportunities and take your career to the next level.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Developer Section -->
  <section class="developer-section" id="developer">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Meet the Developer</h2>
        <p class="section-subtitle">
          The mind behind Skillsync AI – crafting innovative features and seamless experiences
        </p>
      </div>
      <div class="developer-card">
        <div class="developer-avatar">LJN</div>
        <h3>Lawrance Johnwilson Nadar</h3>
        <p class="developer-role">Full Stack Developer & AI Specialist</p>
        <p>
          Passionate about coding, design, and making AI accessible for everyone. Specializing in 
          AI-powered tools and intuitive user experiences that make a real difference in people's careers.
        </p>
        <a href="mailto:lawrencejohnwilson28624@gmail.com" class="btn btn-primary">Get in Touch</a>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="cta-content">
      <h2 class="cta-title">Ready to Transform Your Career?</h2>
      <p class="cta-subtitle">
        Join thousands of professionals who have already elevated their careers with Skillsync AI
      </p>
      <div class="hero-buttons">
        <a href="#" class="btn btn-primary" id="cta-signup">Start Building Now</a>
        <a href="#features" class="btn btn-outline">Learn More</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>&copy; 2025 Skillsync AI. All rights reserved. <a href="#developer">Lawrance Johnwilson Nadar</a></p>
    </div>
  </footer>

  <!-- Login Modal -->
  <div id="login-modal" class="auth-modal <?php echo $login_message ? '' : 'hidden'; ?>">
    <div class="auth-content">
      <span id="login-close" class="close">&times;</span>
      <form method="POST" class="auth-form">
        <h2>Welcome Back</h2>
        <?php if ($login_message) echo "<div class='message message-error'>$login_message</div>"; ?>
        <div class="input-group">
          <label>Email Address</label>
          <input type="email" name="login_email" required autocomplete="email" placeholder="you@example.com">
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" name="login_password" required autocomplete="current-password" placeholder="••••••••">
        </div>
        <div class="form-options">
          <label><input type="checkbox" name="remember"> Remember Me</label>
          <a href="#">Forgot Password?</a>
        </div>
        <button class="btn btn-primary" type="submit" name="login_submit">Login</button>
        <p>Don't have an account? <a href="#" id="switch-to-signup">Sign up</a></p>
      </form>
    </div>
  </div>

  <!-- Signup Modal -->
  <div id="signup-modal" class="auth-modal <?php echo $signup_message ? '' : 'hidden'; ?>">
    <div class="auth-content">
      <span id="signup-close" class="close">&times;</span>
      <form method="POST" class="auth-form">
        <h2>Create Account</h2>
        <?php if ($signup_message) {
          $messageClass = strpos($signup_message, 'successful') !== false ? 'message-success' : 'message-error';
          echo "<div class='message $messageClass'>$signup_message</div>";
        } ?>
        <div class="input-group">
          <label>Email Address</label>
          <input type="email" name="signup_email" required autocomplete="email" placeholder="you@example.com">
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" name="signup_password" required autocomplete="new-password" placeholder="••••••••">
        </div>
        <button class="btn btn-primary" type="submit" name="signup_submit">Sign Up</button>
        <p>Already have an account? <a href="#" id="switch-to-login">Login</a></p>
      </form>
    </div>
  </div>

  <script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
          e.preventDefault();
          document.querySelector(href).scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Active nav link on scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links a[href^="#"]');

    window.addEventListener('scroll', () => {
      let current = '';
      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (scrollY >= (sectionTop - 200)) {
          current = section.getAttribute('id');
        }
      });

      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
          link.classList.add('active');
        }
      });
    });

    // Modal functionality
    const loginModal = document.getElementById('login-modal');
    const signupModal = document.getElementById('signup-modal');
    const openLogin = document.getElementById('open-login');
    const openSignup = document.getElementById('open-signup');
    const heroSignup = document.getElementById('hero-signup');
    const ctaSignup = document.getElementById('cta-signup');
    const loginClose = document.getElementById('login-close');
    const signupClose = document.getElementById('signup-close');
    const switchToSignup = document.getElementById('switch-to-signup');
    const switchToLogin = document.getElementById('switch-to-login');

    // Open modals
    openLogin?.addEventListener('click', e => {
      e.preventDefault();
      loginModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    });

    [openSignup, heroSignup, ctaSignup].forEach(btn => {
      btn?.addEventListener('click', e => {
        e.preventDefault();
        signupModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      });
    });

    // Close modals
    loginClose?.addEventListener('click', () => {
      loginModal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    });

    signupClose?.addEventListener('click', () => {
      signupModal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    });

    // Switch between modals
    switchToSignup?.addEventListener('click', e => {
      e.preventDefault();
      loginModal.classList.add('hidden');
      signupModal.classList.remove('hidden');
    });

    switchToLogin?.addEventListener('click', e => {
      e.preventDefault();
      signupModal.classList.add('hidden');
      loginModal.classList.remove('hidden');
    });

    // Click outside to close
    window.addEventListener('click', e => {
      if (e.target === loginModal) {
        loginModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
      if (e.target === signupModal) {
        signupModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
    });

    // Escape key to close modals
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        loginModal.classList.add('hidden');
        signupModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
    });

    // Animation on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .about-card, .how-card').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(30px)';
      el.style.transition = 'all 0.6s ease';
      observer.observe(el);
    });
  </script>
</body>
</html>