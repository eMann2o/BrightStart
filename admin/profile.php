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
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $_SESSION['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch the user row (assumes $email is already sanitized and set)
    $email = $_SESSION['email']; // Ensure session is started before this line
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['profile_pic'])) {
        // Encode the BLOB image data as base64
        $base64Image = base64_encode($row['profile_pic']);
        $mimeType = "image/jpeg"; // or detect dynamically if stored with type
        $imageSrc = "data:$mimeType;base64,$base64Image";
    } else {
        // Fallback image
        $imageSrc = "emoji.png"; // Or a base64-encoded placeholder
    }


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
    <title><?php $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User"; echo htmlspecialchars($name);?></title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
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
        
        <section class="content">
            <div class="container">
                <div class="profile-footer">
                    <button class="btn btn-secondary" style="color: white; border: 1px solid #00bdff; background-color: #00bdff; margin-right: 5px;" onclick="window.location.href='upload_profile.php?email=<?php echo htmlspecialchars($user['email']); ?>'">Upload Profile Picture</button>
                </div>
                <div class="profile-card">
                    <!-- Header Section -->
                    <div class="profile-header">
                        <div class="profile-image-container">
                            <?php
                                echo "
                                    <div class='profile-image'>
                                        <img src='$imageSrc' alt='Profile Picture' class='profile-picture' width='150' height='150' style='border-radius: 50%; object-fit: cover;'/>
                                    </div>
                                ";
                            ?>
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
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['email']); ?></div>
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
                                    <div class="detail-label">District</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($user['district']); ?></div>
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
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

        </script>
    </div>
</body>
</html>