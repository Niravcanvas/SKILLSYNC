<?php
// Auto-detect the base path based on current file location
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/app/views/') !== false) {
    $base_path = '../../';
} elseif (strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $base_path = '../';
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
  <div class="nav-container">
    <a href="<?= $base_path ?>app/views/dashboard.php" class="logo">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
      Skillsync AI
    </a>

    <!-- Mobile menu button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 12h18M3 6h18M3 18h18"/>
      </svg>
    </button>

    <div class="nav-links" id="navLinks">
      <a href="<?= $base_path ?>app/views/dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
          <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span>Dashboard</span>
      </a>

      <a href="<?= $base_path ?>app/views/resume-builder.php" class="<?= $current_page == 'resume-builder.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <span>Resume</span>
      </a>

      <a href="<?= $base_path ?>public/jobs.php" class="<?= $current_page == 'jobs.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
          <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
        </svg>
        <span>Jobs</span>
      </a>

      <a href="<?= $base_path ?>app/views/applications.php" class="<?= $current_page == 'applications.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M9 11l3 3L22 4"/>
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        </svg>
        <span>Applications</span>
      </a>

      <a href="<?= $base_path ?>app/views/cover-letter.php" class="<?= $current_page == 'cover-letter.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        <span>Cover Letter</span>
      </a>

      <a href="<?= $base_path ?>app/views/skill-gap.php" class="<?= $current_page == 'skill-gap.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        <span>Skill Gap</span>
      </a>

      <a href="<?= $base_path ?>public/chatbot.php" class="<?= $current_page == 'chatbot.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
          <circle cx="12" cy="17" r=".5" fill="currentColor"/>
        </svg>
        <span>AI Chat</span>
      </a>

      <a href="<?= $base_path ?>app/views/profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <span>Profile</span>
      </a>

      <a href="<?= $base_path ?>public/logout.php" class="logout-btn">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Logout</span>
      </a>
    </div>
  </div>
</nav>

<style>
.navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  background: rgba(17, 24, 39, 0.85); backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border, #1f2d45);
  height: 80px;
}

.nav-container {
  max-width: 1400px; margin: 0 auto; height: 100%;
  padding: 0 2rem;
  display: flex; align-items: center; justify-content: space-between;
}

.logo {
  font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.3rem;
  display: flex; align-items: center; gap: 0.6rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; text-decoration: none;
  transition: transform 0.3s; z-index: 2;
}
.logo:hover { transform: translateY(-2px); }
.logo svg { stroke: #6366f1; }

.mobile-menu-btn {
  display: none; background: none; border: none;
  color: var(--text, #f1f5f9); cursor: pointer;
  padding: 0.5rem; z-index: 2;
}
.mobile-menu-btn svg { stroke: var(--text, #f1f5f9); }

.nav-links {
  display: flex; align-items: center; gap: 0.3rem;
}

.nav-links a {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.65rem 1rem;
  color: var(--muted, #64748b);
  font-size: 0.85rem; font-weight: 500;
  text-decoration: none;
  border-radius: 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.nav-links a svg {
  flex-shrink: 0;
  transition: transform 0.3s;
}

.nav-links a:hover {
  color: var(--text, #f1f5f9);
  background: var(--bg-lift, #161f31);
}

.nav-links a:hover svg {
  transform: translateY(-2px);
}

.nav-links a.active {
  color: #fff;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.nav-links a.active svg {
  stroke: #fff;
}

.logout-btn {
  margin-left: 0.5rem;
  color: var(--secondary, #ec4899) !important;
  border: 1px solid rgba(236, 72, 153, 0.2);
}

.logout-btn:hover {
  background: rgba(236, 72, 153, 0.1) !important;
  border-color: rgba(236, 72, 153, 0.4);
}

/* Mobile styles */
@media (max-width: 1024px) {
  .nav-links { gap: 0.2rem; }
  .nav-links a { padding: 0.6rem 0.85rem; font-size: 0.82rem; }
}

@media (max-width: 900px) {
  .mobile-menu-btn { display: block; }
  
  .nav-links {
    position: fixed;
    top: 80px;
    left: 0;
    right: 0;
    background: rgba(17, 24, 39, 0.98);
    backdrop-filter: blur(12px);
    flex-direction: column;
    align-items: stretch;
    gap: 0;
    padding: 1rem;
    border-bottom: 1px solid var(--border, #1f2d45);
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.3s ease, opacity 0.3s ease;
  }
  
  .nav-links.active {
    max-height: 600px;
    opacity: 1;
  }
  
  .nav-links a {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
  }
  
  .logout-btn {
    margin-left: 0;
    margin-top: 0.5rem;
    border-top: 1px solid var(--border, #1f2d45);
    padding-top: 1.25rem !important;
  }
}

@media (max-width: 768px) {
  .nav-container { padding: 0 1rem; }
  .logo { font-size: 1.1rem; }
  .logo svg { width: 20px; height: 20px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const navLinks = document.getElementById('navLinks');
  
  mobileMenuBtn?.addEventListener('click', function() {
    navLinks.classList.toggle('active');
  });
  
  // Close menu when clicking a link
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 900) {
        navLinks.classList.remove('active');
      }
    });
  });
  
  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 900 && 
        !navLinks.contains(e.target) && 
        !mobileMenuBtn.contains(e.target) &&
        navLinks.classList.contains('active')) {
      navLinks.classList.remove('active');
    }
  });
});
</script>