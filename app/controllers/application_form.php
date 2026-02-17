<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = trim($_POST['job_title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $status = $_POST['status'] ?? 'Applied';
    $date_applied = $_POST['date_applied'] ?? date('Y-m-d');
    $salary = trim($_POST['salary'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $job_url = trim($_POST['job_url'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($job_title) || empty($company)) {
        $message = 'Job title and company are required';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO applications 
                (user_id, job_title, company, status, date_applied, salary, location, job_url, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$user_id, $job_title, $company, $status, $date_applied, $salary, $location, $job_url, $notes])) {
                header('Location: ../views/applications.php');
                exit;
            } else {
                $message = 'Failed to save application';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Application – Skillsync AI</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="../../public/images/favicon.svg">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --primary: #6366f1; --bg: #080e1a; --bg-card: #111827; --bg-input: #0d1424; --bg-lift: #161f31;
  --border: #1f2d45; --border-lit: #2e3f5e; --text: #f1f5f9; --muted: #64748b;
  --g1: linear-gradient(135deg, #6366f1, #8b5cf6);
}
html { scroll-behavior: smooth; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding-top: 80px; }
body::before {
  content: ''; position: fixed; inset: 0; z-index: -1;
  background: radial-gradient(ellipse 60% 40% at 10% 10%, rgba(99,102,241,.07) 0%, transparent 70%);
}
.wrap { max-width: 700px; margin: 0 auto; padding: 2.5rem 2rem; }
.card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 1.25rem; padding: 2.5rem; position: relative; overflow: hidden;
}
.card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--g1);
}
.card-title {
  font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 700;
  margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
}
.form-group { margin-bottom: 1.25rem; }
.form-group label {
  display: block; margin-bottom: .5rem;
  font-size: .88rem; font-weight: 500; color: var(--text);
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
input, textarea, select {
  width: 100%; background: var(--bg-input); border: 1px solid var(--border);
  border-radius: .75rem; padding: .75rem 1rem; font-size: .88rem;
  font-family: 'Inter', sans-serif; color: var(--text);
  transition: all .3s;
}
input:focus, textarea:focus, select:focus {
  outline: none; border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
textarea { resize: vertical; min-height: 100px; }
select { cursor: pointer; }
.btn-group { display: flex; gap: 1rem; margin-top: 2rem; }
.btn {
  flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
  padding: .85rem 1.5rem; font-weight: 600; font-size: .88rem;
  border: none; border-radius: .85rem; cursor: pointer; text-decoration: none;
  transition: transform .25s, box-shadow .25s;
}
.btn-primary { background: var(--g1); color: #fff; box-shadow: 0 4px 16px rgba(99,102,241,.3); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(99,102,241,.5); }
.btn-secondary { background: var(--bg-lift); color: var(--text); border: 1px solid var(--border); }
.btn-secondary:hover { background: var(--bg-card); border-color: var(--border-lit); }

.message {
  padding: 1rem; border-radius: .75rem; margin-bottom: 1.5rem; font-weight: 500;
}
.message-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

@media (max-width: 640px) {
  .form-row { grid-template-columns: 1fr; }
  .btn-group { flex-direction: column; }
}
</style>
</head>
<body>

<?php include __DIR__ . '/../../includes/partials/navbar.php'; ?>

<div class="wrap">
  <div class="card">
    <div class="card-title">Add Job Application</div>
    
    <?php if ($message): ?>
    <div class="message message-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label>Job Title *</label>
          <input type="text" name="job_title" required placeholder="e.g., Senior Developer">
        </div>
        <div class="form-group">
          <label>Company *</label>
          <input type="text" name="company" required placeholder="e.g., Google">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="Applied">Applied</option>
            <option value="Interview">Interview</option>
            <option value="Offer">Offer</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
        <div class="form-group">
          <label>Date Applied</label>
          <input type="date" name="date_applied" value="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Salary</label>
          <input type="text" name="salary" placeholder="e.g., $120k - $150k">
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" placeholder="e.g., Remote / San Francisco">
        </div>
      </div>

      <div class="form-group">
        <label>Job URL</label>
        <input type="text" name="job_url" placeholder="https://...">
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" placeholder="Add any notes about this application..."></textarea>
      </div>

      <div class="btn-group">
        <a href="../views/applications.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
          </svg>
          Save Application
        </button>
      </div>
    </form>
  </div>
</div>

</body>
</html>