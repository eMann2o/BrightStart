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

try {
    // Get total count and counts per role in one query
    $stmt = $db->query("
        SELECT 
            COUNT(*) AS total_lessons
        FROM lessons
    ");
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_lessons = $row['total_lessons'];

    

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brightstart Admin Dashboard</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <script>
        let activityChart;

        function fetchChartData(days) {
            fetch(`fetch_logins.php?days=${days}`)
                .then(response => response.json())
                .then(data => {
                    if (activityChart) activityChart.destroy();

                    const ctx = document.getElementById('activityChart').getContext('2d');
                    activityChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Unique Logins',
                                data: data.logins,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: '#4bc0c0',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                            }, {
                            label: 'Module Views',
                            data: [28, 18, 20, 15, 6, 13, 25, 30, 15, 1, 8, 2, 13],
                            borderColor: '#00d2ff',
                            backgroundColor: 'rgba(0, 210, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    stepSize: 1
                                }
                            }
                        }
                    });
                });
        }

        // Load default (7 days) on page load
        document.addEventListener('DOMContentLoaded', () => {
            fetchChartData(7);
        });
    </script>

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
        
        <div class="welcome-section">
            <h1 class="welcome-title">
                
                Dashboard Overview
            </h1>
            
            
        </div>
        
        <div class="dashboard-grid">            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="action-item" onclick="window.location.href='uploaded_videos.php';">
                    <div class="action-icon">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <div class="action-text">User Uploaded Files</div>
                </div>
                <div class="action-item" onclick="window.location.href='logged_in_users.php';">
                    <div class="action-icon">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                    <div class="action-text">User Logins</div>
                </div>
                <div class="action-item" onclick="window.location.href='quiz_results.php';">
                    <div class="action-icon">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                    </div>
                    <div class="action-text">Quiz Results</div>
                </div>
            </div>
        </div>
            

        <script>

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });
            
            // Initialize charts
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
                        }],
                        hoverOffset: 4
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