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

    // Get the logged-in user's email from session
    $email = $_SESSION['email'];

    // Step 1: Get the region of the logged-in user
    $stmt1 = $db->prepare("SELECT region FROM users WHERE email = :email");
    $stmt1->execute([':email' => $email]);
    $user = $stmt1->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    $region = $user['region'];

    // Step 2: Fetch all users in the same region
    $stmt2 = $db->prepare("SELECT * FROM user_logins WHERE region = :region");
    $stmt2->execute([':region' => $region]);
    $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // At this point, $rows contains all users in the same region as the logged-in user

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
/* Welcome section */
.welcome-section {
    color: white;
    padding: 2rem 0;
    text-align: center;
    margin-bottom: 2rem;
}

.welcome-title {
    font-size: 2.5rem;
    font-weight: 600;
    letter-spacing: -0.5px;
}

/* Content section */
.content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Filter section */
label {
    display: inline-block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #34495e;
    font-size: 1rem;
}

#date-dropdown {
    background-color: white;
    border: 2px solid #bdc3c7;
    border-radius: 6px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    color: #2c3e50;
    min-width: 200px;
    margin-bottom: 2rem;
    transition: border-color 0.3s ease;
}

#date-dropdown:focus {
    outline: none;
    border-color: #3498db;
}

#date-dropdown:hover {
    border-color: #7f8c8d;
}

/* Table styles */
table {
    width: 100%;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

thead {
    background-color: #34495e;
}

thead th {
    color: white;
    font-weight: 600;
    padding: 1.25rem 1rem;
    text-align: left;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

tbody tr {
    border-bottom: 1px solid #ecf0f1;
    transition: background-color 0.2s ease;
}

tbody tr:hover {
    background-color: #f8f9fa;
}

tbody tr:last-child {
    border-bottom: none;
}

tbody td {
    padding: 1rem;
    color: #2c3e50;
    font-size: 0.95rem;
}

tbody td:first-child {
    font-weight: 500;
    color: #2980b9;
}

/* Pagination controls */
.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.pagination-controls button {
    background-color: white;
    border: 2px solid #bdc3c7;
    color: #2c3e50;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 40px;
}

.pagination-controls button:hover {
    background-color: #ecf0f1;
    border-color: #95a5a6;
}

.pagination-controls button.active {
    background-color: #3498db;
    border-color: #3498db;
    color: white;
}

.pagination-controls button:disabled {
    background-color: #ecf0f1;
    border-color: #d5dbdb;
    color: #95a5a6;
    cursor: not-allowed;
}

.pagination-controls span {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin: 0 0.5rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .content {
        padding: 0 1rem;
    }
    
    .welcome-title {
        font-size: 2rem;
    }
    
    table {
        font-size: 0.85rem;
    }
    
    thead th,
    tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    .pagination-controls {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .pagination-controls button {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
}

/* Loading state */
.loading {
    text-align: center;
    padding: 2rem;
    color: #7f8c8d;
    font-style: italic;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #7f8c8d;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    color: #95a5a6;
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