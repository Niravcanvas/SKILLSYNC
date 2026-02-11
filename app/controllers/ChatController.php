<?php
/**
 * ChatController.php
 * Handles AI chatbot interactions using Perplexity API
 * Located in: /app/controllers/ChatController.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Database connection using PDO (as per README)
require_once __DIR__ . '/../config/database.php';

// Load environment variables
require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Authentication check
 */
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "error" => "Authentication required. Please log in to continue."
    ]);
    exit;
}

/**
 * Initialize chat history in session
 */
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

/**
 * Handle AJAX POST request
 */
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || empty($data['message'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "No message provided."
    ]);
    exit;
}

$userMessage = trim($data['message']);

// Sanitize and validate message length
if (strlen($userMessage) > 1000) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Message too long. Please keep it under 1000 characters."
    ]);
    exit;
}

// Add user message to chat history
$_SESSION['chat_history'][] = [
    'sender' => 'You',
    'message' => htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8'),
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    /**
     * Fetch user profile data from database
     */
    $userData = fetchUserData($pdo, $userId);
    
    /**
     * Build context-aware prompt
     */
    $prompt = buildPrompt($userData, $userMessage);
    
    /**
     * Get API response from Perplexity AI
     */
    $reply = getAIResponse($prompt);
    
    /**
     * Add AI response to chat history
     */
    $_SESSION['chat_history'][] = [
        'sender' => 'AI',
        'message' => htmlspecialchars($reply, ENT_QUOTES, 'UTF-8'),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    /**
     * Return successful response
     */
    echo json_encode([
        "success" => true,
        "reply" => $reply,
        "history" => $_SESSION['chat_history']
    ]);

} catch (Exception $e) {
    error_log("ChatController Error: " . $e->getMessage());
    
    $errorReply = "I'm having trouble connecting right now. Please try again in a moment.";
    
    $_SESSION['chat_history'][] = [
        'sender' => 'AI',
        'message' => $errorReply,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        "success" => false,
        "reply" => $errorReply,
        "history" => $_SESSION['chat_history'],
        "error" => "Service temporarily unavailable"
    ]);
}

/**
 * Fetch user data from database
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array User data and related information
 */
function fetchUserData($pdo, $userId) {
    $data = [];
    
    try {
        // Fetch basic user info
        $stmt = $pdo->prepare("SELECT full_name, headline, location, bio FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $data['user'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch skills
        $stmt = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['skills'] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        
        // Fetch experience
        $stmt = $pdo->prepare("SELECT company, position, description FROM experience WHERE user_id = ? ORDER BY start_date DESC LIMIT 5");
        $stmt->execute([$userId]);
        $data['experiences'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch projects
        $stmt = $pdo->prepare("SELECT project_name, description, technologies FROM projects WHERE user_id = ? LIMIT 5");
        $stmt->execute([$userId]);
        $data['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch education
        $stmt = $pdo->prepare("SELECT institution, degree, field_of_study, start_year, end_year FROM education WHERE user_id = ? ORDER BY end_year DESC LIMIT 3");
        $stmt->execute([$userId]);
        $data['education'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch certifications
        $stmt = $pdo->prepare("SELECT title, issuer, cert_date FROM certifications WHERE user_id = ? ORDER BY cert_date DESC LIMIT 5");
        $stmt->execute([$userId]);
        $data['certifications'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
    } catch (PDOException $e) {
        error_log("Database error in fetchUserData: " . $e->getMessage());
        throw new Exception("Failed to fetch user data");
    }
    
    return $data;
}

/**
 * Build context-aware prompt for AI
 * 
 * @param array $userData User profile data
 * @param string $userMessage User's question
 * @return string Formatted prompt
 */
function buildPrompt($userData, $userMessage) {
    $prompt = "User Profile:\n";
    
    // Basic info
    $user = $userData['user'];
    $prompt .= "Name: " . ($user['full_name'] ?? 'Not provided') . "\n";
    $prompt .= "Headline: " . ($user['headline'] ?? 'Not provided') . "\n";
    $prompt .= "Location: " . ($user['location'] ?? 'Not provided') . "\n";
    $prompt .= "Bio: " . ($user['bio'] ?? 'Not provided') . "\n\n";
    
    // Skills
    if (!empty($userData['skills'])) {
        $prompt .= "Skills: " . implode(', ', $userData['skills']) . "\n\n";
    } else {
        $prompt .= "Skills: None listed\n\n";
    }
    
    // Experience
    if (!empty($userData['experiences'])) {
        $prompt .= "Work Experience:\n";
        foreach ($userData['experiences'] as $exp) {
            $position = $exp['position'] ?? 'Position';
            $company = $exp['company'] ?? 'Company';
            $description = $exp['description'] ?? 'No description';
            $prompt .= "- $position at $company: $description\n";
        }
        $prompt .= "\n";
    }
    
    // Projects
    if (!empty($userData['projects'])) {
        $prompt .= "Projects:\n";
        foreach ($userData['projects'] as $proj) {
            $name = $proj['project_name'] ?? 'Project';
            $tech = $proj['technologies'] ?? 'N/A';
            $desc = $proj['description'] ?? 'No description';
            $prompt .= "- $name ($tech): $desc\n";
        }
        $prompt .= "\n";
    }
    
    // Education
    if (!empty($userData['education'])) {
        $prompt .= "Education:\n";
        foreach ($userData['education'] as $edu) {
            $degree = $edu['degree'] ?? 'Degree';
            $field = $edu['field_of_study'] ?? 'Field';
            $institution = $edu['institution'] ?? 'Institution';
            $years = ($edu['start_year'] ?? '?') . "-" . ($edu['end_year'] ?? '?');
            $prompt .= "- $degree in $field from $institution ($years)\n";
        }
        $prompt .= "\n";
    }
    
    // Certifications
    if (!empty($userData['certifications'])) {
        $prompt .= "Certifications:\n";
        foreach ($userData['certifications'] as $cert) {
            $title = $cert['title'] ?? 'Certification';
            $issuer = $cert['issuer'] ?? 'Issuer';
            $date = $cert['cert_date'] ?? 'Date N/A';
            $prompt .= "- $title by $issuer ($date)\n";
        }
        $prompt .= "\n";
    }
    
    $prompt .= "User Question: $userMessage";
    
    return $prompt;
}

/**
 * Get AI response from Perplexity API
 * 
 * @param string $prompt User prompt with context
 * @return string AI response
 * @throws Exception If API call fails
 */
function getAIResponse($prompt) {
    // Load API key from environment variable (as per README best practices)
    $apiKey = getenv('PERPLEXITY_API_KEY') ?: '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';
    
    if (!$apiKey) {
        throw new Exception("API key not configured");
    }
    
    $client = new GuzzleClient([
        'timeout' => 30,
        'connect_timeout' => 10
    ]);
    
    try {
        $response = $client->post('https://api.perplexity.ai/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'sonar-pro',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are SkillSync AI, a helpful, concise, and friendly career assistant. You provide personalized career advice, resume tips, job search guidance, and skill development recommendations based on the user\'s profile. Always reply in plain text, no Markdown formatting. Keep responses conversational and under 100 words unless the user needs detailed information. If asked who created you, reply: "Lawrence built me as part of the SkillSync AI project." Your birthday is September 9th. Be engaging, supportive, and professional. Focus on actionable advice and positive encouragement.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ],
        ]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
        
        throw new Exception("Invalid API response format");
        
    } catch (GuzzleException $e) {
        error_log("Perplexity API Error: " . $e->getMessage());
        throw new Exception("AI service unavailable: " . $e->getMessage());
    }
}

/**
 * Clear chat history (optional endpoint)
 */
if (isset($data['action']) && $data['action'] === 'clear') {
    $_SESSION['chat_history'] = [];
    echo json_encode([
        "success" => true,
        "message" => "Chat history cleared",
        "history" => []
    ]);
    exit;
}