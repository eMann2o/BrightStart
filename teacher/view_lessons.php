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

$courses_id = $_GET['course_id'] ?? null;
if (!$courses_id) {
    echo "Course ID not specified.";
    exit;
}

// Fetch course information
$course_stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id");
$course_stmt->bindParam(':course_id', $courses_id, PDO::PARAM_INT);
$course_stmt->execute();
$course = $course_stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "Course not found.";
    exit;
}

?>
<?php
require 'db.php';

// === Helper Functions ===

function getUserIdByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ? $user['id'] : null;
}

function getLessonsByCourse($pdo, $course_id) {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function getCourseProgress($pdo, $user_id, $course_id) {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $total_stmt->execute([$course_id]);
    $total = $total_stmt->fetchColumn();

    $completed_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM progress 
        WHERE user_id = ? AND lesson_id IN (
            SELECT id FROM lessons WHERE course_id = ?
        ) AND status = 'completed'
    ");
    $completed_stmt->execute([$user_id, $course_id]);
    $completed = $completed_stmt->fetchColumn();

    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;

    return [$completed, $total, $percent];
}

function getLessonProgress($pdo, $user_id, $lesson_id) {
    $stmt = $pdo->prepare("SELECT status FROM progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $result = $stmt->fetch();
    return $result ? $result['status'] : 'not_started';
}

function renderLessonCard($lesson, $status) {
    ob_start();
    ?>
    <div class="lesson-card">
        <div class="card-header">
            <h2><?= htmlspecialchars($lesson['title']) ?></h2>
        </div>
        <div class="card-body">
            <div class="meta">
                <span>Status: <?= htmlspecialchars($status) ?></span>
            </div>
            <div class="lesson-actions">
                <a href="view_video.php?lesson_id=<?= $lesson['id'] ?>" target="_blank" class="action-button video-button"  style="text-decoration: none;">
                    <i class="fa-solid fa-play"></i>Watch Video
                </a>
                <?php if (!empty($lesson['file_attachment'])): ?>
                    <a href="download_file.php?lesson_id=<?= $lesson['id'] ?>" class="action-button download-button" style="text-decoration: none;">
                        <i class="fa-solid fa-download"></i> Download File
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
      :root {
          --primary: #4361ee;
          --primary-light: #e6e9ff;
          --text: #2b2d42;
          --text-light: #8d99ae;
          --background: #f8f9fa;
          --card-bg: #ffffff;
          --border: #e9ecef;
          --success: #4cc9f0;
          --error: #f72585;
          --warning: #f8961e;
          --radius: 8px;
          --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      }

      .course-content {
          padding: 2rem 0;
      }

      .course-container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 1rem;
      }

      .alert {
          padding: 1rem;
          border-radius: var(--radius);
          margin-bottom: 1.5rem;
          font-weight: 500;
      }

      .alert.error {
          background-color: #fde8e8;
          color: var(--error);
          border-left: 4px solid var(--error);
      }

      .progress-container {
          margin-bottom: 2.5rem;
          background: var(--card-bg);
          padding: 1.5rem;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
      }

      .progress-info {
          display: flex;
          justify-content: space-between;
          margin-bottom: 0.75rem;
          font-size: 0.95rem;
      }

      .progress-label {
          font-weight: 600;
          color: var(--text);
      }

      .progress-stats {
          color: var(--text-light);
      }

      .progress-track {
          height: 10px;
          background-color: var(--primary-light);
          border-radius: 5px;
          overflow: hidden;
          position: relative;
      }

      .progress-fill {
          height: 100%;
          background-color: var(--primary);
          border-radius: 5px;
          transition: width 0.3s ease;
      }

      .section-title {
          font-size: 1.5rem;
          margin-bottom: 1.5rem;
          color: var(--text);
          font-weight: 600;
      }

      .lessons-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
          gap: 1.5rem;
          margin-top: 1rem;
      }

      .lesson-card {
            width: 320px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .lesson-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px;
            text-align: center;
        }

        .card-body {
            padding: 20px;
        }

        .action-button{
          margin-bottom: 0.3rem;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: var(--text);
            font-size: 14px;
        }

        .video-thumbnail {
            position: relative;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
            cursor: pointer;
        }

        .thumbnail-img {
            width: 100%;
            opacity: 0.8;
            transition: opacity 0.3s;
            display: block;
        }

        .video-thumbnail:hover .thumbnail-img {
            opacity: 0.6;
        }

        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s;
        }

        .play-btn::after {
            content: "";
            display: block;
            width: 0;
            height: 0;
            border-top: 12px solid transparent;
            border-bottom: 12px solid transparent;
            border-left: 20px solid var(--primary);
            margin-left: 4px;
        }

        .play-btn:hover {
            transform: translate(-50%, -50%) scale(1.1);
            background: white;
        }

        .key-takeaways {
            margin-top: 20px;
        }

        .key-takeaways li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }

        .key-takeaways li::before {
            content: "•";
            color: var(--primary);
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .pro-tip {
            background: #f8f9fa;
            border-left: 4px solid var(--primary);
            padding: 12px;
            margin-top: 20px;
            font-size: 14px;
            border-radius: 0 4px 4px 0;
        }
      @media (max-width: 1000px) {
          .lessons-grid {
              grid-template-columns: 1fr;
          }
          
          .progress-info {
              flex-direction: column;
              gap: 0.5rem;
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
                <div class="user-profile" >
                    <div class="user-avatar" style="color:blue;"onclick="window.location.href='profile.php';"><i class="fa-solid fa-user"></i></div>
                </div>
                
                <div class="user-profile" >
                    
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
        
        <section class="course-content">
            
          <div class="course-container">
            <div class="pro">
                    <div class="cart" style="background-color: white; border-radius: 12px; margin-bottom: 30px; padding: 25px;">
                        <h2><?php echo htmlspecialchars($course['title']); ?> Description</h2>
                        <pre style="white-space: pre-wrap; word-wrap: break-word; overflow: auto; max-width: 1000px; font-family: inherit; padding: 10px; border-radius: 8px;"><?php echo htmlspecialchars($course['description']); ?></pre>
                    </div>
                </div>
              <?php
              // Authentication check
              if (!isset($_SESSION['email'])) {
                  echo "<div class='alert error'>You must be logged in to view this page.</div>";
                  exit;
              }

              // Course ID validation
              if (!isset($_GET['course_id'])) {
                  echo "<div class='alert error'>Invalid Course ID.</div>";
                  exit;
              }

              $email = $_SESSION['email'];
              $course_id = $_GET['course_id'];

              // User verification
              $user_id = getUserIdByEmail($pdo, $email);
              if (!$user_id) {
                  echo "<div class='alert error'>User not found.</div>";
                  exit;
              }

              // Get course data 
              $lessons = getLessonsByCourse($pdo, $course_id);
              [$completed, $total, $percent] = getCourseProgress($pdo, $user_id, $course_id);
              ?>
              
              <div class="progress-container">
                  <div class="progress-info">
                      <span class="progress-label">Module Progress:</span>
                      <span class="progress-stats"><?= $completed ?> / <?= $total ?> lessons completed (<?= $percent ?>%)</span>
                  </div>
                  <div class="progress-track">
                      <div class="progress-fill" style="width:<?= $percent ?>%"></div>
                  </div>
              </div>

              <h2 class="section-title">Lessons</h2>
              
              <div class="lessons-grid">
                  <?php foreach ($lessons as $lesson): 
                      $status = getLessonProgress($pdo, $user_id, $lesson['id']);
                      echo renderLessonCard($lesson, $status);
                  endforeach; ?>
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