<?php
require 'db.php';

$user_id = $_GET['user_id'];
$lesson_id = $_GET['lesson_id'];

// Check if progress already exists
$stmt = $pdo->prepare("SELECT id FROM progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$user_id, $lesson_id]);
$exists = $stmt->fetch();

if ($exists) {
    $pdo->prepare("UPDATE progress SET status = 'completed' WHERE user_id = ? AND lesson_id = ?")
        ->execute([$user_id, $lesson_id]);
} else {
    $pdo->prepare("INSERT INTO progress (user_id, lesson_id, status) VALUES (?, ?, 'completed')")
        ->execute([$user_id, $lesson_id]);
}

if (isset($_GET['temp_file'])) {
    $tempFile = basename($_GET['temp_file']); // security: remove any path info
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}
?>
