<?php
require_once '../config.php'; // session + PDO connection file

$albums = $db->query("SELECT * FROM albums")->fetchAll();

foreach ($albums as $album) {
    echo "<div class='album' style='margin:15px; padding:10px; border:1px solid #ccc;'>";

    // Album name input + update button
    echo "<input type='text' class='rename-album' id='album-name-{$album['id']}' value='{$album['name']}'>";
    echo "<button class='update-photo' data-id='{$album['id']}'>Update Album</button>";
    

    // Get all photos for this album
    $stmt = $db->prepare("SELECT * FROM photos WHERE album_id = ?");
    $stmt->execute([$album['id']]);
    $photos = $stmt->fetchAll();

    echo "<div style='display:flex; flex-wrap:wrap; margin-top:10px;'>";
    foreach ($photos as $photo) {
        echo "<div class='photo' style='margin:10px; border:1px solid #ddd; padding:10px; text-align:center;'>";
        echo "<img src='uploads/{$photo['filename']}' alt='Photo' style='max-width:150px; display:block; margin-bottom:5px;'>";
        echo "<input type='text' class='rename-photo' id='photo-title-{$photo['id']}' value='{$photo['title']}''><br>";
        echo "<button class='update-photo' data-id='{$photo['id']}' style='padding:4px 8px; margin-right:5px; cursor:pointer;'>Update Title</button>";
        echo "<button class='delete-photo' data-id='{$photo['id']}' style='padding:4px 8px; background:#e74c3c; color:#fff; cursor:pointer;'>Delete</button>";
        echo "</div>";
    }
    echo "</div>";

    echo "</div>";
}
?>
