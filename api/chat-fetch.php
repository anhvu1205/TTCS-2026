<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$sessionId = trim($data['session_id'] ?? '');
$lastId = (int)($data['last_id'] ?? 0);

if ($sessionId === '') {
    echo json_encode(['messages' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$safeSession = mysqli_real_escape_string($conn, $sessionId);

$res = mysqli_query($conn, "
    SELECT id, sender, message, created_at
    FROM ChatbotMessages
    WHERE session_id = '$safeSession'
    AND id > $lastId
    AND sender = 'admin'
    ORDER BY id ASC
");

$messages = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = $row;
    }
}

echo json_encode([
    'messages' => $messages
], JSON_UNESCAPED_UNICODE);
