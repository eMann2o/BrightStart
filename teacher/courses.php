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
    <title>Modules</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        /* Container for all module cards */
        .modules-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Individual module card */
        .module-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 280px;
            display: grid;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            cursor: pointer;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        /* Module image container */
        .module-image-container {
            position: relative;
            height: 140px;
        }
        
        /* Module image */
        .module-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Status tag */
        .status-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .status-complete {
            background-color: #e7f5ea;
            color: #34a853;
        }
        
        .status-in-progress {
            background-color: #fff8e1;
            color: #fbbc04;
        }
        
        .status-not-started {
            background-color: #f5f5f5;
            color: #5f6368;
        }
        
        /* Card content */
        .card-content {
            padding: 15px;
        }
        
        /* Institution logo */
        .institution-logo {
            height: 30px;
            margin-bottom: 10px;
        }
        
        /* Module title */
        .module-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #333;
            line-height: 1.3;
        }
        
        /* Module description */
        .module-description {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
        }
        
        /* Course count */
        .course-count {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #4285F4;
            margin-top: 12px;
        }
        
        /* Progress bar container */
        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            margin-top: 15px;
        }
        
        /* Progress bar */
        .progress-bar {
            height: 100%;
            border-radius: 3px;
            background-color: #4285F4;
        }
        
        /* Module info container */
        .module-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 12px;
            color: #5f6368;
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
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
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
            <h1 class="welcome-title">
                 Modules
            </h1>
        </div>
        
        <section class="content">
            <div class="modules-container">
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

                <?php
                $modules = $pdo->query("SELECT * FROM modules")->fetchAll();

                foreach ($modules as $module) {
                    // Get courses for the module
                    $stmt = $pdo->prepare("SELECT id FROM courses WHERE module_id = ?");
                    $stmt->execute([$module['id']]);
                    $course_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    $course_count = count($course_ids);

                    if ($course_count > 0) {
                        // Count total lessons across all courses
                        $in_clause = implode(',', array_fill(0, $course_count, '?'));
                        $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id IN ($in_clause)");
                        $total_stmt->execute($course_ids);
                        $total_lessons = $total_stmt->fetchColumn();

                        // Count completed lessons
                        $completed_stmt = $pdo->prepare("
                            SELECT COUNT(*) FROM progress 
                            WHERE user_id = ? 
                            AND LOWER(status) = 'completed' 
                            AND lesson_id IN (
                                SELECT id FROM lessons WHERE course_id IN ($in_clause)
                            )");
                        $completed_stmt->execute(array_merge([$user_id], $course_ids));
                        $completed_lessons = $completed_stmt->fetchColumn();

                        // Progress calculation
                        $percent = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

                        // Status class
                        $status_class = 'status-not-started';
                        $status_text = 'Not Started';
                        
                        if ($percent > 0 && $percent < 100) {
                            $status_class = 'status-in-progress';
                            $status_text = 'In Progress';
                        } elseif ($percent == 100) {
                            $status_class = 'status-complete';
                            $status_text = 'Completed';
                        } elseif ($percent == 0) {
                            $status_class = 'status-not-started';
                            $status_text = 'Not Started';
                        }
                        echo '                        
                            <div class="module-card" onclick="window.location.href=\'view_courses.php?module_id=' . $module['id'] . '\'">
                                <div class="module-image-container">
                                    <img src="chill.jpg" alt="Module illustration" class="module-image">
                                    <div class="status-tag ' . $status_class . '">' . $status_text . '</div>
                                </div>
                                <div class="card-content">
                                    <img src="../logo.png" alt="Institution Logo" class="institution-logo">
                                    <h3 class="module-title">' . htmlspecialchars($module["title"]) . '</h3>
                                    <p class="module-description">' . htmlspecialchars($module["description"]) . '</p>
                                    <div class="course-count">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                                        </svg>
                                        ' . $course_count . ' Courses
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: ' . $percent . '%;"></div>
                                    </div>
                                    <div class="module-info">
                                        <span>' . $completed_lessons . ' of ' . $total_lessons . ' lessons completed</span>
                                    </div>
                                </div>
                            </div>';
                    }
                }
                ?>



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