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
 
if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userEmail = $_SESSION['email'];

// Step 1: Get the logged-in user's district
$stmtOrg = $pdo->prepare("SELECT district FROM users WHERE email = :email");
$stmtOrg->execute([':email' => $userEmail]);
$orgData = $stmtOrg->fetch();

if (!$orgData) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$district = $orgData['district'];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Step 2: Get total count of videos from the same district
$stmtTotal = $pdo->prepare("
    SELECT COUNT(*) FROM videos v
    JOIN users u ON v.email = u.email
    WHERE u.district = :district
");
$stmtTotal->execute([':district' => $district]);
$totalVideos = $stmtTotal->fetchColumn();

// Step 3: Fetch paginated videos from same district
$stmt = $pdo->prepare("
    SELECT v.id, v.file_name, v.file_path, u.name 
    FROM videos v
    JOIN users u ON v.email = u.email
    WHERE u.district = :district
    ORDER BY v.uploaded_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':district', $district, PDO::PARAM_STR);
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
