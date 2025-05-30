<?php
require 'db.php';

if (!isset($_GET['lesson_id'])) exit('No lesson specified.');

$lesson_id = $_GET['lesson_id'];
$stmt = $pdo->prepare("SELECT video FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$row = $stmt->fetch();

if (!$row || empty($row['video'])) {
    exit('No video found.');
}

header("Content-Type: video/mp4");
echo $row['video'];
exit;
?>
