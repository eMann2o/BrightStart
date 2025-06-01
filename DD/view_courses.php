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

    // Fetch course details
    $courses_id = $_GET['module_id'] ?? null;
    if (!$courses_id) {
        echo "Module ID not specified.";
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM courses WHERE module_id = :module_id");
    $stmt->bindParam(':module_id', $courses_id, PDO::PARAM_INT);
    $stmt->execute();


    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Get module ID from URL
$courses_id = $_GET['module_id'] ?? null;
if (!$courses_id) {
    echo "Course ID not specified.";
    exit;
}

// Fetch module information
$module_stmt = $db->prepare("SELECT * FROM modules WHERE id = :module_id");
$module_stmt->bindParam(':module_id', $courses_id, PDO::PARAM_INT);
$module_stmt->execute();
$module = $module_stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    echo "Course not found.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($module['title']); ?></title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>        
        /* Responsive styles */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .modules-container {
                gap: 15px;
            }
            
            .module-card {
                max-width: 100%;
            }
        }
        
        @media screen and (max-width: 480px) {
            .module-title {
                font-size: 16px;
            }
            
            .module-description {
                font-size: 13px;
                -webkit-line-clamp: 2;
            }
            
            .module-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .module-image-container {
                height: 120px;
            }
        }
        
        /* Container for all module cards */
        .modules-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            justify-content: start;
        }
        
        /* Individual module card */
        .module-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 280px;
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
                <span>Courses</span>
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
                 <?php echo htmlspecialchars($module['title']); ?> Overview
            </h1>
        </div>
        
        <section class="content">
            <div class="modules-container">
                <?php
                // Fetch courses that belong to this module
                $stmt = $db->prepare("SELECT * FROM courses WHERE module_id = :module_id");
                $stmt->bindParam(':module_id', $courses_id, PDO::PARAM_INT);
                $stmt->execute();
                $courses = $stmt->fetchAll();

                foreach ($courses as $course) {
                    // Placeholder values
                    $completedCourses = 2;
                    $totalHours = 15;
                    $progressPercent = 100; // adjust if needed

                    echo '<div class="module-card" onclick="window.location.href=\'view_lessons.php?course_id=' . $course['id'] . '\'">';
                    echo '    <div class="module-image-container">';
                    echo '        <img src="chill.jpg" alt="Module illustration" class="module-image">';
                    echo '    </div>';
                    echo '    <div class="card-content">';
                    echo '        <img src="../logo.png" alt="Institution Logo" class="institution-logo">';
                    echo "        <h3 class='module-title'>{$course['title']}</h3>";
                    echo "        <p class='module-description'>{$course['description']}</p>";
                    echo '        <div class="course-count">';
                    echo '            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                    echo '                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>';
                    echo '            </svg>';
                    echo "            1 Course";
                    echo '        </div>';
                    echo '    </div>';
                    echo '</div>';
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