<?php
session_start();
header('Content-Type: application/json');
include_once '../database.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        SELECT ul.login_date, ul.user_id, u.name, u.email
        FROM user_logins ul
        JOIN users u ON ul.user_id = u.id
        ORDER BY ul.login_date DESC
    ");
    $stmt->execute();

    $logins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logins);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>