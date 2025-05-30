<?php
session_start();
include_once '../database.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo "Not logged in";
    exit();
}

$content = trim($_POST['message']);
if ($content === "") {
    http_response_code(400);
    echo "Empty message";
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("INSERT INTO messages (sender_email, message) VALUES (:email, :message)");
    $stmt->execute([
        ':email' => $_SESSION['email'],
        ':message' => $content
    ]);

    echo "Message sent";
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB Error: " . $e->getMessage();
}
?>
