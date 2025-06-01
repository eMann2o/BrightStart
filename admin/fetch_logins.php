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

    // Fetch login data - Use DATE() to group by date only
    $stmt = $db->prepare("
        SELECT DATE(login_date) as login_date, COUNT(DISTINCT user_id) AS unique_logins
        FROM user_logins
        WHERE DATE(login_date) >= CURDATE() - INTERVAL :days DAY
        GROUP BY DATE(login_date)
        ORDER BY DATE(login_date) ASC
    ");
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    $loginResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch completions data - Use DATE() to group by date only
    $stmt = $db->prepare("
        SELECT DATE(updated_at) as updated_at, COUNT(DISTINCT user_id) AS unique_completions
        FROM progress
        WHERE DATE(updated_at) >= CURDATE() - INTERVAL :days DAY
        GROUP BY DATE(updated_at)
        ORDER BY DATE(updated_at) ASC
    ");
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    $completionResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare labels (based on login and completion dates)
    $labels = [];
    $loginsMap = [];
    $completionsMap = [];

    foreach ($loginResults as $row) {
        $key = date("M j", strtotime($row['login_date']));
        $loginsMap[$key] = (int)$row['unique_logins'];
    }

    foreach ($completionResults as $row) {
        $key = date("M j", strtotime($row['updated_at']));
        $completionsMap[$key] = (int)$row['unique_completions'];
    }

    // Merge labels from both datasets
    $allDates = array_unique(array_merge(array_keys($loginsMap), array_keys($completionsMap)));
    sort($allDates);

    $logins = [];
    $completions = [];

    foreach ($allDates as $label) {
        $labels[] = $label;
        $logins[] = $loginsMap[$label] ?? 0;
        $completions[] = $completionsMap[$label] ?? 0;
    }

    echo json_encode([
        "labels" => $labels,
        "logins" => $logins,
        "completions" => $completions
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>