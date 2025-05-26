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
    $stmt = $db->prepare("SELECT * FROM user_logins"); 
    $stmt->execute();

    // Fetch all data from the query
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <div class="menu-item active" onclick="window.location.href='users.php';">
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
        
        <div class="welcome-section">
            <h1 class="welcome-title">
                Logged In Users
            </h1>
            
            
        </div>
        
        <section class="content">

            <div>
                <label for="date-dropdown">Filter by Date:</label>
                <select id="date-dropdown"></select>

                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Login Time</th>
                    </tr>
                    </thead>
                    <tbody id="login-table-body"></tbody>
                </table>
                <div id="pagination" class="pagination-controls"></div>
            </div>

        </section>
        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            let allData = [];
            let currentPage = 1;
            const pageSize = 10;
            let filteredData = [];

            function formatTime(datetime) {
                const d = new Date(datetime);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function renderTablePage(page) {
                currentPage = page;
                const start = (page - 1) * pageSize;
                const paginated = filteredData.slice(start, start + pageSize);

                const table = document.getElementById("login-table-body");
                table.innerHTML = "";

                paginated.forEach(row => {
                table.innerHTML += `
                    <tr>
                    <td>${row.name}</td>
                    <td>${row.email}</td>
                    <td>${formatTime(row.login_date)}</td>
                    </tr>
                `;
                });

                renderPagination(filteredData.length);
            }

            function renderPagination(total) {
                const pagination = document.getElementById("pagination");
                const totalPages = Math.ceil(total / pageSize);
                pagination.innerHTML = "";

                for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement("button");
                btn.textContent = i;
                btn.classList.add(i === currentPage ? "active" : "");
                btn.onclick = () => renderTablePage(i);
                pagination.appendChild(btn);
                }
            }

            function switchDate(date) {
                filteredData = allData.filter(r => r.login_date.startsWith(date));
                renderTablePage(1);
            }

            function renderDateFilters(dates) {
                const dropdown = document.getElementById("date-dropdown");
                dropdown.innerHTML = "";

                dates.forEach(date => {
                    const option = document.createElement("option");
                    option.value = date;
                    option.textContent = date;
                    dropdown.appendChild(option);
                });

                dropdown.onchange = () => {
                    switchDate(dropdown.value);
                };
                }

            fetch("get_logins.php")
                .then(res => res.json())
                .then(data => {
                if (!Array.isArray(data)) {
                    alert("Error: " + (data.error || "Invalid response"));
                    return;
                }

                allData = data.map(row => ({
                    ...row,
                    login_date: new Date(row.login_date).toISOString()
                }));

                const uniqueDates = [...new Set(allData.map(r => r.login_date.slice(0, 10)))];
                renderDateFilters(uniqueDates);
                if (uniqueDates.length) switchDate(uniqueDates[0]);
                })
                .catch(err => console.error("Fetch failed", err));
        </script>
    </div>
</body>
</html>