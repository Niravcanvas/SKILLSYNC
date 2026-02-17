<?php
// ── These MUST be at the top, outside everything ──────────────────────────
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ── Session & auth ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
    }
}
loadEnv(__DIR__ . '/../config/.env');

$uid = $_SESSION['user_id'];

// ── Defaults ──────────────────────────────────────────────────────────────
$name = $email = $phone = $location = $linkedin = '';
$education = $skills = $experience = $career_objective = '';
$strengths = $technical = $interests = $languages = '';
$accent_color = '#667eea'; $text_color = '#222222'; $bg_color = '#ffffff';
$font_family = 'Crimson Pro'; $font_scale = 1;
$divider_style = 'solid'; $divider_thickness = 2;
$bw_theme = false; $profile_frame = 'circle'; $profile_img = '';

// ── Load from DB ──────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT full_name, email, phone, location, bio, profile_picture, headline FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $name             = $user['full_name']       ?? '';
        $email            = $user['email']           ?? '';
        $phone            = $user['phone']           ?? '';
        $location         = $user['location']        ?? '';
        $career_objective = $user['bio']             ?? '';
        $profile_img      = $user['profile_picture'] ?? '';
        $linkedin         = $user['headline']        ?? '';
    }

    // Education
    $stmt = $pdo->prepare("SELECT degree, field_of_study, institution, start_year, end_year FROM education WHERE user_id = ? ORDER BY end_year DESC");
    $stmt->execute([$uid]);
    $edu_parts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $line = trim($e['degree'] . ' in ' . $e['field_of_study']);
        $line .= "\n" . $e['institution'];
        if ($e['start_year'] || $e['end_year']) $line .= ', ' . $e['start_year'] . '–' . $e['end_year'];
        $edu_parts[] = $line;
    }
    $education = implode("\n\n", $edu_parts);

    // Skills
    $stmt = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
    $stmt->execute([$uid]);
    $skills = implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));

    // Experience
    $stmt = $pdo->prepare("SELECT position, company, start_date, end_date, description FROM experience WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$uid]);
    $exp_parts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $e) {
        $start = $e['start_date'] ? date('M Y', strtotime($e['start_date'])) : '';
        $end   = $e['end_date']   ? date('M Y', strtotime($e['end_date']))   : 'Present';
        $line  = $e['position'] . ' – ' . $e['company'];
        if ($start) $line .= "\n" . $start . ' – ' . $end;
        if ($e['description']) $line .= "\n" . $e['description'];
        $exp_parts[] = $line;
    }
    $experience = implode("\n\n", $exp_parts);

    // Projects → Technical field
    $stmt = $pdo->prepare("SELECT project_name, technologies, description FROM projects WHERE user_id = ?");
    $stmt->execute([$uid]);
    $proj_parts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $line = $p['project_name'];
        if ($p['technologies']) $line .= ' (' . $p['technologies'] . ')';
        if ($p['description'])  $line .= "\n" . $p['description'];
        $proj_parts[] = $line;
    }
    $technical = implode("\n\n", $proj_parts);

    // Certifications → Strengths field
    $stmt = $pdo->prepare("SELECT title, issuer, cert_date FROM certifications WHERE user_id = ? ORDER BY cert_date DESC");
    $stmt->execute([$uid]);
    $cert_parts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $date = $c['cert_date'] ? date('M Y', strtotime($c['cert_date'])) : '';
        $line = $c['title'];
        if ($c['issuer']) $line .= ' – ' . $c['issuer'];
        if ($date)        $line .= ' (' . $date . ')';
        $cert_parts[] = $line;
    }
    $strengths = implode("\n", $cert_parts);

} catch (PDOException $e) {
    error_log("resume.php DB load: " . $e->getMessage());
}

// ── Session styling prefs override DB ─────────────────────────────────────
$accent_color      = $_SESSION['accent_color']      ?? $accent_color;
$text_color        = $_SESSION['text_color']        ?? $text_color;
$bg_color          = $_SESSION['bg_color']          ?? $bg_color;
$font_family       = $_SESSION['font_family']       ?? $font_family;
$font_scale        = $_SESSION['font_scale']        ?? $font_scale;
$divider_style     = $_SESSION['divider_style']     ?? $divider_style;
$divider_thickness = $_SESSION['divider_thickness'] ?? $divider_thickness;
$bw_theme          = $_SESSION['bw_theme']          ?? $bw_theme;
$profile_frame     = $_SESSION['profile_frame']     ?? $profile_frame;

// ── Handle POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // AI Refresh
    if (isset($_POST['refresh_ai'])) {
        header('Content-Type: application/json');

        $user_skills = trim($_POST['skills']     ?? '');
        $user_edu    = trim($_POST['education']  ?? '');
        $user_exp    = trim($_POST['experience'] ?? '');
        $user_name   = trim($_POST['name']       ?? '');
        $api_key     = $_ENV['PERPLEXITY_API_KEY'] ?? '';

        if (!empty($api_key)) {
            $prompt = "Write a professional resume career objective (3 sentences max) for {$user_name}. " .
                      "Skills: {$user_skills}. Education: {$user_edu}. Experience: {$user_exp}. " .
                      "Output only the objective text, no labels or quotes.";

            $payload = json_encode([
                'model'    => 'llama-3.1-sonar-small-128k-online',
                'messages' => [
                    ['role' => 'system', 'content' => 'You write concise professional resume content. Output only the requested text.'],
                    ['role' => 'user',   'content' => $prompt]
                ],
                'max_tokens' => 200, 'temperature' => 0.4
            ]);

            $ch = curl_init('https://api.perplexity.ai/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $api_key,
                    'Content-Type: application/json'
                ]
            ]);
            $res = curl_exec($ch);
            curl_close($ch);
            $decoded = json_decode($res, true);
            $ai_text = $decoded['choices'][0]['message']['content'] ?? '';
            if (!empty($ai_text)) {
                echo json_encode(['success' => true, 'career_objective' => trim($ai_text)]);
                exit;
            }
        }

        // Fallback
        $arr  = array_filter(array_map('trim', explode(',', $user_skills)));
        $main = implode(', ', array_slice($arr, 0, 3));
        $obj  = !empty($main)
            ? "Results-driven professional with expertise in {$main}, passionate about building impactful solutions. Seeking to bring technical skill and collaborative energy to a forward-thinking team."
            : "Dedicated professional eager to contribute skills and grow within a collaborative, innovative environment.";
        echo json_encode(['success' => true, 'career_objective' => $obj]);
        exit;
    }

    // Save form fields to session
    $post_fields = [
        'name','email','phone','location','linkedin','education','skills',
        'experience','career_objective','strengths','technical','interests','languages',
        'accent_color','text_color','bg_color','font_family','font_scale',
        'divider_style','divider_thickness','profile_frame'
    ];
    foreach ($post_fields as $f) {
        $$f = $_POST[$f] ?? $$f;
        $_SESSION[$f] = $$f;
    }
    $bw_theme = isset($_POST['bw_theme']) ? 1 : 0;
    $_SESSION['bw_theme'] = $bw_theme;

    // Profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $upload_dir = __DIR__ . '/../../public/uploads/profiles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $fname  = 'profile_' . $uid . '_' . time() . '.' . $ext;
            $target = $upload_dir . $fname;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $profile_img             = '../../public/uploads/profiles/' . $fname;
                $_SESSION['profile_img'] = $profile_img;
                try {
                    $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?")->execute([$profile_img, $uid]);
                } catch (PDOException $ignored) {}
            }
        }
    }

    // PDF Generation
    if (isset($_POST['download_pdf'])) {
        $pdf_accent = $bw_theme ? '#333' : $accent_color;
        $pdf_text   = $bw_theme ? '#111' : $text_color;
        $pdf_bg     = $bw_theme ? '#fff' : $bg_color;

        $pic_html = '';
        if (!empty($profile_img)) {
            $abs = __DIR__ . '/../../' . ltrim(str_replace('../../', '', $profile_img), '/');
            if (file_exists($abs)) {
                $mime     = mime_content_type($abs);
                $b64      = base64_encode(file_get_contents($abs));
                $src      = "data:{$mime};base64,{$b64}";
                $radius   = match($profile_frame) { 'circle' => '50%', 'rounded' => '14px', default => '4px' };
                $pic_html = "<div style='text-align:center;margin-bottom:18px;'><img src='{$src}' style='width:90px;height:90px;object-fit:cover;border-radius:{$radius};border:3px solid {$pdf_accent};'></div>";
            }
        }

        $skill_pills = '';
        if (!empty($skills)) {
            $arr         = array_filter(array_map('trim', explode(',', $skills)));
            $skill_pills = implode('', array_map(fn($s) =>
                "<span style='display:inline-block;background:{$pdf_accent}18;color:{$pdf_accent};border:1px solid {$pdf_accent}44;border-radius:4px;padding:2px 10px;margin:2px 3px;font-size:11px;font-weight:600;'>"
                . htmlspecialchars($s) . "</span>", $arr));
        }

        $sec = fn($title, $content) =>
            "<h3 style='font-size:13pt;font-weight:700;color:{$pdf_accent};margin:18px 0 6px;padding-bottom:5px;border-bottom:{$divider_thickness}px {$divider_style} {$pdf_accent};'>{$title}</h3>
             <div style='color:{$pdf_text};font-size:11pt;line-height:1.65;'>{$content}</div>";

        $contact = implode('  &bull;  ', array_filter([
            htmlspecialchars($email),
            htmlspecialchars($phone),
            htmlspecialchars($location),
            htmlspecialchars($linkedin)
        ]));

        $sections = '';
        if (!empty($career_objective)) $sections .= $sec('Career Objective', '<p>' . nl2br(htmlspecialchars($career_objective)) . '</p>');
        if (!empty($education))        $sections .= $sec('Education',        '<p>' . nl2br(htmlspecialchars($education))        . '</p>');
        if (!empty($skills))           $sections .= $sec('Skills',           $skill_pills);
        if (!empty($experience))       $sections .= $sec('Experience',       '<p>' . nl2br(htmlspecialchars($experience))       . '</p>');
        if (!empty($technical))        $sections .= $sec('Projects',         '<p>' . nl2br(htmlspecialchars($technical))        . '</p>');
        if (!empty($strengths))        $sections .= $sec('Certifications',   '<p>' . nl2br(htmlspecialchars($strengths))        . '</p>');
        if (!empty($interests))        $sections .= $sec('Interests',        '<p>' . nl2br(htmlspecialchars($interests))        . '</p>');
        if (!empty($languages))        $sections .= $sec('Languages',        '<p>' . nl2br(htmlspecialchars($languages))        . '</p>');

        $fs   = round(11 * (float)$font_scale);
        $html = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'{$font_family}',Georgia,serif;font-size:{$fs}pt;color:{$pdf_text};background:{$pdf_bg};padding:36px 44px;line-height:1.6;}
  .name{font-size:calc({$fs}pt * 1.9);font-weight:800;color:{$pdf_accent};text-align:center;margin-bottom:6px;letter-spacing:-0.5px;}
  .contact{text-align:center;font-size:9pt;color:#666;margin-bottom:4px;}
  .bar{width:60px;height:3px;background:{$pdf_accent};margin:10px auto 0;border-radius:2px;}
</style></head><body>
{$pic_html}
<div class="name">{$name}</div>
<div class="contact">{$contact}</div>
<div class="bar"></div>
{$sections}
</body></html>
HTML;

        $opts = new Options();
        $opts->set('defaultFont', 'serif');
        $opts->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safe = preg_replace('/[^a-z0-9_-]/i', '_', $name ?: 'resume');
        $dompdf->stream("resume_{$safe}.pdf", ['Attachment' => true]);
        exit;
    }
}
?>