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
    <title>Change Password</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
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
            grid-template-columns: 1fr;
            gap: 15px;
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
        
        .required::after {
            content: '*';
            color: #e74c3c;
            margin-left: 3px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
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
        
        .password-requirements {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }
        
        .password-requirements ul {
            padding-left: 20px;
            margin-top: 5px;
        }
        
        .password-requirements li {
            margin-bottom: 3px;
        }
        
        .requirement-met {
            color: #2ecc71;
        }
        
        .requirement-unmet {
            color: #e74c3c;
        }
        
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search courses, students, or content...">
                <i class="fas fa-search search-icon"></i>
            </div>
            
            <div class="header-actions">
                <button class="notification-btn" onclick="window.location.href='editpass.php';" title="Edit Password">
                    <i class="fa-solid fa-pencil"></i>
                </button>

                
                <div class="user-profile">
                    
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
                <div class="header">
                    <h1>Edit Password</h1>
                </div>
                
                <div id="alertBox" class="alert"></div>
                
                <form id="editPasswordForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="currentPassword" class="required">Current Password</label>
                            <div class="password-container">
                                <input type="password" id="currentPassword" name="currentPassword" required>
                                <button type="button" class="password-toggle" aria-label="Show password" data-target="currentPassword">
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
                            <label for="newPassword" class="required">New Password</label>
                            <div class="password-container">
                                <input type="password" id="newPassword" name="newPassword" required>
                                <button type="button" class="password-toggle" aria-label="Show password" data-target="newPassword">
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
                            <div class="password-requirements">
                                <span>Password must contain:</span>
                                <ul id="passwordRequirements">
                                    <li id="req-length" class="requirement-unmet">At least 8 characters</li>
                                    <li id="req-uppercase" class="requirement-unmet">At least one uppercase letter</li>
                                    <li id="req-lowercase" class="requirement-unmet">At least one lowercase letter</li>
                                    <li id="req-number" class="requirement-unmet">At least one number</li>
                                    <li id="req-special" class="requirement-unmet">At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmNewPassword" class="required">Confirm New Password</label>
                            <div class="password-container">
                                <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                                <button type="button" class="password-toggle" aria-label="Show password" data-target="confirmNewPassword">
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
                            <div id="passwordMatch" class="password-requirements"></div>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Update Password</button>
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
                const form = document.getElementById('editPasswordForm');
                const cancelBtn = document.getElementById('cancelBtn');
                const alertBox = document.getElementById('alertBox');
                const newPassword = document.getElementById('newPassword');
                const confirmNewPassword = document.getElementById('confirmNewPassword');
                const passwordMatch = document.getElementById('passwordMatch');
                
                // Password toggle functionality
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
                
                // Password validation
                function validatePassword(password) {
                    const requirements = {
                        length: password.length >= 8,
                        uppercase: /[A-Z]/.test(password),
                        lowercase: /[a-z]/.test(password),
                        number: /[0-9]/.test(password),
                        special: /[^A-Za-z0-9]/.test(password)
                    };
                    
                    document.getElementById('req-length').className = requirements.length ? 'requirement-met' : 'requirement-unmet';
                    document.getElementById('req-uppercase').className = requirements.uppercase ? 'requirement-met' : 'requirement-unmet';
                    document.getElementById('req-lowercase').className = requirements.lowercase ? 'requirement-met' : 'requirement-unmet';
                    document.getElementById('req-number').className = requirements.number ? 'requirement-met' : 'requirement-unmet';
                    document.getElementById('req-special').className = requirements.special ? 'requirement-met' : 'requirement-unmet';
                    
                    return Object.values(requirements).every(value => value === true);
                }
                
                // Check password match
                function checkPasswordMatch() {
                    if (confirmNewPassword.value === '') {
                        passwordMatch.textContent = '';
                        return false;
                    } else if (newPassword.value === confirmNewPassword.value) {
                        passwordMatch.textContent = 'Passwords match!';
                        passwordMatch.className = 'password-requirements requirement-met';
                        return true;
                    } else {
                        passwordMatch.textContent = 'Passwords do not match!';
                        passwordMatch.className = 'password-requirements requirement-unmet';
                        return false;
                    }
                }
                
                // Show alert
                function showAlert(message, type) {
                    alertBox.textContent = message;
                    alertBox.className = `alert alert-${type}`;
                    alertBox.style.display = 'block';
                    
                    setTimeout(() => {
                        alertBox.style.display = 'none';
                    }, 5000);
                }
                
                // Event listeners for password validation
                newPassword.addEventListener('input', () => {
                    validatePassword(newPassword.value);
                    if (confirmNewPassword.value !== '') {
                        checkPasswordMatch();
                    }
                });
                
                confirmNewPassword.addEventListener('input', checkPasswordMatch);
                
                document.getElementById('editPasswordForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const form = e.target;
                    const formData = new FormData(form);
                    const saveBtn = document.getElementById('saveBtn');
                    
                    // Disable button during submission
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Processing...';
                    
                    try {
                        const response = await fetch('editpage.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert(result.message);
                            window.location.href = 'profile.php'; // Redirect after success
                        } else {
                            alert(result.message);
                            if (result.field) {
                                document.getElementById(result.field).focus();
                            }
                        }
                    } catch (error) {
                        alert('An error occurred. Please try again.');
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Update Password';
                    }
                });
                
                
                cancelBtn.addEventListener('click', () => {
                    if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
                        form.reset();
                        // Reset password requirement indicators
                        document.querySelectorAll('#passwordRequirements li').forEach(li => {
                            li.className = 'requirement-unmet';
                        });
                        passwordMatch.textContent = '';
                        // Normally would redirect: window.location.href = 'profile.html';
                    }
                });
            });
        </script>
    </div>
</body>
</html>