<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'], $data['file_path'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$id = (int)$data['id'];
$file_path = $data['file_path'];

// DB connection (same as before)
$host = 'localhost';
$db   = 'brightstart';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Delete from DB
$stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
if ($stmt->execute([$id])) {
    // Delete file from server
    $fullPath = __DIR__ . '/../uploads/' . $file_path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to delete video']);
}
?>