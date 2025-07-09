<?php
header('Content-Type: application/json');
include_once '../database.php';

$host = $host;
$dbname = $dbname;
$username = $username_db;
$password = $epassword;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch albums with first photo as cover
    $stmt = $pdo->query("
        SELECT a.id, a.name,
        (SELECT filename FROM photos WHERE album_id = a.id ORDER BY uploaded_at ASC LIMIT 1) AS cover
        FROM albums a
        ORDER BY a.created_at DESC
    ");
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'albums' => $albums]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
