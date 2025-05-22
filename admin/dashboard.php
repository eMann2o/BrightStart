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
    <title>Brightstart Admin Dashboard</title>
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
                            label: 'Course Views',
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
                    <h2 class="card-title">Portal Activity</h2>
                    <select id="activity-range" onchange="fetchChartData(this.value)" class="dropdown-select">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 3 Months</option>
                    </select>

                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="action-item" onclick="window.location.href='useradd.php';">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-text">Add New User</div>
                </div>
                <div class="action-item" onclick="window.location.href='addcourse.php';">
                    <div class="action-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="action-text">Create Course</div>
                </div>
                <div class=>
                    <div class=>
                       
                    </div>
                    
                </div>
                <div class="action-item" onclick="window.location.href='uploaded_videos.php';">
                    <div class="action-icon">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <div class="action-text">Videos</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="dashboard-container">
                <!-- Overview Panel -->
                <div class="panel">
                    <h2>Overview</h2>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Admin
                        </div>
                        <div class="stat-value"><?php echo $admin; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            SISO/STEM-Coordinator
                        </div>
                        <div class="stat-value"><?php echo $siso; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Headteachers
                        </div>
                        <div class="stat-value"><?php echo $headteacher; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Teachers
                        </div>
                        <div class="stat-value"><?php echo $teacher; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Assigned courses
                        </div>
                        <div class="stat-value">45</div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Total users
                        </div>
                        <div class="stat-value"><?php echo $total; ?></div>
                    </div>
                </div>
                
                <!-- Pie Chart Panel -->
                <div class="panel">
                    <h2>User Distribution</h2>
                    <div class="chart-container">
                        <canvas id="userChart"></canvas>
                    </div>
                    <div class="role-legend">
                        <div class="legend-item">
                            <div class="color-box" style="background-color: #4F46E5;"></div>
                            <span>Admin (<?php
                                echo $admin_percent;
                            ?>%)</span>
                        </div>
                        <div class="legend-item">
                            <div class="color-box" style="background-color: #F59E0B;"></div>
                            <span>SISO/STEM-Coordinator (<?php
                                echo $siso_percent;
                            ?>%)</span>
                        </div>
                        <div class="legend-item">
                            <div class="color-box" style="background-color: #10B981;"></div>
                            <span>Headteacher (<?php
                                echo $headteacher_percent;
                            ?>%)</span>
                        </div>
                        <div class="legend-item">
                            <div class="color-box" style="background-color: #EF4444;"></div>
                            <span>Teacher (<?php
                                echo $teacher_percent;
                            ?>%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <?php
            // Connect to DB and count roles
            $stmt = $db->query("
                SELECT 
                    COUNT(*) AS total,
                    SUM(role = 'siso') AS siso,
                    SUM(role = 'teacher') AS teacher,
                    SUM(role = 'headteacher') AS headteacher,
                    SUM(role = 'admin') AS admin
                FROM users
            ");

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Map roles to JavaScript-friendly format
            $userData = [
                [ "role" => "Admin", "count" => (int)$row['admin'], "color" => "#4F46E5" ],
                [ "role" => "SISO", "count" => (int)$row['siso'], "color" => "#F59E0B" ],
                [ "role" => "Headteacher", "count" => (int)$row['headteacher'], "color" => "#10B981" ],
                [ "role" => "Teacher", "count" => (int)$row['teacher'], "color" => "#EF4444" ]
            ];

            // Output JSON into JavaScript variable
            echo "<script>const userData = " . json_encode($userData) . ";</script>";
        ?>

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