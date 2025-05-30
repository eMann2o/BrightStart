<?php
include_once '../database.php';
session_start();

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$days = isset($_GET['days']) ? (int) $_GET['days'] : 7;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        SELECT login_date, COUNT(DISTINCT user_id) AS unique_logins
        FROM user_logins
        WHERE login_date >= CURDATE() - INTERVAL :days DAY
        GROUP BY login_date
        ORDER BY login_date ASC
    ");
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $logins = [];

    foreach ($results as $row) {
        $labels[] = date("M j", strtotime($row['login_date']));
        $logins[] = (int)$row['unique_logins'];
    }

    echo json_encode(["labels" => $labels, "logins" => $logins]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
}
?>
