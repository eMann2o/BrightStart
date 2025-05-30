<?php
include_once '../database.php'; // Include database connection file

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.html");
    exit();
}

try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get email from session
    $email = $_SESSION['email'];

    // Step 1: Retrieve the district of the logged-in user
    $stmt = $db->prepare("SELECT district FROM user_logins WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    $district = $user['district'];

    // Step 2: Retrieve all users in the same district
    $stmt = $db->prepare("SELECT * FROM user_logins WHERE district = :district");
    $stmt->execute([':district' => $district]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // You can now work with $rows to display data
    // Example: print_r($rows);
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
    <title>Participants</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        
        .content {
        padding: 30px;
        background-color: #f5f7fa;
        }

        .content h1 {
        margin-bottom: 20px;
        font-size: 28px;
        color: #333;
        }

        .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .toolbar input[type="text"] {
        padding: 10px;
        width: 300px;
        border: 1px solid #ccc;
        border-radius: 4px;
        }

        .add-user-btn {
        background-color: #3a7bd5;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
        }

        .add-user-btn:hover {
        background-color: #2c5fb3;
        }

        table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        }

        th,
        td {
        padding: 14px;
        border: 1px solid #e0e0e0;
        text-align: left;
        }

        th {
        background-color: #f0f0f0;
        font-weight: 600;
        color: #333;
        }

        td {
        color: #555;
        }

        tr:hover {
        background-color: #f9f9f9;
        }

        .user-role {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        }

        .role-admin {
        background-color: #e6f7ee;
        color: #00b894;
        }

        .role-instructor {
        background-color: #e6f7ff;
        color: #3a7bd5;
        }

        .role-learner {
        background-color: #f0f0f0;
        color: #555;
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
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
            </div>
            
            <div class="menu-item" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
            </div>
            <div class="menu-item active" onclick="window.location.href='users.php';">
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
        
        <div class="welcome-section">
            <h1 class="welcome-title">
                
                 Participants Overview
            </h1>
            
            
        </div>
        
        <section class="content">
            <div class="toolbar">
                <input type="text" placeholder="Search users..." id="searchInput" onkeyup="searchTable()">
                <button class="add-user-btn" onclick="downloadTableAsExcel()"><i class="fa-solid fa-cloud-arrow-down"></i></button>
                
            </div>

            <table id="myTable">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>District</th>
                    <th>Town</th>
                    <th>Organization</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                        if ($rows) {
                            foreach ($rows as $row) {
                                $email = urlencode($row['email']); // Encode email for URL safety
                                echo "<tr style='cursor: pointer;' onclick=\"window.location.href='userprofile.php?email={$email}'\">";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['district']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['town']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['organization']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No data found</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </section>
        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            function searchTable() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("searchInput");
                filter = input.value.toLowerCase();
                table = document.getElementById("myTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {  // Starts from 1 to skip the header row
                    td = tr[i].getElementsByTagName("td");
                    let rowFound = false;
                    
                    for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        rowFound = true;
                        }
                    }
                    }
                    if (rowFound) {
                    tr[i].style.display = "";
                    } else {
                    tr[i].style.display = "none";
                    }
                }
            }

            function downloadTableAsExcel() {
            // Get the table element
            const table = document.getElementById("myTable");

            // Convert the table to a data string
            let tableHTML = table.outerHTML.replace(/ /g, "%20");

            // File properties
            const filename = "Participants list";
            const dataType = "application/vnd.ms-excel";

            // Create a download link
            const link = document.createElement("a");
            link.href = `data:${dataType}, ${tableHTML}`;
            link.download = filename;

            // Trigger the download
            link.click();
        }

        </script>
    </div>
</body>
</html>