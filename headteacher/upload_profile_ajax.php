<?php
include_once '../database.php';
require 'db.php';

header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email not provided.']);
        exit;
    }

    $email = $_POST['email'];

    if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Invalid file upload.']);
        exit;
    }

    $imgData = file_get_contents($_FILES['profile_pic']['tmp_name']);
    $imgMime = mime_content_type($_FILES['profile_pic']['tmp_name']);

    try {
        $stmt = $db->prepare("UPDATE users SET profile_pic = :img, profile_mime = :mime WHERE email = :email");
        $stmt->execute([
            ':img' => $imgData,
            ':mime' => $imgMime,
            ':email' => $email
        ]);

        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update picture.']);
    }
}
?>
