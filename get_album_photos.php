<?php
header('Content-Type: application/json');
include_once 'database.php';

$host = $host;
$dbname = $dbname;
$username = $username_db;
$password = $epassword;

$albumId = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT filename, original_name, title FROM photos WHERE album_id = ?");
    $stmt->execute([$albumId]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'photos' => $photos]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
