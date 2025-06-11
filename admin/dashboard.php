<?php
// Security improvements
ini_set('display_errors', 0); // Don't display errors in production
error_reporting(E_ALL);

include_once '../database.php';

// Start the session at the beginning with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict'
]);

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../index.html");
    exit();
}


// Database connection with improved error handling
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

// Function to get user statistics
function getUserStats($db) {
    try {
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN role = 'siso' THEN 1 ELSE 0 END) AS siso_count,
                SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) AS teacher_count,
                SUM(CASE WHEN role = 'headteacher' THEN 1 ELSE 0 END) AS headteacher_count,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_count,
                SUM(CASE WHEN role = 'District Director' THEN 1 ELSE 0 END) AS dd_count,
                SUM(CASE WHEN role = 'Regional Director' THEN 1 ELSE 0 END) AS rd_count
            FROM users
        ");
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user stats: " . $e->getMessage());
        return false;
    }
}

// Function to get lesson count
function getLessonCount($db) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) AS total_lessons FROM lessons");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total_lessons'];
    } catch (PDOException $e) {
        error_log("Error fetching lesson count: " . $e->getMessage());
        return 0;
    }
}

// Function to get users for display (with proper filtering)
function getDisplayUsers($db) {
    try {
        $stmt = $db->prepare("
            SELECT id, name, email, role, created_at 
            FROM users 
            WHERE role IN ('siso', 'teacher', 'headteacher') 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching users: " . $e->getMessage());
        return [];
    }
}

// Get statistics
$userStats = getUserStats($db);
$totalLessons = getLessonCount($db);
$displayUsers = getDisplayUsers($db);

// Calculate percentages safely
$total = $userStats['total'] ?? 0;
$siso = $userStats['siso_count'] ?? 0;
$teacher = $userStats['teacher_count'] ?? 0;
$headteacher = $userStats['headteacher_count'] ?? 0;
$admin = $userStats['admin_count'] ?? 0;
$dd = $userStats['dd_count'] ?? 0;
$rd = $userStats['rd_count'] ?? 0;

// Calculate percentages
function calculatePercentage($count, $total) {
    return $total > 0 ? round(($count / $total) * 100, 2) : 0;
}

$siso_percent = calculatePercentage($siso, $total);
$teacher_percent = calculatePercentage($teacher, $total);
$headteacher_percent = calculatePercentage($headteacher, $total);
$admin_percent = calculatePercentage($admin, $total);
$dd_percent = calculatePercentage($dd, $total);
$rd_percent = calculatePercentage($rd, $total);

// Prepare user data for chart
$userData = [
    ["role" => "Admin", "count" => $admin, "color" => "#4F46E5"],
    ["role" => "SISO", "count" => $siso, "color" => "#F59E0B"],
    ["role" => "Headteacher", "count" => $headteacher, "color" => "#10B981"],
    ["role" => "Teacher", "count" => $teacher, "color" => "#EF4444"],
    ["role" => "District Director", "count" => $dd, "color" => "#8B5CF6"],
    ["role" => "Regional Director", "count" => $rd, "color" => "#F97316"]
];


// Filter out roles with zero count for cleaner chart
$userData = array_filter($userData, function($item) {
    return $item['count'] > 0;
});

// Crucial fix: Re-index the array to ensure it's always treated as a list in JavaScript
$userData = array_values($userData); 

// Sanitize output function
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
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
            flex-wrap: wrap;
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
        
        .error-message {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
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
                
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name">
                            <?php echo sanitizeOutput($_SESSION['name'] ?? 'Unknown User'); ?>
                        </div>
                        <div class="user-role">
                            <?php echo sanitizeOutput($_SESSION['role'] ?? 'Unknown Role'); ?>
                        </div>
                    </div>
                    <div class="user-avatar" onclick="window.location.href='logout.php';" title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="welcome-section">
            <h1 class="welcome-title">Dashboard Overview</h1>
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
                    <div class="action-text">Create Module</div>
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
        
        <div class="card">
            <div class="dashboard-container">
                <!-- Overview Panel -->
                <div class="panel">
                    <h2>User Overview</h2>
                    
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
                            <i class="fa-solid fa-chalkboard-user"></i>
                            Teachers
                        </div>
                        <div class="stat-value"><?php echo $teacher; ?></div>
                    </div>

                    <div class="stat-row">
                        <div class="stat-label">
                            <i class="fa-solid fa-user-tie"></i>
                            District Directors
                        </div>
                        <div class="stat-value"><?php echo $dd; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-label">
                            <i class="fa-solid fa-user-tie"></i>
                            Regional Directors
                        </div>
                        <div class="stat-value"><?php echo $rd; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Total Lessons
                        </div>
                        <div class="stat-value"><?php echo $totalLessons; ?></div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-label">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Total Users
                        </div>
                        <div class="stat-value"><?php echo $total; ?></div>
                    </div>
                </div>
                
                <!-- Pie Chart Panel -->
                <div class="panel">
                    <h2>User Distribution</h2>
                    <div class="chart-container">
                        <?php if (!empty($userData)): ?>
                            <canvas id="userChart"></canvas>
                        <?php else: ?>
                            <div class="no-data-message">No user data to display.</div>
                        <?php endif; ?>
                    </div>
                    <div class="role-legend">
                        <?php foreach ($userData as $user): ?>
                            <div class="legend-item">
                                <div class="color-box" style="background-color: <?php echo $user['color']; ?>;"></div>
                                <span><?php echo sanitizeOutput($user['role']); ?> 
                                (<?php echo calculatePercentage($user['count'], $total); ?>%)
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass data to JavaScript safely
        const userData = <?php echo json_encode($userData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
        
        let activityChart;

        function fetchChartData(days) {
            const loadingEl = document.querySelector('.chart-container');
            const originalContent = loadingEl.innerHTML;
            
            // Show loading state
            loadingEl.innerHTML = '<div class="loading">Loading chart data...</div>';
            
            fetch(`fetch_logins.php?days=${encodeURIComponent(days)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Restore original content
                loadingEl.innerHTML = originalContent;
                
                if (activityChart) {
                    activityChart.destroy();
                }

                const ctx = document.getElementById('activityChart').getContext('2d');
                activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Unique Logins',
                            data: data.logins || [],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: '#4bc0c0',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                        }, {
                            label: 'Module Views',
                            data: data.completions || [],
                            borderColor: '#00d2ff',
                            backgroundColor: 'rgba(0, 210, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                stepSize: 1
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
                loadingEl.innerHTML = '<div class="error-message">Error loading chart data. Please try again.</div>';
            });
        }

        // Sidebar toggle functionality
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Load default chart data
            fetchChartData(7);
            
            // Calculate total for pie chart
            const total = userData.reduce((sum, item) => sum + item.count, 0);
            
            if (total > 0) {
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
                            borderWidth: 2,
                            hoverOffset: 4
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
            }
        });
    </script>
</body>
</html>