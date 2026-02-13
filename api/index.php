<?php
require __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

// --- CONFIG ---
$SECRET_KEY = getenv('GHOST_KEY'); 

// --- ENCRYPTION HELPER ---
function decrypt_payload($encrypted_base64, $key) {
    // Decode base64
    $data = base64_decode($encrypted_base64);
    
    // Extract IV (first 16 bytes) and Ciphertext
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    
    // Decrypt
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return json_decode($decrypted, true);
}

// --- MAIN LOGIC ---
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'listening']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ghost_packet'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No ghost packet found']);
    exit;
}

// 1. Decrypt
$payload = decrypt_payload($input['ghost_packet'], $SECRET_KEY);

if (!$payload) {
    http_response_code(403);
    echo json_encode(['error' => 'Decryption failed']);
    exit;
}

// 2. Setup Google Client
$client = new Client();
$client->setClientId(getenv('GOOGLE_CLIENT_ID'));
$client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$client->refreshToken(getenv('GOOGLE_REFRESH_TOKEN'));
$service = new Gmail($client);

// 3. Create Email
$to = $payload['to'];
$subject = $payload['subject'];
$bodyText = $payload['body'];

$strSubject = '=?utf-8?B?'.base64_encode($subject).'?=';
$rawMsg = "From: me\r\n";
$rawMsg .= "To: $to\r\n";
$rawMsg .= "Subject: $strSubject\r\n\r\n";
$rawMsg .= $bodyText;

// Base64Url encode required by Gmail API
$mime = rtrim(strtr(base64_encode($rawMsg), '+/', '-_'), '=');
$msg = new Message();
$msg->setRaw($mime);

// 4. Send
try {
    $service->users_messages->send("me", $msg);
    echo json_encode(['status' => 'success', 'target' => $to]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
