<?php
session_start();

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo "Unauthorized. Please log in.";
    exit;
}

$email = $_SESSION['email'];

// Database credentials
require '../database.php'; // Make sure this file sets $host, $dbname, $username_db, $password_db

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

$allowedTypes = [
    'video/mp4' => 'mp4',
    'video/quicktime' => 'mov',
    'video/x-msvideo' => 'avi',
    'video/webm' => 'webm',
    'video/x-ms-wmv' => 'wmv'
];

$fileType = mime_content_type($_FILES['video']['tmp_name']);
$fileSize = $_FILES['video']['size'];
$maxSize = 1024 * 1024 * 1024; // 1GB

if (!array_key_exists($fileType, $allowedTypes)) {
    http_response_code(415);
    echo "Unsupported file type.";
    exit;
}

if ($fileSize > $maxSize) {
    http_response_code(413);
    echo "File is too large. Max is 1GB.";
    exit;
}

$extension = $allowedTypes[$fileType];
$uniqueName = uniqid('video_', true) . "." . $extension;

$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destination = $uploadDir . $uniqueName;
if (!move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
    http_response_code(500);
    echo "Failed to move uploaded file.";
    exit;
}

// Insert video record into the database
$stmt = $pdo->prepare("INSERT INTO videos (email, file_name, file_path, file_size, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$email, $_FILES['video']['name'], $uniqueName, $fileSize]);

echo "Upload successful!";
?>
