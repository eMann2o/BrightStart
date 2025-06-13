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
    <title>File Upload</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .upload-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            max-width: 500px;
            width: 100%;
        }

        #spinner {
            display: none;
            margin-top: 10px;
            font-size: 16px;
            color: #333;
        }

        .upload-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 30px;
        }

        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 60px 20px;
            text-align: center;
            background-color: #fafbfc;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            border-color: #9ca3af;
            background-color: #f3f4f6;
        }

        .upload-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 20px;
            opacity: 0.6;
        }

        .upload-text {
            font-size: 16px;
            color: #374151;
            margin-bottom: 8px;
        }

        .upload-subtext {
            font-size: 14px;
            color: #6b7280;
        }

        .button-container {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .upload-button {
            background-color: #6ea3ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
        }

        .upload-button:hover {
            background-color: #0055ff;
        }

        .cancel-button {
            background-color: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cancel-button:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        .file-input {
            display: none;
        }

        .upload-status {
            margin-top: 20px;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            display: none;
        }

        .upload-status.success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .upload-status.error {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .file-info {
            margin-top: 15px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            display: none;
        }

        .file-name {
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .file-size {
            font-size: 14px;
            color: #6b7280;
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
            <div class="menu-item active" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
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
            <div class="upload-container">
                <h1 class="upload-title">Upload File</h1>
                
                <div class="upload-area" id="uploadArea">
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7,10 12,15 17,10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    <div class="upload-text">Drag and drop your file here or click to browse</div>
                    <div class="upload-subtext">Supports all files</div>
                </div>
                <div style="
                    background-color: #f5f5f5;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    max-width: 400px;
                    margin: 20px auto;
                    font-family: Arial, sans-serif;
                ">
                    <div style="margin-bottom: 15px;">
                        <label for="caption" style="
                            display: block;
                            margin-bottom: 5px;
                            color: #333;
                            font-weight: bold;
                        ">Caption:</label>
                        <input 
                            type="text" 
                            id="caption" 
                            name="caption"
                            placeholder="Enter caption here..."
                            style="
                                width: 100%;
                                padding: 10px;
                                border: 2px solid #ddd;
                                border-radius: 4px;
                                font-size: 14px;
                                box-sizing: border-box;
                                transition: border-color 0.3s ease;
                            "
                            onfocus="this.style.borderColor='#007bff'"
                            onblur="this.style.borderColor='#ddd'"
                        >
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="category" style="
                            display: block;
                            margin-bottom: 5px;
                            color: #333;
                            font-weight: bold;
                        ">Category:</label>
                        <select 
                            id="category" 
                            name="category"
                            style="
                                width: 100%;
                                padding: 10px;
                                border: 2px solid #ddd;
                                border-radius: 4px;
                                font-size: 14px;
                                box-sizing: border-box;
                                transition: border-color 0.3s ease;
                                background-color: white;
                                cursor: pointer;
                            "
                            onfocus="this.style.borderColor='#28a745'"
                            onblur="this.style.borderColor='#ddd'"
                        >
                            <option value="">Select a category...</option>
                            <option value="technology">Technology</option>
                            <option value="business">Business</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="sports">Sports</option>
                            <option value="news">News</option>
                            <option value="education">Education</option>
                            <option value="lifestyle">Lifestyle</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="file-info" id="fileInfo" style="display: none;">
                    <div class="file-name" id="fileName"></div>
                    <div class="file-size" id="fileSize"></div>
                </div>

                <div class="upload-status" id="uploadStatus"></div>

                <div class="button-container">
                    <button class="upload-button" id="uploadButton">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7,10 12,15 17,10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Upload File
                    </button>
                    <div id="spinner">⏳ Uploading, please wait...</div>

                    <button class="cancel-button" id="cancelButton">Cancel</button>
                </div>

                <input type="file" id="fileInput" class="file-input" hidden>
            </div>
        </section>
        
    </div>

    <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const fileNameDisplay = document.getElementById('fileName');
const fileSizeDisplay = document.getElementById('fileSize');
const fileInfo = document.getElementById('fileInfo');
const uploadStatus = document.getElementById('uploadStatus');
const uploadButton = document.getElementById('uploadButton');
const cancelButton = document.getElementById('cancelButton');
const spinner = document.getElementById('spinner');
const captionInput = document.getElementById('caption');
const categoryInput = document.getElementById('category');

let selectedFile = null;

// Click-to-select file
uploadArea.addEventListener('click', () => fileInput.click());

// File selected
fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));

// Drag styling
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragging');
});
uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragging'));
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragging');
    handleFile(e.dataTransfer.files[0]);
});

function handleFile(file) {
    if (!file) return;

    if (file.size > 1024 * 1024 * 1024) {
        uploadStatus.textContent = "❌ File too large. Max 1GB allowed.";
        fileInfo.style.display = "none";
        selectedFile = null;
        return;
    }

    selectedFile = file;
    fileNameDisplay.textContent = "📄 Name: " + file.name;
    fileSizeDisplay.textContent = "📦 Size: " + (file.size / (1024 * 1024)).toFixed(2) + " MB";
    fileInfo.style.display = "block";
    uploadStatus.textContent = "";
}

// Upload button clicked
uploadButton.addEventListener('click', () => {
    if (!selectedFile) {
        uploadStatus.textContent = "⚠️ Please select a file first.";
        return;
    }

    const caption = captionInput.value.trim();
    const category = categoryInput.value;

    if (!caption || !category) {
        uploadStatus.textContent = "⚠️ Please fill in both caption and category.";
        return;
    }

    const formData = new FormData();
    formData.append('video', selectedFile);
    formData.append('caption', caption);
    formData.append('category', category);

    uploadStatus.textContent = "";
    uploadButton.disabled = true;
    spinner.style.display = 'block';

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        uploadButton.disabled = false;
        spinner.style.display = 'none';

        if (data.toLowerCase().includes("success")) {
            Swal.fire({
                icon: 'success',
                title: 'Upload Successful',
                text: 'Your file has been uploaded!',
                confirmButtonColor: '#3085d6',
                timer: 2000,
                timerProgressBar: true
            });

            setTimeout(() => {
                window.location.href = 'dashboard.php'; // Replace with your redirect
            }, 2000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: data,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(err => {
        uploadButton.disabled = false;
        spinner.style.display = 'none';

        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: 'Something went wrong. Try again.',
            confirmButtonColor: '#d33'
        });
        console.error(err);
    });
});

// Cancel selection
cancelButton.addEventListener('click', () => {
    selectedFile = null;
    fileInput.value = "";
    fileInfo.style.display = "none";
    uploadStatus.textContent = "";
    captionInput.value = "";
    categoryInput.value = "";
});



    </script>
</body>
</html>