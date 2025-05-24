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
    $stmt = $db->prepare("SELECT * FROM users"); // Replace 'employees' with your table name
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
    <title>Add a New User</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>     
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #444;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0e62aa;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-full {
            grid-column: span 2;
        }
        
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #0e62aa;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0a4f8a;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background-color: #e5e5e5;
        }
        
        .checkbox-group {
            margin-top: 10px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            cursor: pointer;
        }
        
        .checkbox-label input {
            width: auto;
            margin-right: 10px;
        }
        
        .required::after {
            content: '*';
            color: #e74c3c;
            margin-left: 3px;
        }
        
        .headers {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Password field with eye icon styles */
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            background: none;
            border: none;
        }
        
        .password-toggle:hover {
            color: #333;
        }
        
        .password-toggle svg {
            width: 18px;
            height: 18px;
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
                <div class="headers">
                    <h1>Add New User</h1>
                </div>
        
                <form id="addUserForm" action="registerpage.php" method="post">
                    <div class="form-grid">
                        <div class="form-group form-full">
                            <label for="name" class="required">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="required">Phone</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="role" class="required">User Type</label>
                            <select id="role" name="role" required>
                                <option value="">Select User Type</option>
                                <option value="Admin">Admin</option>
                                <option value="SISO">SISO/STEM</option>
                                <option value="Headteacher">Headteacher</option>
                                <option value="Teacher">Teacher</option>
                                <option value="District Director">District Director</option>
                                <option value="Regional Director">Regional Director</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Login Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_mail" class="required">Contact Email</label>
                            <input type="email" id="contact_mail" name="contact_mail" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="organization">Organization/School</label>
                            <input type="text" id="organization" name="organization">
                        </div>

                        <div class="form-group">
                            <label for="district">District</label>
                            <input type="text" id="district" name="district">
                        </div>
                        
                        <div class="form-group">
                            <label for="town">Town</label>
                            <input type="text" id="town" name="town">
                        </div>
                        
                        <div class="form-group">
                            <label for="region">Region</label>
                            <input type="text" id="region" name="region">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="required">Password</label>
                            <div class="password-container">
                                <input type="password" id="password" name="password" required>
                                <button type="button" class="password-toggle" aria-label="Show password" data-target="password">
                                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword" class="required">Confirm Password</label>
                            <div class="password-container">
                                <input type="password" id="confirmPassword" name="confirmPassword" required>
                                <button type="button" class="password-toggle" aria-label="Show password" data-target="confirmPassword">
                                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </section>
        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('addUserForm');
                const cancelBtn = document.getElementById('cancelBtn');
                
                // Add event listeners for password toggle buttons
                const passwordToggles = document.querySelectorAll('.password-toggle');
                passwordToggles.forEach(toggle => {
                    toggle.addEventListener('click', () => {
                        const targetId = toggle.getAttribute('data-target');
                        const passwordInput = document.getElementById(targetId);
                        const eyeIcon = toggle.querySelector('.eye-icon');
                        const eyeOffIcon = toggle.querySelector('.eye-off-icon');
                        
                        // Toggle password visibility
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            eyeIcon.style.display = 'none';
                            eyeOffIcon.style.display = 'block';
                            toggle.setAttribute('aria-label', 'Hide password');
                        } else {
                            passwordInput.type = 'password';
                            eyeIcon.style.display = 'block';
                            eyeOffIcon.style.display = 'none';
                            toggle.setAttribute('aria-label', 'Show password');
                        }
                    });
                });
                
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    try {
                        const response = await fetch('registerpage.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        
                        if (result.success) {
                            alert(result.message);
                            window.location.href = result.redirect;
                        } else {
                            alert(result.error);
                            if (result.field) {
                                document.getElementById(result.field).focus();
                            }
                        }
                    } catch (error) {
                        alert('An error occurred during registration');
                    }
                });

                cancelBtn.addEventListener('click', () => {
                    // Redirect back to users list
                    // window.location.href = 'users.html';
                    
                    // For demo purposes:
                    if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
                        form.reset();
                        // window.location.href = 'users.html';
                    }
                });
            });
        </script>
    </div>
</body>
</html>