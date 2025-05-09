<?php
// Start session at the very beginning
session_start();


// Include database configuration
require_once '../database.php';

// Set content type to JSON for API-like responses
header('Content-Type: application/json');

// Function to return JSON error responses
function json_error($message, $field = null) {
    echo json_encode([
        'success' => false,
        'error' => $message,
        'field' => $field
    ]);
    exit();
}

try {
    // Create a PDO instance with secure settings
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    json_error("Database connection failed");
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Invalid request method");
}

// Validate CSRF token if you implement it
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     json_error("CSRF token validation failed");
// }

// Validate and sanitize inputs
$required_fields = ['name', 'phone', 'email', 'role', 'password', 'district', 'confirmPassword'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        json_error("Field $field is required", $field);
    }
}

$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$role = trim($_POST['role']);
$district = trim($_POST['district']);
$town = isset($_POST['town']) ? trim($_POST['town']) : '';
$organization = isset($_POST['organization']) ? trim($_POST['organization']) : '';
$password = $_POST['password'];
$confirm_password = $_POST['confirmPassword'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error("Invalid email format", 'email');
}

// Validate password strength
if (strlen($password) < 8) {
    json_error("Password must be at least 8 characters", 'password');
}

// Check password match
if ($password !== $confirm_password) {
    json_error("Passwords do not match", 'confirmPassword');
}

// Validate role against allowed values
$allowed_roles = ['Admin', 'SISO', 'Headteacher', 'Teacher'];
if (!in_array($role, $allowed_roles)) {
    json_error("Invalid user role", 'role');
}

// Check if email exists
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        json_error("Email already exists", 'email');
    }
} catch (PDOException $e) {
    json_error("Database error checking email");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Insert new user
try {
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO users (name, phone, email, role, district, town, organization, password, created_by, created_at) 
            VALUES (:name, :phone, :email, :role, :district, :town, :organization, :password, :created_by, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':town', $town);
    $stmt->bindParam(':district', $district);
    $stmt->bindParam(':organization', $organization);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':created_by', $_SESSION['email']);
    
    if ($stmt->execute()) {
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'redirect' => 'users.php'
        ]);
    } else {
        $pdo->rollBack();
        json_error("Failed to register user");
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    json_error("Database error: " . $e->getMessage());
}


?>