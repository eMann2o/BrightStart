<?php
include_once '../database.php';//include database connection file 
require 'db.php'; 

// Start the session at the beginning
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: ../index.html");
    exit();
  }
  


  try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query to fetch data from the table
    $stmt = $db->prepare("SELECT * FROM users"); // Replace 'employees' with your table name
    $stmt->execute();

    // Fetch all data from the query
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add a Course</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <link rel="stylesheet" href="styles/style.css">
        <style>
            .container {
                max-width: 1000px;
                margin: auto;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            }

            .container h2 {
                color: #2c3e50;
                text-align: center;
                margin-bottom: 30px;
                font-size: 28px;
                font-weight: 600;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #34495e;
                font-weight: 500;
                font-size: 16px;
            }

            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #dfe6e9;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s ease;
                background-color: #f8f9fa;
            }

            .form-control:focus {
                border-color: #3498db;
                outline: none;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
                background-color: #fff;
            }

            textarea.form-control {
                min-height: 150px;
                resize: vertical;
            }

            .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            max-width: 250px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .btn {
                width: 100%;
                padding: 15px 20px;
                font-size: 14px;
                max-width: none;
            }
        }

        @media screen and (max-width: 480px) {
            .btn {
                padding: 12px 16px;
                font-size: 13px;
            }
        }
        </style>
    </head>
    <body>
        <div class="sidebar">
            <div class="sidebar-logo">
                <h2>Bright<span>Start</span></h2>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item" onclick="window.location.href='dashboard.php';">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item active" onclick="window.location.href='courses.php';">
                    <i class="fas fa-book"></i>
                    <span>Modules</span>
                </div>
                <div class="menu-item" onclick="window.location.href='users.php';">
                    <i class="fas fa-users"></i>
                    <span>Participants</span>
                </div>
                
                <div class="menu-item" onclick="window.location.href='messages.php';">
                    <i class="fas fa-comment"></i>
                    <span>Messages</span>
                
                </div>
                
                
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search courses, students, or content...">
                    <i class="fas fa-search search-icon"></i>
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn" onclick="window.location.href='editpass.php';" title="Edit Password">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    
                    <div class="user-profile" onclick="window.location.href='profile.php';">
                        
                        <div class="user-info">
                            <div class="user-name"><?php
                            $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User";
                            echo htmlspecialchars($name);
                            ?> </div>
                            <div class="user-role"><?php
                            $role = isset($_SESSION['role']) ? $_SESSION['role'] : "Unknown User";
                            echo htmlspecialchars($role);
                            ?> </div>
                        </div>
                        <div class="user-avatar" onclick="window.location.href='logout.php';"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="welcome-section">
                
                <button class="customize-btn" onclick="window.location.href='addmodule.php';">
                    Add a module
                    <i class="fas fa-cog"></i>
                </button>
                <button class="customize-btn" onclick="window.location.href='addcourse.php';">
                    Add a course
                    <i class="fas fa-cog"></i>
                </button>
                <button class="customize-btn" onclick="window.location.href='addlesson.php';">
                    Add a lesson
                    <i class="fas fa-cog"></i>
                </button>
            </div>
            
            <section class="content">
                <div class="card">
                    <div class="container">
                        <!-- add_course.php -->
                        <form method="POST" action="addcourse.php">
                            <h2>Create New Course</h2>

                            <div class="form-group">
                                <label for="module_id" >Select Module:</label>
                                <select name="module_id" class="form-control" required>
                                    <?php
                                    require 'db.php';
                                    $modules = $pdo->query("SELECT id, title FROM modules")->fetchAll();
                                    foreach ($modules as $mod) {
                                        echo "<option value='{$mod['id']}'>{$mod['title']}</option>";
                                    }
                                    ?>
                                </select><br>
                            </div>
                            
                            
                            <div class="form-group">
                                <label>Course Title:</label>
                                <input type="text" class="form-control" name="title" maxlength="255" required><br>
                            </div>
                            
                            <div class="form-group">
                                <label>Description:</label>
                                <textarea name="description" class="form-control" required></textarea><br>
                            </div>
                            

                            <input type="submit" class="btn" value="Create Course">
                        </form>
                    </div>
                </div>
            </section>
            

            <script>
                

                // Sidebar toggle functionality
                document.querySelector('.menu-toggle').addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('collapsed');
                    document.querySelector('.main-content').classList.toggle('expanded');
                });

            </script>
        </div>
    </body>
</html>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';

    $module_id = $_POST['module_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Insert new course into the database
    $stmt = $pdo->prepare("INSERT INTO courses (module_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$module_id, $title, $description]);

    echo "<script type=\"text/javascript\">
                alert(\"✅ Course created successfully!\");
                window.location.href = \"courses.php\";
            </script>";
}
?>
