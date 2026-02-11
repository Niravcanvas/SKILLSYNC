<?php
session_start();
require __DIR__ . '/dbcon.php'; // Make sure this points to your db connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: ../form.php"); // go up one directory
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// Handle profile picture upload
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $upload_dir = __DIR__ . '/../uploads/'; // uploads folder in root Skillsync/
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $file_name = 'user_' . $user_id . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
        $errors[] = "Failed to upload profile picture.";
    } else {
        $profile_picture = 'uploads/' . $file_name; // relative path to use in HTML
    }
}

// Collect personal info
$full_name = $_POST['full_name'] ?? '';
$headline  = $_POST['headline'] ?? '';
$location  = $_POST['location'] ?? '';
$email     = $_POST['email'] ?? '';
$phone     = $_POST['phone'] ?? '';
$bio       = $_POST['bio'] ?? '';

try {
    $pdo->beginTransaction();

    // Update users table
    $stmt = $pdo->prepare("UPDATE users SET full_name=?, headline=?, location=?, email=?, phone=?, bio=?, profile_picture=?, profile_complete=1 WHERE id=?");
    $stmt->execute([$full_name, $headline, $location, $email, $phone, $bio, $profile_picture, $user_id]);

    // Clear old related data
    $tables = ['education', 'experience', 'skills', 'certifications', 'projects'];
    foreach ($tables as $table) {
        $pdo->prepare("DELETE FROM $table WHERE user_id=?")->execute([$user_id]);
    }

    // Insert Education
    if (!empty($_POST['education_institution'])) {
        $edu_count = count($_POST['education_institution']);
        $stmt = $pdo->prepare("INSERT INTO education (user_id, institution, degree, field_of_study, start_year, end_year) VALUES (?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < $edu_count; $i++) {
            $stmt->execute([
                $user_id,
                $_POST['education_institution'][$i],
                $_POST['education_degree'][$i],
                $_POST['education_field'][$i],
                $_POST['education_start'][$i],
                $_POST['education_end'][$i]
            ]);
        }
    }

    // Insert Experience
    if (!empty($_POST['experience_company'])) {
        $exp_count = count($_POST['experience_company']);
        $stmt = $pdo->prepare("INSERT INTO experience (user_id, company, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < $exp_count; $i++) {
            $stmt->execute([
                $user_id,
                $_POST['experience_company'][$i],
                $_POST['experience_position'][$i],
                $_POST['experience_start'][$i] ?: null,
                $_POST['experience_end'][$i] ?: null,
                $_POST['experience_description'][$i]
            ]);
        }
    }

    // Insert Skills
    if (!empty($_POST['skills'])) {
        $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)");
        foreach ($_POST['skills'] as $skill) {
            if ($skill) $stmt->execute([$user_id, $skill]);
        }
    }

    // Insert Certifications
    if (!empty($_POST['cert_title'])) {
        $cert_count = count($_POST['cert_title']);
        $stmt = $pdo->prepare("INSERT INTO certifications (user_id, title, issuer, cert_date, url) VALUES (?, ?, ?, ?, ?)");
        for ($i = 0; $i < $cert_count; $i++) {
            $stmt->execute([
                $user_id,
                $_POST['cert_title'][$i],
                $_POST['cert_issuer'][$i],
                $_POST['cert_date'][$i] ?: null,
                $_POST['cert_url'][$i]
            ]);
        }
    }

    // Insert Projects
    if (!empty($_POST['project_name'])) {
        $proj_count = count($_POST['project_name']);
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, project_name, description, technologies, project_url) VALUES (?, ?, ?, ?, ?)");
        for ($i = 0; $i < $proj_count; $i++) {
            $stmt->execute([
                $user_id,
                $_POST['project_name'][$i],
                $_POST['project_description'][$i],
                $_POST['project_tech'][$i],
                $_POST['project_url'][$i]
            ]);
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "Profile saved successfully!";
    header("Location: ../dashboard.php"); // fixed relative path
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error saving profile: " . $e->getMessage();
    header("Location: ../form.php"); // fixed relative path
    exit;
}
?>