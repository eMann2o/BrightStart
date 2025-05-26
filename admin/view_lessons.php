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
    <title>Lessons</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>


.container {
  max-width: 1000px;
  margin: auto;
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
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


.courses-header {
      margin-bottom: 30px;
    }
    
    .courses-header h1 {
      font-size: 28px;
      color: #2c3e50;
      font-weight: 600;
    }
    
    .progress-container {
      margin-bottom: 25px;
    }
    
    .progress-text {
      font-size: 18px;
      margin-bottom: 10px;
      color: #2c3e50;
      font-weight: 500;
    }
    
    .progress-bar-container {
      height: 20px;
      width: 100%;
      background-color: #f0f0f0;
      border-radius: 10px;
      box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 3px;
      margin-bottom: 5px;
      position: relative;
      overflow: hidden;
    }
    
    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #4776E6, #8E54E9);
      border-radius: 8px;
      transition: width 1s ease;
      position: relative;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(142, 84, 233, 0.4);
      }
      70% {
        box-shadow: 0 0 0 5px rgba(142, 84, 233, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(142, 84, 233, 0);
      }
    }

    .progress-percentage {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      color: #fff;
      font-size: 12px;
      font-weight: bold;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
      z-index: 2;
    }
    
    /* Adding animated stripes for progress bar */
    .progress-bar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, 0.2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.2) 50%,
        rgba(255, 255, 255, 0.2) 75%,
        transparent 75%,
        transparent
      );
      background-size: 20px 20px;
      animation: move 1s linear infinite;
      z-index: 1;
      border-radius: 8px;
    }
    
    @keyframes move {
      0% {
        background-position: 0 0;
      }
      100% {
        background-position: 20px 0;
      }
    }
    
    .lessons-header {
      padding-bottom: 10px;
      margin-bottom: 15px;
      border-bottom: 1px solid #e9ecef;
      font-size: 22px;
      color: #2c3e50;
    }
    
    .lesson-card {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 15px;
      transition: all 0.3s ease;
      border-left: 5px solid #3498db;
      width: fit-content;
    }
    
    .lesson-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    
    .lesson-title {
      font-size: 20px;
      font-weight: 600;
      color: #3498db;
      margin-bottom: 5px;
    }
    
    .lesson-subtitle {
      color: #7f8c8d;
      font-size: 14px;
      margin-bottom: 15px;
    }
    
    .status {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .status-label {
      font-weight: 500;
      margin-right: 8px;
    }
    
    .status-completed {
      color: #27ae60;
      font-weight: 600;
    }
    
    .lesson-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    
    .action-button {
      padding: 8px 16px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: all 0.2s ease;
      font-weight: 500;
    }
    
    .video-button {
      background-color: #9b59b6;
      color: white;
    }
    
    .video-button:hover {
      background-color: #8e44ad;
    }
    
    .download-button {
      background-color: #f1f1f1;
      color: #555;
    }
    
    .download-button:hover {
      background-color: #e6e6e6;
    }
    
    .icon {
      display: inline-block;
      width: 16px;
      height: 16px;
    }

    @media (max-width: 600px) {
      .container {
        margin: 20px;
        padding: 15px;
      }
      
      .progress-text {
        font-size: 16px;
      }
      
      .lesson-title {
        font-size: 18px;
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
        
        <section class="content">
            <div class="card">
              <h2 class="lessons-header">Lessons</h2>
                <div class="container">
                    <?php
                    require 'db.php';

                    // Validate session
                    if (!isset($_SESSION['email'])) {
                        echo "You must be logged in to view this page.";
                        exit;
                    }

                    $email = $_SESSION['email'];

                    // Get user ID from email
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if (!$user) {
                        echo "User not found.";
                        exit;
                    }

                    $user_id = $user['id'];

                    // Check course ID
                    if (!isset($_GET['course_id'])) {
                        echo "Invalid course ID";
                        exit;
                    }

                    $course_id = $_GET['course_id'];

                    // Get all lessons for this course
                    $lesson_stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ?");
                    $lesson_stmt->execute([$course_id]);
                    $lessons = $lesson_stmt->fetchAll();
                    foreach ($lessons as $lesson) {
                        echo "<div class=\"lesson-card\">";
                        echo "<h3 class=\"lesson-title\">{$lesson['title']}</h3>";                        
                        echo "<p class=\"lesson-subtitle\">{$lesson['content']}</p>"; 

                        echo "
                            <div class=\"lesson-actions\">
                                <a href='view_video.php?lesson_id={$lesson['id']}' target='_blank' class=\"action-button video-button\">
                                <span class=\"icon\">▶</span> Watch Video
                                </a>";
                                if ($lesson['file_attachment']) {
                                    echo "<a href='download_file.php?lesson_id={$lesson['id']}' class=\"action-button download-button\"><span class=\"icon\">⬇</span> Download File</a>";
                                }
                        echo "</div>";

                        echo "<hr>";
                        echo "</div>";
                    }
                    ?>
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