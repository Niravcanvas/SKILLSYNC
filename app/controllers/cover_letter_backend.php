<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'];

// Load environment variables
$envFile = __DIR__ . '/../config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

$groqApiKey = getenv('GROQ_API_KEY') ?: '';
if (empty($groqApiKey)) {
    echo json_encode(['error' => 'Groq API key not configured']);
    exit;
}

// Get user data for personalization
try {
    $stmt = $pdo->prepare("SELECT full_name, headline, bio FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("SELECT company, position, description FROM experience WHERE user_id = ? ORDER BY start_date DESC LIMIT 2");
    $stmt->execute([$user_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $user = ['full_name' => 'Candidate', 'headline' => '', 'bio' => ''];
    $skills = [];
    $experience = [];
}

// Get form data
$job_title = $_POST['job_title'] ?? '';
$company = $_POST['company'] ?? '';
$job_description = $_POST['job_description'] ?? '';
$tone = $_POST['tone'] ?? 'professional';

if (empty($job_title) || empty($company) || empty($job_description)) {
    echo json_encode(['error' => 'Please fill in all required fields']);
    exit;
}

// Build prompt
$prompt = "Write a cover letter for the following job application:\n\n";
$prompt .= "Candidate Name: " . ($user['full_name'] ?? 'Candidate') . "\n";
if (!empty($user['headline'])) $prompt .= "Current Role: " . $user['headline'] . "\n";
if (!empty($skills)) $prompt .= "Skills: " . implode(', ', array_slice($skills, 0, 10)) . "\n";
if (!empty($experience)) {
    $prompt .= "Recent Experience:\n";
    foreach ($experience as $exp) {
        $prompt .= "- " . $exp['position'] . " at " . $exp['company'] . "\n";
    }
}
$prompt .= "\nJob Title: $job_title\n";
$prompt .= "Company: $company\n";
$prompt .= "Job Description: $job_description\n\n";
$prompt .= "Tone: $tone\n\n";
$prompt .= "Write a compelling cover letter that:\n";
$prompt .= "1. Shows enthusiasm for the role and company\n";
$prompt .= "2. Highlights relevant experience and skills\n";
$prompt .= "3. Explains why I'm a great fit\n";
$prompt .= "4. Is professional, concise (250-300 words)\n";
$prompt .= "5. Includes proper salutation and closing\n";

// Call Groq API
try {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an expert career coach and professional writer. Write compelling, personalized cover letters that help candidates stand out. Be specific, authentic, and professional.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 800,
        'top_p' => 1,
        'stream' => false
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $groqApiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('API Error: ' . $httpCode);
    }
    
    $result = json_decode($response, true);
    $coverLetter = $result['choices'][0]['message']['content'] ?? '';
    
    if (empty($coverLetter)) {
        throw new Exception('No response from AI');
    }
    
    echo json_encode([
        'success' => true,
        'cover_letter' => trim($coverLetter)
    ]);
    
} catch (Exception $e) {
    error_log('Groq Cover Letter Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'AI generation failed. Please try again.'
    ]);
}