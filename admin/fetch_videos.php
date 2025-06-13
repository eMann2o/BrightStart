<?php

session_start();
include "../dbconnect.php";

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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total videos count
$stmtTotal = $pdo->query("SELECT COUNT(*) FROM videos");
$totalVideos = $stmtTotal->fetchColumn();

// Fetch videos with uploader's username
$stmt = $pdo->prepare("SELECT v.id, v.file_name, v.category, v.caption, v.file_path, u.name 
                       FROM videos v
                       JOIN users u ON v.email = u.email
                       ORDER BY v.uploaded_at DESC
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$videos = $stmt->fetchAll();

echo json_encode([
    'videos' => $videos,
    'totalVideos' => (int)$totalVideos,
    'limit' => $limit,
    'page' => $page,
]);
?>
