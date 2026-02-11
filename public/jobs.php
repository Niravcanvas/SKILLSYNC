<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
require_once __DIR__ . '/../app/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobs & Internships – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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
  margin: 0;
  padding: 0;
  box-sizing: border-box;
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

/* Jobs Section */
.jobs-section {
  display: flex;
  gap: 2rem;
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
}

/* Sidebar */
.jobs-sidebar {
  flex: 0 0 280px;
  position: sticky;
  top: 100px;
  height: fit-content;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.jobs-sidebar h2 {
  font-family: 'Sora', sans-serif;
  color: var(--text-light);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.job-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.jobs-sidebar button,
.jobs-sidebar select {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--gradient-1);
  color: white;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  font-family: 'Inter', sans-serif;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.jobs-sidebar button:hover,
.jobs-sidebar select:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}

#job-search {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  background: var(--bg-dark);
  color: var(--text-light);
  font-size: 0.95rem;
  font-family: 'Inter', sans-serif;
  transition: var(--transition);
}

#job-search:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

#job-search::placeholder {
  color: var(--text-gray);
}

/* Main Jobs Area */
.jobs-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Jobs Grid */
.jobs-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.job-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  padding: 1.75rem;
  transition: var(--transition);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
  overflow: hidden;
}

.job-card::before {
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

.job-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

.job-card:hover::before {
  opacity: 1;
}

.job-card h4 {
  font-family: 'Sora', sans-serif;
  color: var(--text-light);
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.job-card .badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
  background: var(--gradient-2);
  margin-right: 0.5rem;
}

.job-card p {
  color: var(--text-gray);
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0;
}

.job-card strong {
  color: var(--text-light);
  font-weight: 600;
}

.job-card small {
  color: var(--text-gray);
  font-size: 0.85rem;
}

.apply-btn {
  margin-top: auto;
  align-self: flex-start;
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--gradient-1);
  color: white;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.apply-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}

/* Modal */
.modal-bg {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-bg.active {
  display: flex;
}

.modal-content {
  background: var(--bg-card);
  border: 1px solid var(--border);
  padding: 2rem;
  border-radius: 1.5rem;
  width: 90%;
  max-width: 500px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
  position: relative;
}

.modal-content h3 {
  font-family: 'Sora', sans-serif;
  color: var(--text-light);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.modal-content input,
.modal-content textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  background: var(--bg-dark);
  color: var(--text-light);
  font-size: 0.95rem;
  font-family: 'Inter', sans-serif;
  margin-bottom: 1rem;
  transition: var(--transition);
}

.modal-content input:focus,
.modal-content textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.modal-content input::placeholder,
.modal-content textarea::placeholder {
  color: var(--text-gray);
}

.modal-content textarea {
  min-height: 100px;
  resize: vertical;
}

.close-modal {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  font-size: 1.75rem;
  font-weight: bold;
  cursor: pointer;
  color: var(--text-gray);
  transition: color 0.2s ease;
  background: none;
  border: none;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-modal:hover {
  color: var(--text-light);
}

.modal-content button[type="submit"] {
  width: 100%;
  padding: 0.875rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--gradient-1);
  color: white;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  margin-top: 0.5rem;
}

.modal-content button[type="submit"]:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}

/* Empty State */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  color: var(--text-gray);
  font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 900px) {
  .jobs-section {
    flex-direction: column;
    padding: 1.5rem;
  }
  
  .jobs-sidebar {
    position: relative;
    top: 0;
    width: 100%;
  }
  
  .jobs-container {
    grid-template-columns: 1fr;
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

.job-card {
  animation: fadeInUp 0.6s ease-out;
}
</style>
</head>
<body>
<div class="bg-pattern"></div>

<!-- Include Navbar -->
<?php include __DIR__ . '/../includes/partials/navbar.php'; ?>

<section class="jobs-section">
  <!-- Sidebar -->
  <div class="jobs-sidebar">
    <h2>Jobs Dashboard</h2>
    <input type="text" id="job-search" placeholder="🔍 Search jobs..." />
    <div class="job-actions">
      <button>💡 Suggest Jobs Based on Resume</button>
      <button id="post-job-btn">➕ Post a Job</button>
      <select id="sort-jobs">
        <option value="recent">📅 Sort by Recent</option>
        <option value="title">🔤 Sort by Title (A-Z)</option>
      </select>
    </div>
  </div>

  <!-- Main Jobs -->
  <div class="jobs-main">
    <div class="jobs-container" id="jobs-container">
      <?php
        try {
            $sql = "SELECT j.*, u.email 
                    FROM jobs j
                    LEFT JOIN users u ON j.user_id = u.id
                    ORDER BY j.id DESC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rows) {
                foreach ($rows as $row) {
                    $username = explode('@', $row['email'] ?? 'anonymous@skillsync.ai')[0];
                    echo '<div class="job-card">';
                    echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
                    echo '<p><span class="badge">' . $row['vacancies'] . ' Vacancy' . ($row['vacancies'] > 1 ? "ies" : "y") . '</span></p>';
                    echo '<p><strong>Company:</strong> ' . htmlspecialchars($row['company']) . '</p>';
                    echo '<p><strong>Position:</strong> ' . htmlspecialchars($row['position']) . '</p>';
                    echo '<p><strong>Working Hours:</strong> ' . htmlspecialchars($row['working_hours']) . '</p>';
                    echo '<p><strong>Contact:</strong> ' . htmlspecialchars($row['contact']) . '</p>';
                    echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p><small>Posted by: ' . htmlspecialchars($username) . ' on ' . $row['posted_on'] . '</small></p>';
                    echo '<button class="apply-btn">Apply Now</button>';
                    echo '</div>';
                }
            } else {
                echo '<div class="empty-state">No jobs available yet. Be the first to post one!</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="empty-state">Error loading jobs. Please try again later.</div>';
        }
      ?>
    </div>
  </div>
</section>

<!-- Modal -->
<div class="modal-bg" id="job-modal">
  <div class="modal-content">
    <button class="close-modal" id="close-job-modal">&times;</button>
    <h3>Post a New Job</h3>
    <form id="job-form">
      <input type="text" name="title" placeholder="Job Title *" required />
      <input type="text" name="company" placeholder="Company Name *" required />
      <input type="text" name="position" placeholder="Your Position in Company *" required />
      <input type="number" name="vacancies" placeholder="Number of Vacancies *" min="1" required />
      <input type="text" name="working_hours" placeholder="Working Hours (e.g., 9AM-5PM) *" required />
      <input type="tel" name="contact" placeholder="Contact Phone Number *" required />
      <textarea name="description" placeholder="Job Description *" required></textarea>
      <button type="submit" id="add-job-btn">Post Job</button>
    </form>
  </div>
</div>

<script>
const postJobBtn = document.getElementById('post-job-btn');
const jobModal = document.getElementById('job-modal');
const closeJobModal = document.getElementById('close-job-modal');
const jobForm = document.getElementById('job-form');
const jobsContainer = document.getElementById('jobs-container');
const jobSearch = document.getElementById('job-search');
const sortJobs = document.getElementById('sort-jobs');

// Toggle modal
function toggleModal(show) {
  jobModal.classList.toggle('active', show);
  document.body.style.overflow = show ? 'hidden' : 'auto';
}

// Open modal
postJobBtn.addEventListener('click', () => toggleModal(true));

// Close modal
closeJobModal.addEventListener('click', () => toggleModal(false));

// Close modal on outside click
jobModal.addEventListener('click', (e) => {
  if (e.target === jobModal) toggleModal(false);
});

// Job search
jobSearch.addEventListener('input', (e) => {
  const searchTerm = e.target.value.toLowerCase();
  const jobCards = document.querySelectorAll('.job-card');
  
  jobCards.forEach(card => {
    const text = card.textContent.toLowerCase();
    card.style.display = text.includes(searchTerm) ? 'flex' : 'none';
  });
});

// Job sorting
sortJobs.addEventListener('change', (e) => {
  const sortBy = e.target.value;
  const jobCards = Array.from(document.querySelectorAll('.job-card'));
  
  if (sortBy === 'title') {
    jobCards.sort((a, b) => {
      const titleA = a.querySelector('h4').textContent;
      const titleB = b.querySelector('h4').textContent;
      return titleA.localeCompare(titleB);
    });
    
    jobCards.forEach(card => jobsContainer.appendChild(card));
  }
});

// AJAX form submission
jobForm.addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(jobForm);
  const submitBtn = document.getElementById('add-job-btn');
  submitBtn.textContent = 'Posting...';
  submitBtn.disabled = true;

  fetch('../../app/controllers/job_post.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      toggleModal(false);
      jobForm.reset();

      const job = data.job;
      const div = document.createElement('div');
      div.className = 'job-card';
      div.style.animation = 'fadeInUp 0.6s ease-out';
      div.innerHTML = `
        <h4>${job.title}</h4>
        <p><span class="badge">${job.vacancies} Vacancy${job.vacancies > 1 ? 'ies' : 'y'}</span></p>
        <p><strong>Company:</strong> ${job.company}</p>
        <p><strong>Position:</strong> ${job.position}</p>
        <p><strong>Working Hours:</strong> ${job.working_hours}</p>
        <p><strong>Contact:</strong> ${job.contact}</p>
        <p>${job.description}</p>
        <p><small>Posted by: ${job.username} on ${job.posted_on}</small></p>
        <button class="apply-btn">Apply Now</button>
      `;
      jobsContainer.prepend(div);
    } else {
      alert(data.error || 'Something went wrong. Please try again.');
    }
  })
  .catch(err => {
    console.error(err);
    alert('Failed to post job. Please try again.');
  })
  .finally(() => {
    submitBtn.textContent = 'Post Job';
    submitBtn.disabled = false;
  });
});
</script>

</body>
</html>