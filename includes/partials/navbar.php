<?php
// Navbar component - to be included in other pages
// This file should be placed at: /includes/partials/navbar.php

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Determine base path based on current location
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/app/views/') !== false) {
    $base_path = '../../';
} elseif (strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $base_path = '../';
}
?>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-container">
    <a href="<?= $base_path ?>index.php" class="logo">
      Skillsync <span class="accent">AI</span>
    </a>
    <ul class="nav-links">
      <li>
        <a href="<?= $base_path ?>app/views/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-4v-7h-6v7H5a2 2 0 0 1-2-2z"/>
          </svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= $base_path ?>app/views/resume-builder.php" class="<?= $current_page == 'resume-builder.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <line x1="10" y1="9" x2="8" y2="9"/>
          </svg>
          <span>Resume</span>
        </a>
      </li>
      <li>
        <a href="<?= $base_path ?>public/jobs.php" class="<?= $current_page == 'jobs.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          <span>Jobs</span>
        </a>
      </li>
      <li>
        <a href="<?= $base_path ?>app/views/profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Profile</span>
        </a>
      </li>
      <li>
        <a href="<?= $base_path ?>public/chatbot.php" class="btn-ai <?= $current_page == 'chatbot.php' || $current_page == 'Chatbot.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            <line x1="9" y1="10" x2="15" y2="10"/>
            <line x1="9" y1="14" x2="13" y2="14"/>
          </svg>
          <span>AI Assistant</span>
        </a>
      </li>
      <li>
        <a href="<?= $base_path ?>public/logout.php" class="btn-logout">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>

<style>
/* Navbar Styles */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
}

.navbar:hover {
  background: rgba(15, 23, 42, 0.98);
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0.8rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Logo */
.navbar .logo {
  font-family: 'Sora', sans-serif;
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--text-light);
  text-decoration: none;
  letter-spacing: -0.02em;
  transition: transform 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.navbar .logo:hover {
  transform: scale(1.05);
}

.navbar .logo .accent {
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Navigation Links */
.nav-links {
  display: flex;
  gap: 0.5rem;
  list-style: none;
  align-items: center;
}

.nav-links li {
  position: relative;
}

.nav-links a {
  text-decoration: none;
  color: var(--text-gray);
  font-weight: 500;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.2rem;
  border-radius: 0.65rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

/* Hover effect with sliding background */
.nav-links a::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0.15) 100%);
  transition: left 0.4s ease;
  z-index: -1;
  border-radius: 0.65rem;
}

.nav-links a:hover::before {
  left: 0;
}

.nav-links a:hover {
  color: var(--text-light);
  transform: translateY(-2px);
}

.nav-links a svg {
  transition: all 0.3s ease;
}

.nav-links a:hover svg {
  transform: scale(1.1);
  filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.4));
}

/* Active state */
.nav-links a.active {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0.3) 100%);
  color: var(--primary-light);
  font-weight: 600;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
}

.nav-links a.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
}

/* AI Assistant Button */
.btn-ai {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white !important;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  position: relative;
  overflow: hidden;
}

.btn-ai::before {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.2) 100%);
}

.btn-ai:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
  color: white !important;
}

.btn-ai.active {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  box-shadow: 0 0 30px rgba(102, 126, 234, 0.6);
}

/* Logout Button */
.btn-logout {
  color: #f87171 !important;
  border: 1px solid rgba(248, 113, 113, 0.3);
}

.btn-logout::before {
  background: linear-gradient(135deg, rgba(248, 113, 113, 0.1) 0%, rgba(248, 113, 113, 0.15) 100%);
}

.btn-logout:hover {
  border-color: #f87171;
  background: rgba(248, 113, 113, 0.1);
  color: #fca5a5 !important;
}

/* Icon animations */
@keyframes iconBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

.nav-links a:hover svg {
  animation: iconBounce 0.6s ease;
}

/* Pulse animation for AI button when active */
@keyframes pulse {
  0%, 100% {
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }
  50% {
    box-shadow: 0 4px 25px rgba(102, 126, 234, 0.6);
  }
}

.btn-ai.active {
  animation: pulse 2s ease-in-out infinite;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .nav-container {
    padding: 0.8rem 1.5rem;
  }
  
  .nav-links {
    gap: 0.3rem;
  }
  
  .nav-links a {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
  }
  
  .navbar .logo {
    font-size: 1.4rem;
  }
}

@media (max-width: 768px) {
  .nav-links a span {
    display: none;
  }
  
  .nav-links a {
    padding: 0.7rem;
    justify-content: center;
  }
  
  .nav-links {
    gap: 0.25rem;
  }
  
  .nav-container {
    padding: 0.8rem 1rem;
  }
}

@media (max-width: 480px) {
  .navbar .logo {
    font-size: 1.2rem;
  }
  
  .nav-links a svg {
    width: 16px;
    height: 16px;
  }
  
  .nav-links a {
    padding: 0.6rem;
  }
}

/* Loading state for navbar */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.navbar {
  animation: fadeIn 0.5s ease;
}
</style>