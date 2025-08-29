<?php
require_once '../config.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'], $_POST['id'])) {
    $type = $_POST['type'];
    $id   = (int) $_POST['id'];

    if ($type === 'album') {
        // First delete all photos belonging to this album
        $stmt = $db->prepare("SELECT filename FROM photos WHERE album_id = ?");
        $stmt->execute([$id]);
        $photos = $stmt->fetchAll();

        foreach ($photos as $photo) {
            $filePath = __DIR__ . "/uploads/" . $photo['filename'];
            if (file_exists($filePath)) {
                unlink($filePath); // delete file
            }
        }

        // Remove photos from DB
        $stmt = $db->prepare("DELETE FROM photos WHERE album_id = ?");
        $stmt->execute([$id]);

        // Remove album itself
        $stmt = $db->prepare("DELETE FROM albums WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "success", "message" => "Album and its photos deleted"]);
    } 
    elseif ($type === 'photo') {
        // Find photo filename
        $stmt = $db->prepare("SELECT filename FROM photos WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();

        if ($photo) {
            $filePath = __DIR__ . "/uploads/" . $photo['filename'];
            if (file_exists($filePath)) {
                unlink($filePath); // delete file
            }

            // Delete DB row
            $stmt = $db->prepare("DELETE FROM photos WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(["status" => "success", "message" => "Photo deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Photo not found"]);
        }
    } 
    else {
        echo json_encode(["status" => "error", "message" => "Invalid delete type"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
