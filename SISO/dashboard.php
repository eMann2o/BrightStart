<?php
include_once '../database.php'; // Include database connection file

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

    // Fetch users with specific roles
    $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('siso', 'teacher', 'headteacher');");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // Count total lessons
    $total_stmt = $db->query("SELECT COUNT(*) FROM lessons");
    $total_lessons = $total_stmt->fetchColumn();

    // Get the current user ID (assuming it's stored in session)
    $user_email = $_SESSION['email'];
    $user_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $user_stmt->execute([$user_email]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user ? $user['id'] : 0;

    // Count user completed lessons
    $completed_stmt = $db->prepare("
        SELECT COUNT(*) FROM progress 
        WHERE user_id = ? 
        AND status = 'completed'
    ");
    $completed_stmt->execute([$user_id]);
    $completed_lessons = $completed_stmt->fetchColumn();

    // Calculate remaining lessons
    $remaining = $total_lessons - $completed_lessons;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}



try {
    // Create a new PDO instance
    $database = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch users with specific roles
    $userQuery = $database->prepare("SELECT * FROM users WHERE role IN ('siso', 'teacher', 'headteacher');");
    $userQuery->execute();
    $userRows = $userQuery->fetchAll(PDO::FETCH_ASSOC);

    // Get total count and counts per role in one query
    $roleCountQuery = $database->query("
        SELECT 
            COUNT(*) AS total,
            SUM(role = 'siso') AS siso_count,
            SUM(role = 'teacher') AS teacher_count,
            SUM(role = 'headteacher') AS headteacher_count,
            SUM(role = 'admin') AS admin_count
        FROM users
    ");
    
    $roleData = $roleCountQuery->fetch(PDO::FETCH_ASSOC);

    $totalUsers = $roleData['total'];
    $sisoCount = $roleData['siso_count'];
    $teacherCount = $roleData['teacher_count'];
    $headteacherCount = $roleData['headteacher_count'];
    $adminCount = $roleData['admin_count'];

    // Calculate percentages
    $sisoPercentage = $totalUsers > 0 ? round(($sisoCount / $totalUsers) * 100, 2) : 0;
    $teacherPercentage = $totalUsers > 0 ? round(($teacherCount / $totalUsers) * 100, 2) : 0;
    $headteacherPercentage = $totalUsers > 0 ? round(($headteacherCount / $totalUsers) * 100, 2) : 0;
    $adminPercentage = $totalUsers > 0 ? round(($adminCount / $totalUsers) * 100, 2) : 0;

    // Count total lessons
    $lessonCountQuery = $database->query("SELECT COUNT(*) FROM lessons");
    $totalLessons = $lessonCountQuery->fetchColumn();

    // Get the current user ID (assuming it's stored in session)
    $currentUserEmail = $_SESSION['email'];
    $userIdQuery = $database->prepare("SELECT id FROM users WHERE email = ?");
    $userIdQuery->execute([$currentUserEmail]);
    $currentUserData = $userIdQuery->fetch(PDO::FETCH_ASSOC);
    $currentUserId = $currentUserData ? $currentUserData['id'] : 0;

    // Count user completed lessons
    $completedLessonsQuery = $database->prepare("
        SELECT COUNT(*) FROM progress 
        WHERE user_id = ? 
        AND status = 'completed'
    ");
    $completedLessonsQuery->execute([$currentUserId]);
    $completedLessonsCount = $completedLessonsQuery->fetchColumn();

    $completedLessonsCount = (int) $completedLessonsCount;
    $totalLessons = (int) $totalLessons;
    $incompleteLessonsCount = max($totalLessons - $completedLessonsCount, 0);

    // Calculate remaining lessons
    $remainingLessons = $totalLessons - $completedLessonsCount;

} catch (PDOException $exception) {
    echo "Database Error: " . $exception->getMessage();
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brightstart SISO/STEM-Coordinator's Dashboard</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
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

        .modules{
            justify-content: space-around;
            display: grid;
        }

        .no-courses {
            text-align: center;
            font-style: italic;
            padding: 20px;
            color: #777;
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
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fas fa-users"></i>
                <span>Participants</span>
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
        
        <div class="dashboard-grid">
           
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="action-item" onclick="window.location.href='courses.php';">
                    <div class="action-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="action-text">Courses</div>
                </div>
                <div class="action-item" onclick="window.location.href='messages.php';">
                    <div class="action-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="action-text">Messages</div>
                </div>
                <div class="action-item" onclick="window.location.href='my_files.php';">
                    <div class="action-icon">
                        <i class="fa-solid fa-file"></i>
                    </div>
                    <div class="action-text">My Uploaded Files</div>
                </div>
                <div class="action-item" onclick="window.location.href='videoupload.php';">
                    <div class="action-icon">
                        <i class="fa-solid fa-upload"></i>
                    </div>
                    <div class="action-text">Upload Files</div>
                </div>
            </div>

            <div class="card">
                <div class="lesson-summary-chart" style="max-width: 400px; margin: auto;">
                    <canvas id="lessonsDoughnut"></canvas>
                </div>
                
            </div>
        </div>
        <div class="modules">
            <h2>Incomplete Courses</h2>
        </div>
        
        <?php
        require 'db.php';

        // Get the logged-in user's ID
        function getUserIdByEmail($pdo) {
            if (!isset($_SESSION['email'])) {
                echo "You must be logged in.";
                exit;
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
            $user = $stmt->fetch();

            if (!$user) {
                echo "User not found.";
                exit;
            }

            return $user['id'];
        }

        // Reusable card rendering function
        function renderModuleCard($module, $course, $percent, $courseCount) {
            echo '<div class="module-card" onclick="window.location.href=\'view_courses.php?module_id=' . $module['id'] . '\'">';
            echo '  <div class="module-image-container">';
            echo '      <img src="chill.jpg" alt="Module illustration" class="module-image">';
            echo '  </div>';
            echo '  <div class="card-content">';
            echo '      <img src="../logo.PNG" alt="Institution Logo" class="institution-logo">';
            echo "      <h3 class=\"module-title\">{$module['title']}</h3>";
            echo "      <p class=\"module-description\">{$module['description']}</p>";
            echo '      <div class="course-count">';
            echo '          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                                                    </svg>';
            echo $courseCount . ' Module' . ($courseCount !== 1 ? 's' : '');
            echo '      </div>';
            echo "      <div class=\"progress-container\">";
            echo "          <div class=\"progress-bar\" style=\"width: {$percent}%;\"></div>";
            echo "      </div>";
            echo "      <div class=\"module-info\">";
            echo "          <span>{$course['completed']} of {$course['total']} completed</span>";
            echo "      </div>";
            echo "  </div>";
            echo "</div>";
        }

        $user_id = getUserIdByEmail($pdo);
        $modules = $pdo->query("SELECT * FROM modules")->fetchAll();

        $hasIncomplete = false;
        $hasComplete = false;
        ?>

        <!-- Ongoing Modules Section -->
        <div class="dashboard-container">
            <?php
            foreach ($modules as $module) {
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE module_id = ?");
                $stmt->execute([$module['id']]);
                $courses = $stmt->fetchAll();

                $incompleteCourses = [];

                foreach ($courses as $course) {
                    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
                    $total_stmt->execute([$course['id']]);
                    $total_lessons = $total_stmt->fetchColumn();

                    $completed_stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM progress 
                        WHERE user_id = ? 
                        AND lesson_id IN (SELECT id FROM lessons WHERE course_id = ?) 
                        AND status = 'completed'");
                    $completed_stmt->execute([$user_id, $course['id']]);
                    $completed_lessons = $completed_stmt->fetchColumn();

                    if ($total_lessons > 0 && $completed_lessons < $total_lessons) {
                        $course['total'] = $total_lessons;
                        $course['completed'] = $completed_lessons;
                        $incompleteCourses[] = $course;
                    }
                }

                $courseCount = count($incompleteCourses);
                if ($courseCount > 0) {
                    foreach ($incompleteCourses as $course) {
                        $percent = ($course['completed'] / $course['total']) * 100;
                        renderModuleCard($module, $course, $percent, $courseCount);
                        $hasIncomplete = true;
                    }
                }
            }

            if (!$hasIncomplete) {
                echo '<div class="no-courses">No new modules found.</div>';
            }
            ?>
        </div>

        <!-- Completed Modules Section -->
        <div class="modules">
            <h2>Completed Courses</h2>
        </div>

        <div class="dashboard-container">
            <?php
            foreach ($modules as $module) {
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE module_id = ?");
                $stmt->execute([$module['id']]);
                $courses = $stmt->fetchAll();

                $completeCourses = [];

                foreach ($courses as $course) {
                    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
                    $total_stmt->execute([$course['id']]);
                    $total_lessons = $total_stmt->fetchColumn();

                    $completed_stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM progress 
                        WHERE user_id = ? 
                        AND lesson_id IN (SELECT id FROM lessons WHERE course_id = ?) 
                        AND status = 'completed'");
                    $completed_stmt->execute([$user_id, $course['id']]);
                    $completed_lessons = $completed_stmt->fetchColumn();

                    if ($total_lessons > 0 && $completed_lessons == $total_lessons) {
                        $course['total'] = $total_lessons;
                        $course['completed'] = $completed_lessons;
                        $completeCourses[] = $course;
                    }
                }

                $courseCount = count($completeCourses);
                if ($courseCount > 0) {
                    foreach ($completeCourses as $course) {
                        $percent = 100;
                        renderModuleCard($module, $course, $percent, $courseCount);
                        $hasComplete = true;
                    }
                }
            }

            if (!$hasComplete) {
                echo '<div class="no-courses">No completed course found.</div>';
            }
            ?>
        </div>



        <script>
            // Initialize charts
            // Wait for DOM to be fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Get the canvas element
                const canvas = document.getElementById('lessonsDoughnut');
                
                if (!canvas) {
                    console.error('Canvas element with id "lessonsDoughnut" not found');
                    return;
                }

                // Initialize chart data with proper validation
                const completedLessons = <?= json_encode((int)$completedLessonsCount) ?>;
                const totalLessons = <?= json_encode((int)$totalLessons) ?>;
                
                console.log('Completed lessons:', completedLessons);
                console.log('Total lessons:', totalLessons);
                
                // Calculate incomplete lessons with validation
                const incompleteLessons = Math.max(0, totalLessons - completedLessons);
                
                // Check if we have valid data
                if (totalLessons <= 0) {
                    // Handle case where there are no lessons
                    const ctx = canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['No lessons available'],
                            datasets: [{
                                data: [1],
                                backgroundColor: ['#e0e0e0'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'No Lessons Available',
                                    font: { size: 16 }
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                    return;
                }

                // Create the chart with valid data
                const ctx = canvas.getContext('2d');
                const lessonsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'Remaining'],
                        datasets: [{
                            data: [completedLessons, incompleteLessons],
                            backgroundColor: [
                                '#4CAF50', // Green for completed
                                '#fbbc04'  // Light gray for remaining
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverBackgroundColor: [
                                '#45a049',
                                '#d0d0d0'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%', // Makes it a proper doughnut (not pie)
                        plugins: {
                            title: {
                                display: true,
                                text: `Lesson Progress: ${completedLessons} of ${totalLessons} completed`,
                                font: { 
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: {
                                    top: 10,
                                    bottom: 20
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const percentage = totalLessons > 0 ? ((value / totalLessons) * 100).toFixed(1) : 0;
                                        return `${context.label}: ${value} lessons (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
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