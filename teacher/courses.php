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
    <title>View courses</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>


.container {
  max-width: 1000px;
  margin: auto;
}

h2 {
  margin-bottom: 10px;
}

.search-bar {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.search-bar input {
  padding: 8px;
  width: 200px;
  margin-right: 8px;
}

.filter-btn {
  background: none;
  border: 1px solid #ccc;
  padding: 8px;
  cursor: pointer;
}

.add-course-btn {
  float: right;
  margin-bottom: 10px;
  background-color: #0061f2;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 4px;
  cursor: pointer;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background-color: #f2f2f2;
}

th, td {
  text-align: left;
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

.icon {
  margin-left: 6px;
}

.status.inactive {
  background-color: #d3dbe4;
  color: #333;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 12px;
  margin-left: 6px;
}

.highlight {
  color: #0073e6;
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
                <i class="fa-solid fa-upload"></i>
                <span>Video Upload</span>
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
                
                <div class="user-profile">
                    
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
            <h1 class="welcome-title">
                <i class="fas fa-chart-line"></i>
                 Modules Overview
            </h1>
        </div>
        
        <section class="content">
            <div class="card">
                <div class="container">
                    <h2>Modules</h2>
                    <?php
                    require 'db.php';

                    // Get user ID from session email
                    if (!isset($_SESSION['email'])) {
                        echo "You must be logged in.";
                        exit;
                    }

                    $email = $_SESSION['email'];
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if (!$user) {
                        echo "User not found.";
                        exit;
                    }

                    $user_id = $user['id'];
                    ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Module Title</th>
                                <th>Module Description</th>
                                <th>Course Title</th>
                                <th>Progress</th>
                                <th>View Lessons</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $modules = $pdo->query("SELECT * FROM modules")->fetchAll();

                            foreach ($modules as $module) {
                                $stmt = $pdo->prepare("SELECT * FROM courses WHERE module_id = ?");
                                $stmt->execute([$module['id']]);
                                $courses = $stmt->fetchAll();

                                if (count($courses) > 0) {
                                    $first_course = true;

                                    foreach ($courses as $course) {
                                        echo "<tr>";

                                        if ($first_course) {
                                            echo "<td rowspan='" . count($courses) . "'>{$module['title']}</td>";
                                            echo "<td rowspan='" . count($courses) . "'>{$module['description']}</td>";
                                            $first_course = false;
                                        }

                                        // Count total lessons for this course
                                        $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
                                        $total_stmt->execute([$course['id']]);
                                        $total_lessons = $total_stmt->fetchColumn();

                                        // Count completed lessons
                                        $completed_stmt = $pdo->prepare("
                                            SELECT COUNT(*) FROM progress 
                                            WHERE user_id = ? 
                                            AND lesson_id IN (SELECT id FROM lessons WHERE course_id = ?) 
                                            AND status = 'completed'");
                                        $completed_stmt->execute([$user_id, $course['id']]);
                                        $completed_lessons = $completed_stmt->fetchColumn();

                                        $percent = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

                                        echo "<td>{$course['title']}</td>";
                                        echo "<td>$completed_lessons / $total_lessons ({$percent}%)</td>";
                                        echo "<td><a href='view_lessons.php?course_id={$course['id']}'>View</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr>";
                                    echo "<td>{$module['title']}</td>";
                                    echo "<td>{$module['description']}</td>";
                                    echo "<td colspan='3'>No courses available for this module.</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>

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