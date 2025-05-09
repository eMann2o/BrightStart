<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: index.html");
    exit();
  }
session_destroy();
header("Location: ../login.html");
exit;
?>