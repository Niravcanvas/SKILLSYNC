<?php
// auth.php
session_start();
require __DIR__ . '/Templates/dbcon.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'signup') {
        $email = strtolower(trim($_POST['email']));
        $password = trim($_POST['password']);

        if (!$email || !$password) {
            $_SESSION['error'] = "Email and password are required";
            header("Location: index.php");
            exit;
        }

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email already registered";
            header("Location: index.php");
            exit;
        }

        // Insert user without hashing, set profile_complete=0
        $stmt = $pdo->prepare("INSERT INTO users (email, password, profile_complete) VALUES (?, ?, 0)");
        if ($stmt->execute([$email, $password])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header("Location: form.php"); // new user goes to profile form
            exit;
        } else {
            $_SESSION['error'] = "Signup failed, try again.";
            header("Location: index.php");
            exit;
        }

    } elseif ($action === 'login') {
        $email = strtolower(trim($_POST['email']));
        $password = trim($_POST['password']);

        if (!$email || !$password) {
            $_SESSION['error'] = "Email and password are required";
            header("Location: index.php");
            exit;
        }

        // Check credentials
        $stmt = $pdo->prepare("SELECT id, password, profile_complete FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id'];

            // Redirect based on profile completion
            if ($user['profile_complete'] == 0) {
                header("Location: form.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Incorrect email or password";
            header("Location: index.php");
            exit;
        }

    } else {
        $_SESSION['error'] = "Invalid action";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['error'] = "No action specified";
    header("Location: index.php");
    exit;
}
?>