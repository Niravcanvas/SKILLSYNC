<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// Correct path: public/ → up one → app/config/
require_once __DIR__ . '/../app/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$title         = trim($_POST['title']         ?? '');
$company       = trim($_POST['company']       ?? '');
$position      = trim($_POST['position']      ?? '');
$vacancies     = (int)($_POST['vacancies']    ?? 1);
$working_hours = trim($_POST['working_hours'] ?? '');
$contact       = trim($_POST['contact']       ?? '');
$description   = trim($_POST['description']   ?? '');
$location      = trim($_POST['location']      ?? 'Not Specified');
$type          = trim($_POST['type']          ?? 'Full-time');
$salary        = trim($_POST['salary']        ?? 'Negotiable');
$user_id       = $_SESSION['user_id']; // ← actual logged-in user

if (empty($title) || empty($company) || empty($position) || empty($contact)) {
    echo json_encode(["success" => false, "error" => "Required fields missing"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO jobs
          (user_id, title, company, position, working_hours, contact,
           description, vacancies, location, type, salary, posted_on)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
    ");
    $stmt->execute([
        $user_id, $title, $company, $position, $working_hours,
        $contact, $description, $vacancies, $location, $type, $salary
    ]);

    $job_id = $pdo->lastInsertId();

    $res = $pdo->prepare("
        SELECT j.*, u.full_name AS poster
        FROM jobs j
        LEFT JOIN users u ON j.user_id = u.id
        WHERE j.id = ?
    ");
    $res->execute([$job_id]);
    $job = $res->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "job" => $job]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "DB error: " . $e->getMessage()]);
}