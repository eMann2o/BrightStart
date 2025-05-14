<?php
include_once 'database.php';//include database connection file  

// Start the session at the beginning
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

    try {
        // Create a new PDO instance
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // Check if the user exists in the database
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check if the email exists in the database
        if (!$user) {
            echo '<script type="text/javascript">
                    alert("No user found with this email");
                    window.location.href = "index.html";
                </script>';
            exit;
        }
    
        // Verify password using password_verify()
        if (password_verify($password, $user["password"])) {
            // Password matches, set session variables
            $_SESSION["email"] = $user["email"];
            $_SESSION["name"] = htmlspecialchars($user["name"]); // Store name in session
            $_SESSION["role"] = htmlspecialchars($user["role"]); // Store role
    
            // Redirect based on role
            switch ($_SESSION["role"]) {
                case "Admin":
                    header("Location: admin/dashboard.php");
                    break;
                case "siso":
                    header("Location: SISO/dashboard.php");
                    break;
                case "headteacher":
                    header("Location: headteacher/dashboard.php");
                    break;
                case "Teacher":
                    header("Location: teacher/dashboard.php");
                    break;
                default:
                    echo '<script type="text/javascript">
                            alert("Unauthorized role");
                            window.location.href = "index.html";
                        </script>';
                    break;
            }
            exit; // Ensure no further code is executed after redirection
        } else {
            // Password does not match
            echo '<script type="text/javascript">
                    alert("Invalid password");
                    window.location.href = "index.html";
                </script>';
        }
    } catch (PDOException $e) {
        // Handle any errors
        error_log("Connection failed: " . $e->getMessage()); // Log error instead of displaying
        echo "An error occurred. Please try again later.";
    } finally {
        // Close the database connection
        $db = null; // Optional but good practice
    }
}
?>
