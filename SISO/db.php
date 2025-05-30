<?php
/**
 * Database Connection File
 * 
 * This file establishes a PDO connection to the database.
 */

// Database connection parameters
$db_host = 'localhost';       // Database host
$db_name = 'brightstart';     // Database name
$db_user = 'root';            // Database username 
$db_pass = '';                // Database password (default empty for XAMPP)
$db_charset = 'utf8mb4';      // Character set

// DSN (Data Source Name)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";

// PDO options for better error handling and performance
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Fetch as associative array by default
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Use real prepared statements
];

// Establish connection
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Die with error message
    die('Database Connection Failed: ' . $e->getMessage());
}

?>