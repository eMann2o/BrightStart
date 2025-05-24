<?php
session_start();
include_once '../database.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo "Not logged in";
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT m.id, m.message, m.created_at, u.name, u.role, m.sender_email 
                      FROM messages m 
                      JOIN users u ON m.sender_email = u.email 
                      ORDER BY m.created_at ASC");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert id to integer for JavaScript comparison
    foreach ($messages as &$message) {
        $message['id'] = (int)$message['id'];
    }

    echo json_encode($messages);
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB Error: " . $e->getMessage();
}
?>