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
    <title>Uploaded Pictures</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <style>

        .cords {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid #e8eaed;
        }

        .album {
            margin-bottom: 32px;
            padding: 24px;
            background: #f8f9fb;
            border-radius: 12px;
            border: 1px solid #e1e5e9;
        }

        .album:last-child {
            margin-bottom: 0;
        }

        .rename-album {
            width: fit-content;
            margin-right: 20px;
            padding: 12px 16px;
            font-size: 18px;
            font-weight: 600;
            color: #202124;
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 8px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .rename-album:focus {
            border-color: #5f6fff;
        }

        .photo {
            display: inline-block;
            margin: 8px;
            padding: 16px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e8eaed;
            vertical-align: top;
            max-width: 280px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .photo:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .photo img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
            background-color: #f5f6fa;
        }

        .rename-photo {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 500;
            color: #3c4043;
            background: #f8f9fb;
            border: 1px solid #dadce0;
            border-radius: 6px;
            margin-bottom: 12px;
            outline: none;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }

        .rename-photo:focus {
            border-color: #5f6fff;
            background: #ffffff;
        }

        .delete-photo {
            width: 100%;
            padding: 8px 16px;
            background: #ea4335;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .delete-photo:hover {
            background: #d33b2c;
        }

        .delete-photo:active {
            background: #b52d20;
        }

        /* Template label styling to match reference */
        .template-label {
            color: #5f6fff;
            font-size: 12px;
            font-weight: 500;
            margin-top: 8px;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .cords {
                padding: 24px;
                border-radius: 12px;
            }

            .album {
                padding: 20px;
                margin-bottom: 24px;
            }

            .rename-album {
                font-size: 16px;
                padding: 10px 14px;
            }

            .photo {
                margin: 6px;
                max-width: calc(50% - 12px);
                min-width: 200px;
            }

            .photo img {
                height: 160px;
            }
        }

        @media (max-width: 480px) {
            .photo {
                max-width: 100%;
                margin: 6px 0;
            }

            .photo img {
                height: 180px;
            }
        }

        /* Clean focus states */
        .rename-album:focus,
        .rename-photo:focus {
            box-shadow: 0 0 0 2px rgba(95, 111, 255, 0.2);
        }

        /* Subtle hover states for albums */
        .album:hover {
            background: #f3f4f6;
        }

        .update-photo {
            padding: 8px 12px;
            background: #5f6fff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            margin-right: 8px;
            margin-bottom: 8px;
            transition: background-color 0.2s ease;
        }

        .update-photo:hover {
            background: #4c5dff;
        }

        .update-photo:active {
            background: #3b4aff;
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
            <h1 class="welcome-title"><svg class="svg-inline--fa fa-image" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="image" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h96 32H424c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"></path></svg> Uploaded Images</h1>
        </div>
        
        <div class="cords" id="gallery">
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

        $(document).ready(function() {
            fetchGallery();

            function fetchGallery() {
                $.get("fetch.php", function(data) {
                $('#gallery').html(data);
                });
            }

            // Create a reusable toast instance
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });

            // Update Album name
            $(document).on("click", ".update-album", function() {
                let id = $(this).data("id");
                let name = $("#album-name-" + id).val();

                $.post("update.php", { type: 'album', id, name }, function(resp) {
                Toast.fire({
                    icon: 'success',
                    title: 'Album name updated'
                });
                fetchGallery();
                }).fail(function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to update album name'
                });
                });
            });

            // Update Photo title
            $(document).on("click", ".update-photo", function() {
                let id = $(this).data("id");
                let title = $("#photo-title-" + id).val();

                $.post("update.php", { type: 'photo', id, title }, function(resp) {
                Toast.fire({
                    icon: 'success',
                    title: 'Photo title updated'
                });
                fetchGallery();
                }).fail(function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to update photo title'
                });
                });
            });

            // Delete photo with SweetAlert confirm (full modal)
            $(document).on("click", ".delete-photo", function() {
                let id = $(this).data("id");

                Swal.fire({
                title: "Are you sure?",
                text: "This photo will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                if (result.isConfirmed) {
                    $.post("delete.php", { id }, function(resp) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'The photo has been removed.'
                    });
                    fetchGallery();
                    }).fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete the photo.'
                    });
                    });
                }
                });
            });
            });

    </script>
</body>
</html>