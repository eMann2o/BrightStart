<?php
session_start();

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo "Unauthorized. Please log in.";
    exit;
}

$email = $_SESSION['email'];

// Database credentials
require '../database.php'; // Make sure this sets $host, $dbname, $username_db, $password_db

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "No file uploaded or upload error.";
    exit;
}

$fileSize = $_FILES['video']['size'];
$maxSize = 1024 * 1024 * 1024; // 1GB

if ($fileSize > $maxSize) {
    http_response_code(413);
    echo "File is too large. Max is 1GB.";
    exit;
}

// Preserve original extension
$originalName = $_FILES['video']['name'];
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$uniqueName = uniqid('file_', true) . '.' . $extension;

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destination = $uploadDir . $uniqueName;
if (!move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
    http_response_code(500);
    echo "Failed to move uploaded file.";
    exit;
}

// Save file details in database
$stmt = $pdo->prepare("INSERT INTO videos (email, file_name, file_path, file_size, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$email, $originalName, $uniqueName, $fileSize]);

echo "Upload successful!";
?>
