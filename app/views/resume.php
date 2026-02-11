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
    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Initialize variables from database or session
    $name = $user['name'] ?? $_SESSION['name'] ?? '';
    $email = $user['email'] ?? $_SESSION['email'] ?? '';
    $phone = $_SESSION['phone'] ?? '';
    $location = $_SESSION['location'] ?? '';
    $linkedin = $_SESSION['linkedin'] ?? '';
    $education = $_SESSION['education'] ?? '';
    $skills = $_SESSION['skills'] ?? '';
    $experience = $_SESSION['experience'] ?? '';
    $career_objective = $_SESSION['career_objective'] ?? '';
    $strengths = $_SESSION['strengths'] ?? '';
    $technical = $_SESSION['technical'] ?? '';
    $interests = $_SESSION['interests'] ?? '';
    $languages = $_SESSION['languages'] ?? '';
    
    // Styling variables
    $accent_color = $_SESSION['accent_color'] ?? '#667eea';
    $text_color = $_SESSION['text_color'] ?? '#222222';
    $bg_color = $_SESSION['bg_color'] ?? '#ffffff';
    $font_family = $_SESSION['font_family'] ?? 'Crimson Pro';
    $font_scale = $_SESSION['font_scale'] ?? 1;
    $divider_style = $_SESSION['divider_style'] ?? 'solid';
    $divider_thickness = $_SESSION['divider_thickness'] ?? 2;
    $bw_theme = $_SESSION['bw_theme'] ?? false;
    $profile_frame = $_SESSION['profile_frame'] ?? 'circle';
    $profile_img = $_SESSION['profile_img'] ?? '';
    
} catch (PDOException $e) {
    // If database fails, use session defaults
    $name = $_SESSION['name'] ?? '';
    $email = $_SESSION['email'] ?? '';
    $phone = $_SESSION['phone'] ?? '';
    $location = $_SESSION['location'] ?? '';
    $linkedin = $_SESSION['linkedin'] ?? '';
    $education = $_SESSION['education'] ?? '';
    $skills = $_SESSION['skills'] ?? '';
    $experience = $_SESSION['experience'] ?? '';
    $career_objective = $_SESSION['career_objective'] ?? '';
    $strengths = $_SESSION['strengths'] ?? '';
    $technical = $_SESSION['technical'] ?? '';
    $interests = $_SESSION['interests'] ?? '';
    $languages = $_SESSION['languages'] ?? '';
    $accent_color = $_SESSION['accent_color'] ?? '#667eea';
    $text_color = $_SESSION['text_color'] ?? '#222222';
    $bg_color = $_SESSION['bg_color'] ?? '#ffffff';
    $font_family = $_SESSION['font_family'] ?? 'Crimson Pro';
    $font_scale = $_SESSION['font_scale'] ?? 1;
    $divider_style = $_SESSION['divider_style'] ?? 'solid';
    $divider_thickness = $_SESSION['divider_thickness'] ?? 2;
    $bw_theme = $_SESSION['bw_theme'] ?? false;
    $profile_frame = $_SESSION['profile_frame'] ?? 'circle';
    $profile_img = $_SESSION['profile_img'] ?? '';
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle AI Refresh
    if (isset($_POST['refresh_ai'])) {
        header('Content-Type: application/json');
        
        $user_name = $_POST['name'] ?? '';
        $user_skills = $_POST['skills'] ?? '';
        $user_education = $_POST['education'] ?? '';
        $user_experience = $_POST['experience'] ?? '';
        
        // Generate AI-powered career objective
        $skills_array = array_filter(array_map('trim', explode(',', $user_skills)));
        $main_skills = implode(', ', array_slice($skills_array, 0, 3));
        
        if (!empty($main_skills)) {
            $ai_objective = "Highly motivated and results-driven professional with proven expertise in {$main_skills}. " .
                           "Seeking to leverage technical proficiency and hands-on experience to contribute to " .
                           "innovative projects and drive organizational success in a dynamic, growth-oriented environment.";
        } else {
            $ai_objective = "Dedicated and ambitious professional seeking opportunities to apply knowledge, " .
                           "develop new skills, and contribute to meaningful projects in a collaborative team environment.";
        }
        
        echo json_encode([
            'success' => true,
            'career_objective' => $ai_objective
        ]);
        exit;
    }
    
    // Handle regular form submission
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $education = $_POST['education'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $career_objective = $_POST['career_objective'] ?? '';
    $strengths = $_POST['strengths'] ?? '';
    $technical = $_POST['technical'] ?? '';
    $interests = $_POST['interests'] ?? '';
    $languages = $_POST['languages'] ?? '';
    
    // Styling
    $accent_color = $_POST['accent_color'] ?? '#667eea';
    $text_color = $_POST['text_color'] ?? '#222222';
    $bg_color = $_POST['bg_color'] ?? '#ffffff';
    $font_family = $_POST['font_family'] ?? 'Crimson Pro';
    $font_scale = $_POST['font_scale'] ?? 1;
    $divider_style = $_POST['divider_style'] ?? 'solid';
    $divider_thickness = $_POST['divider_thickness'] ?? 2;
    $bw_theme = isset($_POST['bw_theme']) ? 1 : 0;
    $profile_frame = $_POST['profile_frame'] ?? 'circle';
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        // Create upload directory if it doesn't exist
        $upload_dir = __DIR__ . '/../../public/uploads/profiles/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            $file_name = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $profile_img = '../../public/uploads/profiles/' . $file_name;
                
                // Save to database
                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$profile_img, $_SESSION['user_id']]);
                } catch (PDOException $e) {
                    // If database update fails, continue with session storage
                }
            }
        }
    }
    
    // Save everything to session
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['location'] = $location;
    $_SESSION['linkedin'] = $linkedin;
    $_SESSION['education'] = $education;
    $_SESSION['skills'] = $skills;
    $_SESSION['experience'] = $experience;
    $_SESSION['career_objective'] = $career_objective;
    $_SESSION['strengths'] = $strengths;
    $_SESSION['technical'] = $technical;
    $_SESSION['interests'] = $interests;
    $_SESSION['languages'] = $languages;
    $_SESSION['accent_color'] = $accent_color;
    $_SESSION['text_color'] = $text_color;
    $_SESSION['bg_color'] = $bg_color;
    $_SESSION['font_family'] = $font_family;
    $_SESSION['font_scale'] = $font_scale;
    $_SESSION['divider_style'] = $divider_style;
    $_SESSION['divider_thickness'] = $divider_thickness;
    $_SESSION['bw_theme'] = $bw_theme;
    $_SESSION['profile_frame'] = $profile_frame;
    if (!empty($profile_img)) {
        $_SESSION['profile_img'] = $profile_img;
    }
    
    // Handle PDF download
    if (isset($_POST['download_pdf'])) {
        // TODO: Implement PDF generation using TCPDF or similar
        // For now, just reload the page
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Save to database (optional - for persistence)
    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                phone = ?,
                location = ?,
                linkedin = ?,
                education = ?,
                skills = ?,
                experience = ?,
                career_objective = ?,
                strengths = ?,
                technical = ?,
                interests = ?,
                languages = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $phone, $location, $linkedin, $education, $skills,
            $experience, $career_objective, $strengths, $technical,
            $interests, $languages, $_SESSION['user_id']
        ]);
    } catch (PDOException $e) {
        // If database save fails, data is still in session
        error_log("Failed to save resume data: " . $e->getMessage());
    }
}
?>