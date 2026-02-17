<?php
// database.php — supports local .env and Docker environment variables

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Load .env file if it exists (local / XAMPP dev) ──────────────────────────
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!isset($_ENV[$key]) && !getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// ── Resolve config — Docker env vars take priority over .env ─────────────────
$host   = getenv('DB_HOST')  ?: 'localhost';
$dbname = getenv('DB_NAME')  ?: 'skillsync_ai';
$user   = getenv('DB_USER')  ?: 'root';
$pass   = getenv('DB_PASS')  ?: '';

// ── Connect ───────────────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,        PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}