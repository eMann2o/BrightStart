<?php
$host = 'localhost';
$dbname = 'stcciju4_brightstart';
$username_db = 'stcciju4_eMann';
$password_db = "";

// Connect to the database
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["student_email"])) {
    $email_to_delete = $_POST['student_email'] ?? '';

    if (!empty($email_to_delete)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email_to_delete]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['id'];
            $db->beginTransaction();

            try {
                $tables = ['user_logins', 'progress', 'enrollments'];
                foreach ($tables as $table) {
                    $stmt = $db->prepare("DELETE FROM $table WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                }

                // Also delete courses where the user is an instructor
                $stmt = $db->prepare("DELETE FROM courses WHERE instructor_id = ?");
                $stmt->execute([$user_id]); // ✅ don't forget this line

                // Delete the user from the users table
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);

                $db->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'User account deleted successfully.'
                ]);
                exit;

            } catch (PDOException $e) {
                $db->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'Delete failed: ' . $e->getMessage()
                ]);
                exit;
            }

        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found.'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No email provided.'
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request.'
]);
?>