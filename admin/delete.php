<?php
require_once '../config.php'; // Replace with the actual filename

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

$id = $_POST['id'];

// Fetch the photo filename
$stmt = $db->prepare("SELECT filename FROM photos WHERE id = ?");
$stmt->execute([$id]);
$photo = $stmt->fetch();

if ($photo) {
    $filePath = "uploads/" . $photo['filename'];
    
    // Delete file from the server
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete record from DB
    $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
    $stmt->execute([$id]);
} else {
    http_response_code(404);
    echo "Photo not found.";
}
?>
