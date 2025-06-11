<?php
include_once '../database.php';
require 'db.php';

if (!isset($_GET['email'])) {
    http_response_code(400);
    exit("Email is required");
}

$email = $_GET['email'];

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT profile_pic FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['profile_pic']) {
        header("Content-Type: image/jpeg"); // or image/png depending on what you're uploading
        echo $row['profile_pic'];
    } else {
        // Show a default image if no picture is set
        header("Content-Type: image/png");
        readfile("default.png"); // Create or use a placeholder image in same folder
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
