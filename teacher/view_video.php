<?php
session_start();
require 'db.php';

if (!isset($_GET['lesson_id']) || !isset($_SESSION['email'])) {
    echo "Missing lesson or user.";
    exit;
}

// Get user ID from session email
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$user_id = $user['id'];

$lesson_id = $_GET['lesson_id'];

// Fetch video from DB
$stmt = $pdo->prepare("SELECT title, video FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo "Lesson not found.";
    exit;
}

// Write video blob to a temporary file
file_put_contents("temp_video.mp4", $lesson['video']);

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($lesson['title']) ?></title>
</head>
<body>
    <h2><?= htmlspecialchars($lesson['title']) ?></h2>
    <video width="720" controls onended="markCompleted()">
        <source src="temp_video.mp4" type="video/mp4">
        Your browser does not support video.
    </video>

    <script>
        function markCompleted() {
            fetch("mark_progress.php?lesson_id=<?= $lesson_id ?>&user_id=<?= $user_id ?>")
                .then(response => console.log("Progress marked"));
        }
    </script>
</body>
</html>
