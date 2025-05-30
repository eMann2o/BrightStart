<?php
session_start();
include_once '../database.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$lastReadMessageId = $input['last_read_message_id'] ?? null;

if ($lastReadMessageId === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid message ID']);
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("INSERT INTO user_read_status (user_email, last_read_message_id, updated_at) 
                         VALUES (:email, :message_id, NOW()) 
                         ON DUPLICATE KEY UPDATE 
                         last_read_message_id = :message_id, updated_at = NOW()");
    
    $stmt->execute([
        ':email' => $_SESSION['email'],
        ':message_id' => $lastReadMessageId
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
}
?>