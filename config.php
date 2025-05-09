<?php
include_once 'database.php';

session_start(); 

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // Redirect to login page if not logged in
  header("Location: index.html");
  exit();
}

// Create the DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    // Create a new PDO instance for the database connection
    $db = new PDO($dsn, $username_db, $password_db);
    
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set the default fetch mode
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle the connection error
    error_log("Database Connection failed: " . $e->getMessage()); // Log the error
    echo 'Database Connection failed. Please try again later.';
    exit;
}
?>