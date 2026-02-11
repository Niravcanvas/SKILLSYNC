<?php
include __DIR__ . "/Templates/dbcon.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = $_POST['title'] ?? '';
    $company       = $_POST['company'] ?? '';
    $position      = $_POST['position'] ?? '';
    $vacancies     = $_POST['vacancies'] ?? 1;
    $working_hours = $_POST['working_hours'] ?? '';
    $contact       = $_POST['contact'] ?? '';
    $description   = $_POST['description'] ?? '';
    $user_id       = 1; // replace later with logged-in user

    if (empty($title) || empty($company) || empty($position) || empty($contact)) {
        echo json_encode(["success" => false, "error" => "All fields are required"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO jobs 
            (user_id, title, company, position, working_hours, contact, description, vacancies, posted_on) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $company, $position, $working_hours, $contact, $description, $vacancies]);

        $job_id = $pdo->lastInsertId();
        $res = $pdo->prepare("SELECT j.*, u.full_name AS username 
                              FROM jobs j 
                              LEFT JOIN users u ON j.user_id = u.id 
                              WHERE j.id = ?");
        $res->execute([$job_id]);
        $job = $res->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "job" => $job]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "DB insert failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
