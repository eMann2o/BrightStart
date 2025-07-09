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
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
if (!empty($category)) {
    $where = 'WHERE v.category = :category';
}

// Count total videos
$countSql = "SELECT COUNT(*) FROM videos v $where";
$countStmt = $pdo->prepare($countSql);
if (!empty($category)) {
    $countStmt->bindParam(':category', $category, PDO::PARAM_STR);
}
$countStmt->execute();
$totalVideos = $countStmt->fetchColumn();

// Fetch paginated videos with uploader name
$dataSql = "SELECT v.id, v.file_name, v.category, v.grade, v.activity, v.caption, v.file_path, u.name 
            FROM videos v
            JOIN users u ON v.email = u.email
            $where
            ORDER BY v.uploaded_at DESC
            LIMIT :limit OFFSET :offset";
$dataStmt = $pdo->prepare($dataSql);
if (!empty($category)) {
    $dataStmt->bindParam(':category', $category, PDO::PARAM_STR);
}
$dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$dataStmt->execute();
$videos = $dataStmt->fetchAll();

// Get all distinct categories
$catStmt = $pdo->query("SELECT DISTINCT category FROM videos WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Return response
echo json_encode([
    'videos' => $videos,
    'totalVideos' => (int)$totalVideos,
    'limit' => $limit,
    'page' => $page,
    'categories' => $categories
]);
?>