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
    <title>Courses Overview</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px; /* Add padding to container for smaller screens */
}

.profile-card {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 0;
    position: relative;
    margin: 20px auto; /* Add margin for spacing */
}

.headers {
    background: linear-gradient(135deg, #3498db, #9b59b6);
    height: 120px; /* Reduced height to eliminate space */
    position: relative;
}

.profile-image {
    position: absolute;
    top: 20px; /* Position image to overlap header more */
    left: 50%;
    transform: translateX(-50%);
    width: 200px;
    height: 200px;
    border-radius: 50%;
    border: 5px solid white;
    background-color: #ddd;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10; /* Ensure image stays on top */
}

.profile-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content {
    padding-top: 110px; /* Adjusted to reduce space above content */
    padding-bottom: 40px;
    padding-left: 20px;
    padding-right: 20px;
    text-align: center;
    position: relative;
    z-index: 5;
}

.name {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.title {
    font-size: 16px;
    color: #888;
    margin-bottom: 20px;
}

.stats {
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Allow stats to wrap on smaller screens */
    gap: 20px;
    margin-bottom: 30px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 80px; /* Ensure minimum width */
}

.stat-value {
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.stat-label {
    font-size: 14px;
    color: #888;
}

.bio {
    font-size: 16px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 30px;
    text-align: center; /* Center on mobile for better readability */
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.progress-section {
    margin-bottom: 25px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.progress-title {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.progress-label {
    font-size: 15px;
    font-weight: 500;
    color: #333;
}

.progress-value {
    font-size: 15px;
    font-weight: 600;
    color: #3498db;
}

.progress-bar-container {
    width: 100%;
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(to right, #3498db, #9b59b6);
    border-radius: 5px;
    transition: width 0.5s ease;
}

.badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
}

.badge {
    display: flex;
    align-items: center;
    background-color: #f0f2f5;
    border-radius: 20px;
    padding: 6px 12px;
    margin-bottom: 5px;
}

.badge-icon {
    width: 18px;
    height: 18px;
    background-color: #3498db;
    border-radius: 50%;
    margin-right: 8px;
}

.badge-text {
    font-size: 13px;
    color: #555;
}

.contact-btn {
    display: inline-block;
    background: linear-gradient(135deg, #3498db, #9b59b6);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 25px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.contact-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.info-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    gap: 15px;
    margin-bottom: 20px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.info-item {
    flex: 1;
    min-width: 200px;
    margin-bottom: 15px;
}

.info-label {
    font-size: 14px;
    color: #888;
    margin-bottom: 5px;
    text-align: left;
}

.info-value {
    font-size: 15px;
    color: #333;
    text-align: left;
}

/* Improved Media Queries */
@media (min-width: 992px) {
    /* Large screens */
    .profile-image {
        width: 250px;
        height: 250px;
        top: 20px;
    }
    
    .headers {
        height: 140px;
    }
    
    .content {
        padding-top: 140px;
        padding-left: 50px;
        padding-right: 50px;
    }
    
    .name {
        font-size: 28px;
    }
    
    .title {
        font-size: 18px;
    }
    
    .bio {
        text-align: justify;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    /* Medium screens */
    .profile-image {
        width: 220px;
        height: 220px;
        top: 20px;
    }
    
    .headers {
        height: 130px;
    }
    
    .content {
        padding-top: 120px;
        padding-left: 40px;
        padding-right: 40px;
    }
}

@media (max-width: 767px) {
    /* Small screens */
    .profile-image {
        width: 180px;
        height: 180px;
        top: 20px;
    }
    
    .headers {
        height: 110px;
    }
    
    .content {
        padding-top: 100px;
        padding-left: 25px;
        padding-right: 25px;
    }
    
    .stats {
        gap: 15px;
    }
    
    .info-row {
        flex-direction: column;
    }
    
    .info-item {
        min-width: 100%;
        margin-bottom: 10px;
    }
}

@media (max-width: 480px) {
    /* Extra small screens */
    .profile-image {
        width: 140px;
        height: 140px;
        top: 20px;
    }
    
    .headers {
        height: 90px;
    }
    
    .content {
        padding-top: 80px;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .name {
        font-size: 20px;
    }
    
    .title {
        font-size: 14px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .stat-label {
        font-size: 12px;
    }
    
    .badge {
        padding: 5px 10px;
    }
    
    .contact-btn {
        padding: 8px 20px;
        font-size: 14px;
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
                <div class="profile-card">
                    <div class="headers"></div>
                    <div class="profile-image">
                        <img src="emoji.png" alt="Teacher Profile Picture">
                    </div>
                    <div class="content">
                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-label">Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-label">Role</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['role']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-label">District</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['district']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Organization</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['organization']); ?></div>
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