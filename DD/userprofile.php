<?php
include_once '../database.php';
require 'db.php';
session_start();

// Redirect if user not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../index.html");
    exit();
}

// Check if an email is passed in the query string
if (!isset($_GET['email'])) {
    echo "No user specified.";
    exit();
}

$email = $_GET['email'];

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the user by email
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    $user_id = $user['id'];

    // Fetch all courses
    $stmt = $db->query("SELECT * FROM courses");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}


$progress_data = [];

// Fetch all courses
$stmt = $db->query("SELECT * FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as $course) {
    $course_id = $course['id'];
    $course_title = htmlspecialchars($course['title']);

    // Total lessons in course
    $stmt1 = $db->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $stmt1->execute([$course_id]);
    $total_lessons = $stmt1->fetchColumn();

    // Completed lessons by user
    $stmt2 = $db->prepare("
        SELECT COUNT(*) 
        FROM progress 
        JOIN lessons ON progress.lesson_id = lessons.id 
        WHERE progress.user_id = ? 
          AND lessons.course_id = ? 
          AND progress.status = 'completed'
    ");
    $stmt2->execute([$user_id, $course_id]);
    $completed_lessons = $stmt2->fetchColumn();

    $progress_percent = ($total_lessons > 0) ? round(($completed_lessons / $total_lessons) * 100) : 0;

    $progress_data[] = [
        'course' => $course_title,
        'progress' => $progress_percent
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_student"])) {
    $email_to_delete = $_POST['student_email'] ?? '';

    if (!empty($email_to_delete)) {
        // Get user ID by email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email_to_delete]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['id'];

            // Start transaction
            $db->beginTransaction();

            try {
                // Delete related entries in user_logins (add other related tables here)
                $stmt = $db->prepare("DELETE FROM user_logins WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stmt = $db->prepare("DELETE FROM courses WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stmt = $db->prepare("DELETE FROM progress WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stmt = $db->prepare("DELETE FROM enrollments WHERE user_id = ?");
                $stmt->execute([$user_id]);

                // TODO: Add deletes for other referencing tables here

                // Delete user
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);

                $db->commit();

                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Student account deleted successfully.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'users.php';
                    });
                </script>";
                exit();

            } catch (PDOException $e) {
                $db->rollBack();
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Delete failed: " . addslashes($e->getMessage()) . "',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                </script>";
            }
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'User not found.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?></title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>        
        /* Profile Card Styles */
        .profile-card {
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Header Section */
        .profile-header {
            background: linear-gradient(to right, #3b82f6, #4f46e5);
            color: white;
            padding: 1.5rem;
            position: relative;
            display: flex;
            justify-content: space-between;
        }
        
        .profile-image-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: white;
            padding: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .canvas-container {
            width: 100px;
            height: 100px;
        }
        
        /* Stats Section */
        .stats-container {
            display: flex;
            justify-content: space-around;
            padding: 1rem;
            background-color: #eff6ff;
            border-bottom: 1px solid #dbeafe;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .progress-chart {
            position: relative;
            width: 60px;
            height: 60px;
        }
        
        /* Details Section */
        .details-container {
            padding: 1.5rem;
        }
        
        /* Responsive Grid for Details */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .detail-item:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background-color: #eff6ff;
            border-radius: 8px;
            margin-right: 0.75rem;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #3b82f6;
        }
        
        .detail-content {
            flex-grow: 1;
        }
        
        .detail-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.125rem;
        }
        
        .detail-value {
            font-size: 0.875rem;
            color: #1f2937;
            word-break: break-word;
        }
        
        /* Footer Section */
        .profile-footer {
            padding: 1rem;
            display: flex;
            justify-content: end;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary {
            background-color: white;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background-color: #f9fafb;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border: 1px solid transparent;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .profile-image {
                width: 80px;
                height: 80px;
            }
            
            .canvas-container {
                width: 80px;
                height: 80px;
            }
            
            .stat-value {
                font-size: 1rem;
            }
            
            .progress-chart {
                width: 50px;
                height: 50px;
            }
        }

        /* Chart Card Styles */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    margin: 1rem 0;
    overflow: hidden;
}

.chart-container {
    padding: 1.5rem;
    background-color: #ffffff;
    position: relative;
    height: 400px; /* Fixed height for consistent display */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Ensure canvas is responsive */
#courseProgressChart {
    width: 100% !important;
    height: 100% !important;
    max-width: 100%;
    max-height: 350px;
}

/* Chart container responsive behavior */
@media (max-width: 768px) {
    .chart-container {
        height: 300px;
        padding: 1rem;
    }
    
    #courseProgressChart {
        max-height: 250px;
    }
}

@media (max-width: 480px) {
    .chart-container {
        height: 250px;
        padding: 0.75rem;
    }
    
    #courseProgressChart {
        max-height: 200px;
    }
}

/* Optional: Add a subtle border to separate chart from other content */
.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 1.5rem;
    right: 1.5rem;
    height: 1px;
    background: linear-gradient(to right, transparent, #e5e7eb, transparent);
}

.chart-container::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 1.5rem;
    right: 1.5rem;
    height: 1px;
    background: linear-gradient(to right, transparent, #e5e7eb, transparent);
}

/* If you want the chart to have a subtle background */
.chart-container {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
}

/* Ensure chart maintains aspect ratio and doesn't overflow */
.chart-container canvas {
    display: block;
    box-sizing: border-box;
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
            <div class="container">
                

                <!-- Footer Section -->
                <div class="profile-footer">
                    <button class="btn btn-secondary" style="color: white; border: 1px solid #00bdff; background-color: #00bdff;" onclick="window.location.href='edit_profile.php?email=<?php echo htmlspecialchars($user['email']); ?>'">Edit Profile</button>
                    <button type="button" class="btn btn-danger delete-user-btn" style="color: #ff0000; border: 1px solid #ff0000; margin-left: 10px; background-color: white;" data-email="<?= htmlspecialchars($user['email']) ?>">
                        Delete User
                    </button>

                </div>
                <div class="profile-card">
                    <!-- Header Section -->
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <div class="profile-image">
                                <img src="emoji.png" alt="Teacher Profile Picture" onerror="this.src='/api/placeholder/400/400'">
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="chart-container">
                            <canvas id="courseProgressChart"></canvas>
                        </div>
                    </div>
                    <!-- Details Section - Responsive Grid -->
                    <div class="details-container">
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Name</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['name']); ?></div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10,17 15,12 10,7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Login Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item" style="cursor: pointer;" onclick="window.location.href = 'mailto:<?php echo htmlspecialchars($user['contact_mail']); ?>';">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Contact Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['contact_mail']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 10c-.83 0-1.5-.67-1.5-1.5v-5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5z"></path>
                                        <path d="M20.5 10H19V8.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"></path>
                                        <path d="M9.5 14c.83 0 1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5S8 21.33 8 20.5v-5c0-.83.67-1.5 1.5-1.5z"></path>
                                        <path d="M3.5 14H5v1.5c0 .83-.67 1.5-1.5 1.5S2 16.33 2 15.5 2.67 14 3.5 14z"></path>
                                        <path d="M14 14.5c0-.83.67-1.5 1.5-1.5h5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-5c-.83 0-1.5-.67-1.5-1.5z"></path>
                                        <path d="M15.5 19H14v1.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"></path>
                                        <path d="M10 9.5C10 8.67 9.33 8 8.5 8h-5C2.67 8 2 8.67 2 9.5S2.67 11 3.5 11h5c.83 0 1.5-.67 1.5-1.5z"></path>
                                        <path d="M8.5 5H10V3.5C10 2.67 9.33 2 8.5 2S7 2.67 7 3.5 7.67 5 8.5 5z"></path>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Role</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['role']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Phone</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Town</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['town']); ?></div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">District</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['district']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Region</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['region']); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Organization</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['organization']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.delete-user-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        const email = button.getAttribute('data-email');

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action will permanently delete the user and their data.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('delete_user.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'student_email=' + encodeURIComponent(email)
                                })
                                .then(response => response.text())
                                .then(text => {
                                    try {
                                        const data = JSON.parse(text);
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Deleted!',
                                                text: data.message,
                                            }).then(() => window.location.href = 'users.php');
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data.message
                                            });
                                        }
                                    } catch (e) {
                                        console.error('Response was not valid JSON:', text);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Server Error',
                                            text: 'Unexpected server response (HTML instead of JSON). Check console for details.'
                                        });
                                    }
                                })
                            }
                        });
                    });
                });
            });

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            const progressData = <?php echo json_encode($progress_data); ?>;

            const labels = progressData.map(item => item.course);
            const percentages = progressData.map(item => item.progress);

            const ctx = document.getElementById('courseProgressChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Progress (%)',
                        data: percentages,
                        backgroundColor: 'rgba(60, 235, 54, 0.63)',
                        borderColor: 'rgb(30, 181, 0)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Completion (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Module'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: context => `${context.parsed.y}% complete`
                            }
                        }
                    }
                }
            });

        </script>
    </div>
</body>
</html>