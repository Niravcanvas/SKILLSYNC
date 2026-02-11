<?php
// dbcon.php

// Start session only if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = "localhost";     
$dbname = "skillsync_ai"; 
$user = "root";           
$pass = "";              

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Stop script and display error in JSON format if used with fetch
    header('Content-Type: application/json');
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
?>