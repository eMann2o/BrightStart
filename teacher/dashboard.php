<?php
include_once '../database.php';//include database connection file  

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
      $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('siso', 'teacher', 'headteacher');"); 
      $stmt->execute();
  
      // Fetch all data from the query
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  } catch (PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
      exit();
}

try {
    // Get total count and counts per role in one query
    $stmt = $db->query("
        SELECT 
            COUNT(*) AS total,
            SUM(role = 'siso') AS siso_count,
            SUM(role = 'teacher') AS teacher_count,
            SUM(role = 'headteacher') AS headteacher_count,
            SUM(role = 'admin') AS admin_count
        FROM users
    ");
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = $row['total'];
    $siso = $row['siso_count'];
    $teacher = $row['teacher_count'];
    $headteacher = $row['headteacher_count'];
    $admin = $row['admin_count'];

    // Calculate percentages
    $siso_percent = $total > 0 ? round(($siso / $total) * 100, 2) : 0;
    $teacher_percent = $total > 0 ? round(($teacher / $total) * 100, 2) : 0;
    $headteacher_percent = $total > 0 ? round(($headteacher / $total) * 100, 2) : 0;
    $admin_percent = $total > 0 ? round(($admin / $total) * 100, 2) : 0;

    

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brightstart Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .dashboard-container {
            display: flex;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .panel {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            flex: 1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .panel h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-row:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
            font-size: 15px;
        }
        
        .stat-value {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }
        
        .icon {
            width: 24px;
            height: 24px;
            opacity: 0.7;
        }
        
        .chart-container {
            height: 280px;
            position: relative;
        }
        
        .role-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
        }
        
        .color-box {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Bright<span>Start</span></h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" onclick="window.location.href='dashboard.php';">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="window.location.href='courses.php';">
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
                        <div class="user-name">
                            <?php
                            $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User";
                            echo htmlspecialchars($name);
                            ?>
                        </div>
                        <div class="user-role">
                            <?php
                            $role = isset($_SESSION['role']) ? $_SESSION['role'] : "Unknown User";
                            echo htmlspecialchars($role);
                            ?>
                        </div>
                    </div>
                    <div class="user-avatar" onclick="window.location.href='logout.php';"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                </div>
            </div>
        </div>
        
        <div class="welcome-section">
            <h1 class="welcome-title">
                <i class="fas fa-chart-line"></i>
                Dashboard Overview
            </h1>
            
            
        </div>
        
        <div class="dashboard-grid">
           
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="action-item" onclick="window.location.href='useradd.php';">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-text">Courses</div>
                </div>
                <div class="action-item">
                    <div class="action-icon">
                        <i class="fa-solid fa-upload"></i>
                    </div>
                    <div class="action-text">Upload Videos</div>
                </div>
                <div class=>
                    <div class=>
                       
                    </div>
                    
                </div>
                <div class="action-item">
                    <div class="action-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="action-text">Messages</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="dashboard-container">
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
                    $modules = $pdo->query("SELECT * FROM modules")->fetchAll();

                    foreach ($modules as $module) {
                        $stmt = $pdo->prepare("SELECT * FROM courses WHERE module_id = ?");
                        $stmt->execute([$module['id']]);
                        $courses = $stmt->fetchAll();

                        $incomplete_courses = [];

                        foreach ($courses as $course) {
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

                            // If incomplete, include in the display
                            if ($completed_lessons < $total_lessons) {
                                $course['completed'] = $completed_lessons;
                                $course['total'] = $total_lessons;
                                $incomplete_courses[] = $course;
                            }
                        }

                        // Output only if there are incomplete courses
                        if (count($incomplete_courses) > 0) {
                            $first_course = true;

                            foreach ($incomplete_courses as $course) {
                                echo "<tr>";

                                if ($first_course) {
                                    echo "<td rowspan='" . count($incomplete_courses) . "'>{$module['title']}</td>";
                                    echo "<td rowspan='" . count($incomplete_courses) . "'>{$module['description']}</td>";
                                    $first_course = false;
                                }

                                $percent = $course['total'] > 0 ? round(($course['completed'] / $course['total']) * 100) : 0;

                                echo "<td>{$course['title']}</td>";
                                echo "<td>{$course['completed']} / {$course['total']} ({$percent}%)</td>";
                                echo "<td><a href='view_lessons.php?course_id={$course['id']}'>View</a></td>";
                                echo "</tr>";
                            }
                        }
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Initialize charts
            document.addEventListener('DOMContentLoaded', function() {
                // Sample activity chart
                const activityCtx = document.getElementById('activityChart').getContext('2d');
                const activityChart = new Chart(activityCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Student Logins',
                            data: [65, 59, 80, 81, 56, 40, 70],
                            borderColor: '#3a7bd5',
                            backgroundColor: 'rgba(58, 123, 213, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Course Views',
                            data: [28, 48, 40, 45, 46, 30, 50],
                            borderColor: '#00d2ff',
                            backgroundColor: 'rgba(0, 210, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });


            /////////////////////////////////////////////////////////
            document.addEventListener('DOMContentLoaded', function() {
            // Calculate total
            const total = userData.reduce((sum, item) => sum + item.count, 0);
            userData.forEach(item => {
                item.percentage = ((item.count / total) * 100).toFixed(1);
            });

            // Create pie chart
            const ctx = document.getElementById('userChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: userData.map(item => item.role),
                    datasets: [{
                        data: userData.map(item => item.count),
                        backgroundColor: userData.map(item => item.color),
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });

        </script>
    </div>
</body>
</html>