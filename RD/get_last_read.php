<?php
session_start();
include_once '../database.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT last_read_message_id FROM user_read_status WHERE user_email = :email");
    $stmt->execute([':email' => $_SESSION['email']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['last_read_message_id' => (int)$result['last_read_message_id']]);
    } else {
        echo json_encode(['last_read_message_id' => null]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
}
?>