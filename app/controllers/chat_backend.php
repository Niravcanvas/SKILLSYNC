<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Load environment variables from .env file
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

// Get Groq API key
$groqApiKey = getenv('GROQ_API_KEY') ?: '';
if (empty($groqApiKey)) {
    echo json_encode(['error' => 'Groq API key not configured']);
    exit;
}

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
if (empty($userMessage)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Connect to database
require_once __DIR__ . '/../config/database.php';

// Fetch user data for context
try {
    // Get user profile
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get education
    $stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$userId]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get experience
    $stmt = $pdo->prepare("SELECT * FROM experience WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$userId]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get skills
    $stmt = $pdo->prepare("SELECT skill_name, proficiency_level FROM skills WHERE user_id = ?");
    $stmt->execute([$userId]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get certifications
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE user_id = ?");
    $stmt->execute([$userId]);
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get projects
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ?");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build user context
    $userContext = "User Profile:\n";
    $userContext .= "Name: " . ($user['full_name'] ?? 'Not provided') . "\n";
    $userContext .= "Email: " . ($user['email'] ?? '') . "\n";
    
    if ($profile) {
        $userContext .= "Phone: " . ($profile['phone'] ?? 'Not provided') . "\n";
        $userContext .= "Location: " . ($profile['address'] ?? 'Not provided') . "\n";
        $userContext .= "Career Level: " . ($profile['career_level'] ?? 'Not specified') . "\n";
        $userContext .= "Job Title: " . ($profile['job_title'] ?? 'Not specified') . "\n";
        if (!empty($profile['bio'])) {
            $userContext .= "Bio: " . $profile['bio'] . "\n";
        }
    }
    
    // Education
    if (!empty($education)) {
        $userContext .= "\nEducation:\n";
        foreach ($education as $edu) {
            $userContext .= "- " . $edu['degree'] . " in " . $edu['field_of_study'];
            $userContext .= " at " . $edu['institution'];
            $userContext .= " (" . date('Y', strtotime($edu['start_date']));
            if ($edu['is_current']) {
                $userContext .= " - Present)\n";
            } else {
                $userContext .= " - " . date('Y', strtotime($edu['end_date'])) . ")\n";
            }
        }
    }
    
    // Experience
    if (!empty($experience)) {
        $userContext .= "\nWork Experience:\n";
        foreach ($experience as $exp) {
            $userContext .= "- " . $exp['job_title'] . " at " . $exp['company'];
            $userContext .= " (" . date('M Y', strtotime($exp['start_date']));
            if ($exp['is_current']) {
                $userContext .= " - Present)\n";
            } else {
                $userContext .= " - " . date('M Y', strtotime($exp['end_date'])) . ")\n";
            }
            if (!empty($exp['description'])) {
                $userContext .= "  " . $exp['description'] . "\n";
            }
        }
    }
    
    // Skills
    if (!empty($skills)) {
        $userContext .= "\nSkills:\n";
        $skillsByLevel = ['Beginner' => [], 'Intermediate' => [], 'Advanced' => [], 'Expert' => []];
        foreach ($skills as $skill) {
            $level = $skill['proficiency_level'] ?? 'Intermediate';
            $skillsByLevel[$level][] = $skill['skill_name'];
        }
        foreach ($skillsByLevel as $level => $skillList) {
            if (!empty($skillList)) {
                $userContext .= "- $level: " . implode(', ', $skillList) . "\n";
            }
        }
    }
    
    // Certifications
    if (!empty($certifications)) {
        $userContext .= "\nCertifications:\n";
        foreach ($certifications as $cert) {
            $userContext .= "- " . $cert['certification_name'];
            $userContext .= " from " . $cert['issuing_organization'];
            $userContext .= " (" . date('Y', strtotime($cert['issue_date'])) . ")\n";
        }
    }
    
    // Projects
    if (!empty($projects)) {
        $userContext .= "\nProjects:\n";
        foreach ($projects as $proj) {
            $userContext .= "- " . $proj['project_name'];
            if (!empty($proj['description'])) {
                $userContext .= ": " . $proj['description'];
            }
            $userContext .= "\n";
        }
    }
    
} catch (PDOException $e) {
    $userContext = "User profile data unavailable.";
}

// Get conversation history from session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Add user message to history
$_SESSION['chat_history'][] = [
    'sender' => 'You',
    'message' => $userMessage,
    'timestamp' => time()
];

// Prepare messages for Groq API with user context
$systemPrompt = "You are Skillsync AI, a helpful career assistant. You help users with resume building, career advice, skill development, and job search strategies. Be concise, professional, and encouraging. Keep responses under 150 words unless the user asks for detailed explanations.\n\n";
$systemPrompt .= "Here is the user's profile information to help you give personalized advice:\n\n";
$systemPrompt .= $userContext;
$systemPrompt .= "\n\nUse this information to provide tailored career guidance, resume tips, and skill recommendations. Reference their actual experience and skills when giving advice.";

$messages = [
    [
        'role' => 'system',
        'content' => $systemPrompt
    ]
];

// Add conversation history (last 10 exchanges)
$recentHistory = array_slice($_SESSION['chat_history'], -10);
foreach ($recentHistory as $msg) {
    $messages[] = [
        'role' => $msg['sender'] === 'You' ? 'user' : 'assistant',
        'content' => $msg['message']
    ];
}

// Call Groq API
try {
    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    
    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 500,
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
    $aiMessage = $result['choices'][0]['message']['content'] ?? 'Sorry, I encountered an error.';
    
    // Add AI response to history
    $_SESSION['chat_history'][] = [
        'sender' => 'Skillsync AI',
        'message' => $aiMessage,
        'timestamp' => time()
    ];
    
    echo json_encode([
        'success' => true,
        'history' => $_SESSION['chat_history']
    ]);
    
} catch (Exception $e) {
    // Fallback response
    $_SESSION['chat_history'][] = [
        'sender' => 'Skillsync AI',
        'message' => "I'm having trouble connecting right now. Please try again! 💡",
        'timestamp' => time()
    ];
    
    echo json_encode([
        'success' => true,
        'history' => $_SESSION['chat_history']
    ]);
}