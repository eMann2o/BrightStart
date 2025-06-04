<?php
include_once '../database.php'; // DB config
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit();
}

// Validate GET email
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid student email.']);
    exit();
}

$student_email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
if (!$student_email) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
    exit();
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $student_email, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'error' => 'User not found.']);
        exit();
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $e->getMessage()]);
    exit();
}

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $contact_mail = filter_var($_POST['contact_mail'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    $district = htmlspecialchars($_POST['district']);
    $town = htmlspecialchars($_POST['town']);
    $organization = htmlspecialchars($_POST['organization']);
    $role = htmlspecialchars($_POST['role']);
    $region = htmlspecialchars($_POST['region']);

    if (!$email || !$contact_mail) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit();
    }

    if ($email !== $student_email) {
    $check = $db->prepare("SELECT id FROM users WHERE email = :email");
    $check->bindParam(':email', $email);
    $check->execute();
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Email is already in use by another user.']);
        exit();
    }
}

    try {
        $stmt = $db->prepare("UPDATE users 
            SET name = :name, phone = :phone, email = :email, contact_mail = :contact_mail,
                district = :district, town = :town, organization = :organization, 
                role = :role, region = :region 
            WHERE email = :original_email");

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_mail', $contact_mail);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':town', $town);
        $stmt->bindParam(':organization', $organization);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':region', $region);
        $stmt->bindParam(':original_email', $student_email);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'redirect' => 'users.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error updating profile.']);
        }
        exit();
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a New User</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1>Edit Profile</h1>
                </div>
        
                <form id="editUserForm" method="post">
                    <div class="form-grid">
                        <div class="form-group form-full">
                            <label>Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>User Type</label>
                            <select name="role" required>
                                <option value="">Select Role</option>
                                <?php
                                $roles = ['Admin', 'SISO', 'Headteacher', 'Teacher', 'District Director', 'Regional Director'];
                                foreach ($roles as $r) {
                                    $selected = $student['role'] === $r ? 'selected' : '';
                                    echo "<option value=\"$r\" $selected>$r</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Login Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" name="contact_mail" value="<?php echo htmlspecialchars($student['contact_mail']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Organization/School</label>
                            <input type="text" name="organization" value="<?php echo htmlspecialchars($student['organization']); ?>">
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <input type="text" name="district" value="<?php echo htmlspecialchars($student['district']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Town</label>
                            <input type="text" name="town" value="<?php echo htmlspecialchars($student['town']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Region</label>
                            <input type="text" name="region" value="<?php echo htmlspecialchars($student['region']); ?>">
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary" style="background-color: green;">Update Profile</button>
                        <a href="users.php"><button type="button" class="btn btn-primary">Cancel</button></a>
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
                const form = document.getElementById('editUserForm');
                const cancelBtn = document.getElementById('cancelBtn');

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const formData = new FormData(form);

                    try {
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        });

                        const text = await response.text();

                        try {
                            const result = JSON.parse(text);

                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: result.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = result.redirect || 'users.php';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.error || 'An error occurred.'
                                });
                            }

                        } catch (jsonError) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Unexpected Response',
                                text: text
                            });
                        }

                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: error.message
                        });
                    }
                });

                cancelBtn.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Unsaved changes will be lost.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, cancel it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.reset();
                            Swal.fire('Cancelled', 'Form has been reset.', 'success');
                        }
                    });
                });
            });
        </script>
    </div>
</body>
</html>