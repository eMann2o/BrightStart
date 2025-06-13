<?php
session_start();
include_once '../database.php';
require 'db.php';

// Connect to DB
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Ensure email is passed
if (!isset($_GET['email'])) {
    die("Email not specified.");
}
$email = $_GET['email'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Profile Picture</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        .content {
            background: #f5f5f5;
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .containers{            
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .container h2 {
            color: #333;
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .current-picture {
            text-align: center;
            margin-bottom: 30px;
        }

        .current-picture h5 {
            color: #555;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .profile-image {
            border: 4px solid #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .upload-form {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .upload-form:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .btn-primary {
            background: #007bff;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        /* Responsive design */
        @media (max-width: 1000px) {
            .container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .container h2 {
                font-size: 1.5rem;
            }
            
            .upload-form {
                padding: 20px;
            }
        }

        /* File input styling */
        .form-control[type="file"] {
            padding: 10px;
            background: white;
        }

        .form-control[type="file"]::-webkit-file-upload-button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin-right: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-control[type="file"]::-webkit-file-upload-button:hover {
            background: #495057;
        }
    </style>
    
</head>
<body class="container mt-5">

    

</body>
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
                <span>Upload Video</span>
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
            <div class="containers">
                <h2 class="mb-4">Upload Profile Picture</h2>

                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    <div class="mb-3">
                        <label class="form-label">Select New Picture:</label>
                        <input class="form-control" type="file" name="profile_pic" id="profile_pic" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <img id="preview" src="" alt="Preview" style="display:none; width:150px; height:150px; border-radius:50%; object-fit:cover; border:1px dashed #888;">
                    </div>

                    <button class="btn btn-primary" type="submit">Upload</button>
                </form>

            </div>
        </section>
        

        <script>
            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            // Show live preview
            document.getElementById('profile_pic').addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        const preview = document.getElementById('preview');
                        preview.src = evt.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle AJAX form submission
            document.getElementById('uploadForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('upload_profile_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Uploaded!' : 'Failed',
                        text: data.message,
                        confirmButtonColor: '#00bdff'
                    }).then((result) => {
                        if (data.success && result.isConfirmed) {
                            // Optional UI updates
                            document.getElementById('currentPic').src = "view_profile.php?email=<?php echo urlencode($email); ?>&t=" + new Date().getTime();
                            document.getElementById('preview').style.display = 'none';
                            document.getElementById('uploadForm').reset();

                            // ✅ Redirect immediately after OK is clicked
                            window.location.href = "profile.php";
                        }
                    });
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: 'Something went wrong during the upload.'
                    });
                });
            });

        </script>
    </div>
</body>
</html>
