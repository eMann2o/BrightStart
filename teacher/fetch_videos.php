<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

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

$userEmail = $_SESSION['email'];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
 
// Count user's videos
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE email = :email");
$stmtTotal->execute(['email' => $userEmail]);
$totalVideos = $stmtTotal->fetchColumn();

// Fetch user's videos with uploader's name
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

