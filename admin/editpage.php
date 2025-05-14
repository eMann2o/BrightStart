<?php
// Start session at the very beginning
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../index.html");
    exit();
}

// Include database configuration
require_once '../database.php';

// Set content type to JSON for consistent responses
header('Content-Type: application/json');

// Function to return JSON responses
function json_response($success, $message = '', $field = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'field' => $field
    ]);
    exit();
}

try {
    // Create secure PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    json_response(false, "Database connection error");
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, "Invalid request method");
}

// Validate CSRF token if implemented
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     json_response(false, "CSRF token validation failed");
// }

// Validate required fields
$required_fields = ['currentPassword', 'newPassword', 'confirmNewPassword'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        json_response(false, "Field $field is required", $field);
    }
}

$current_password = $_POST['currentPassword'];
$new_password = $_POST['newPassword'];
$confirm_password = $_POST['confirmNewPassword'];
$email = $_SESSION['email'];

// Password validation
if (strlen($new_password) < 8) {
    json_response(false, "Password must be at least 8 characters", 'newPassword');
}

if (!preg_match('/[A-Z]/', $new_password)) {
    json_response(false, "Password must contain at least one uppercase letter", 'newPassword');
}

if (!preg_match('/[a-z]/', $new_password)) {
    json_response(false, "Password must contain at least one lowercase letter", 'newPassword');
}

if (!preg_match('/[0-9]/', $new_password)) {
    json_response(false, "Password must contain at least one number", 'newPassword');
}

if (!preg_match('/[\W]/', $new_password)) {
    json_response(false, "Password must contain at least one special character", 'newPassword');
}

if ($new_password !== $confirm_password) {
    json_response(false, "New passwords do not match", 'confirmNewPassword');
}

// Verify current password
try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        json_response(false, "Current password is incorrect", 'currentPassword');
    }
    
    // Check if new password is different from current
    if (password_verify($new_password, $user['password'])) {
        json_response(false, "New password must be different from current password", 'newPassword');
    }
} catch (PDOException $e) {
    json_response(false, "Database error verifying current password");
}

// Update password
try {
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update_stmt->execute([$hashed_password, $email]);
    
    if ($update_stmt->rowCount() > 0) {
        // Optionally log the user out after password change
        // session_destroy();
        
        json_response(true, "Password updated successfully");
    } else {
        json_response(false, "Failed to update password");
    }
} catch (PDOException $e) {
    json_response(false, "Database error updating password");
}
?>
