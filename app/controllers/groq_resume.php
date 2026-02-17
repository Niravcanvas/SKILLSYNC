<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

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

// Get form data
$name = $_POST['name'] ?? '';
$education = $_POST['education'] ?? '';
$skills = $_POST['skills'] ?? '';
$experience = $_POST['experience'] ?? '';
$strengths = $_POST['strengths'] ?? '';

// Build context for AI
$context = "Generate a professional career objective for this person:\n\n";
$context .= "Name: $name\n";
if ($education) $context .= "Education: $education\n";
if ($skills) $context .= "Skills: $skills\n";
if ($experience) $context .= "Experience: $experience\n";
if ($strengths) $context .= "Strengths: $strengths\n";
$context .= "\nWrite a concise, professional career objective (2-3 sentences) that highlights their skills and goals.";

// Call Groq API
try {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a professional resume writer. Generate career objectives that are concise, impactful, and ATS-friendly. Keep them to 2-3 sentences maximum.'
            ],
            [
                'role' => 'user',
                'content' => $context
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 200,
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
    $careerObjective = $result['choices'][0]['message']['content'] ?? '';
    
    if (empty($careerObjective)) {
        throw new Exception('No response from AI');
    }
    
    echo json_encode([
        'success' => true,
        'career_objective' => trim($careerObjective)
    ]);
    
} catch (Exception $e) {
    error_log('Groq Resume AI Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'AI generation failed. Please try again.'
    ]);
}