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
    <title>Upload To Gallery</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .upload-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            width: 100%;
        }

        .upload-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .upload-header h1 {
            color: #333;
            font-size: 2.2em;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .upload-header p {
            color: #666;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1.1em;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .file-upload-area {
            position: relative;
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            background: rgba(102, 126, 234, 0.03);
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .file-upload-area:hover {
            background: rgba(102, 126, 234, 0.08);
            border-color: #764ba2;
        }

        .file-upload-area.dragover {
            background: rgba(102, 126, 234, 0.15);
            border-color: #764ba2;
            transform: scale(1.02);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 3em;
            color: #667eea;
            margin-bottom: 15px;
        }

        .upload-text {
            color: #667eea;
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .upload-subtext {
            color: #999;
            font-size: 0.9em;
        }

        .file-preview {
            margin-top: 20px;
            display: none;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .preview-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e1e5e9;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .preview-remove:hover {
            background: rgba(255, 0, 0, 1);
            transform: scale(1.1);
        }

        .upload-btn {
            width: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .upload-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .upload-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .selected-files {
            color: #667eea;
            font-weight: 600;
            margin-top: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e1e5e9;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 15px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
        }

        @media (max-width:700px) {
            form{
                flex-direction: column;
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
        
        <section class="content" style="justify-items: center;">
            <div class="upload-container">
                <div class="upload-header">
                    <h1>📸 Upload Photos</h1>
                    <p>Share your moments with the world</p>
                </div>

                <form id="uploadForm" enctype="multipart/form-data" style="display: flex; justify-content: space-between;">
                    <div class="leftie">
                        <div class="form-group">
                            <label for="albumOption">Album Option</label>
                            <select id="albumOption" name="album_option" required>
                                <option value="">Select an option</option>
                                <option value="new">Create New Album</option>
                                <option value="existing">Add to Existing Album</option>
                            </select>
                        </div>

                        <div class="form-group" id="newAlbumGroup" style="display: none;">
                            <label for="newAlbumName">New Album Name</label>
                            <input type="text" id="newAlbumName" name="new_album_name" placeholder="Enter new album name">
                        </div>

                        <div class="form-group" id="existingAlbumGroup" style="display: none;">
                            <label for="existingAlbum">Select Album</label>
                            <select id="existingAlbum" name="existing_album">
                                <option value="">Loading albums...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="photoTitle">Photo Description (optional)</label>
                            <input type="text" id="photoTitle" name="photo_title" placeholder="Enter a description for your photos">
                        </div>
                    </div>

                    <div class="rightie">
                            <div class="form-group">
                            <label>Select Photos</label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <input type="file" id="photoFiles" name="photos[]" accept="image/*" multiple required>
                                <div class="upload-icon">📁</div>
                                <div class="upload-text">Click to select photos</div>
                                <div class="upload-subtext">or drag and drop images here</div>
                            </div>
                            <div class="selected-files" id="selectedFiles"></div>
                            <div class="file-preview" id="filePreview">
                                <div class="preview-grid" id="previewGrid"></div>
                            </div>
                        </div>

                        <button type="submit" class="upload-btn" id="uploadBtn">
                            Upload Photos
                        </button>

                        <div class="progress-bar" id="progressBar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>

                        <div class="loading" id="loading">
                            <div class="loading-spinner"></div>
                            <div>Uploading photos...</div>
                        </div>

                        <div class="message" id="message"></div>
                    </div>
                </form>
            </div>
        </section>

    </div>
            

    <script>
        

        // Sidebar toggle functionality
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let selectedFiles = [];
        let albums = [];

        const fileUploadArea = document.getElementById('fileUploadArea');
        const photoFiles = document.getElementById('photoFiles');
        const selectedFilesDiv = document.getElementById('selectedFiles');
        const filePreview = document.getElementById('filePreview');
        const previewGrid = document.getElementById('previewGrid');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const loading = document.getElementById('loading');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const albumOption = document.getElementById('albumOption');
        const newAlbumGroup = document.getElementById('newAlbumGroup');
        const existingAlbumGroup = document.getElementById('existingAlbumGroup');
        const newAlbumName = document.getElementById('newAlbumName');
        const existingAlbum = document.getElementById('existingAlbum');
        const photoTitle = document.getElementById('photoTitle');

        loadAlbums();
        albumOption.addEventListener('change', handleAlbumOptionChange);
        photoFiles.addEventListener('change', handleFileSelect);
        fileUploadArea.addEventListener('dragover', handleDragOver);
        fileUploadArea.addEventListener('dragleave', handleDragLeave);
        fileUploadArea.addEventListener('drop', handleDrop);
        uploadForm.addEventListener('submit', handleSubmit);

        function handleFileSelect(event) {
            const files = event.target.files;
            selectedFiles = Array.from(files);
            updateFileDisplay();
        }

        function handleDragOver(event) {
            event.preventDefault();
            fileUploadArea.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.preventDefault();
            fileUploadArea.classList.remove('dragover');
        }

        function handleDrop(event) {
            event.preventDefault();
            fileUploadArea.classList.remove('dragover');

            const files = event.dataTransfer.files;
            selectedFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
            updateFileDisplay();
        }

        function updateFileDisplay() {
            if (selectedFiles.length === 0) {
                selectedFilesDiv.textContent = '';
                filePreview.style.display = 'none';
                return;
            }

            selectedFilesDiv.textContent = `${selectedFiles.length} file(s) selected`;
            filePreview.style.display = 'block';
            previewGrid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="preview-remove" onclick="removeFile(${index})">×</button>
                    `;
                    previewGrid.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFileDisplay();
        }

        function loadAlbums() {
            fetch('get_albums.php')
                .then(response => response.json())
                .then(data => {
                    albums = data.albums || [];
                    updateAlbumDropdown();
                })
                .catch(() => {
                    existingAlbum.innerHTML = '<option value="">Error loading albums</option>';
                });
        }

        function updateAlbumDropdown() {
            existingAlbum.innerHTML = '<option value="">Select an album</option>';
            albums.forEach(album => {
                const option = document.createElement('option');
                option.value = album.id;
                option.textContent = album.name;
                existingAlbum.appendChild(option);
            });
        }

        function handleAlbumOptionChange() {
            const selectedOption = albumOption.value;

            newAlbumGroup.style.display = 'none';
            existingAlbumGroup.style.display = 'none';
            newAlbumName.removeAttribute('required');
            existingAlbum.removeAttribute('required');

            if (selectedOption === 'new') {
                newAlbumGroup.style.display = 'block';
                newAlbumName.setAttribute('required', 'required');
            } else if (selectedOption === 'existing') {
                existingAlbumGroup.style.display = 'block';
                existingAlbum.setAttribute('required', 'required');
            }
        }

        function validateForm() {
            if (albumOption.value === 'new' && newAlbumName.value.trim() === '') {
                Swal.fire('Error', 'Please enter a new album name.', 'error');
                return false;
            }
            if (albumOption.value === 'existing' && existingAlbum.value === '') {
                Swal.fire('Error', 'Please select an existing album.', 'error');
                return false;
            }
            if (photoTitle.value.trim() === '') {
                Swal.fire('Error', 'Please enter a photo title.', 'error');
                return false;
            }
            if (selectedFiles.length === 0) {
                Swal.fire('Error', 'Please select at least one photo.', 'error');
                return false;
            }
            return true;
        }

        function handleSubmit(event) {
            event.preventDefault();

            if (!validateForm()) {
                return;
            }

            const formData = new FormData();
            formData.append('album_option', albumOption.value);
            if (albumOption.value === 'new') {
                formData.append('album_name', newAlbumName.value.trim());
            } else if (albumOption.value === 'existing') {
                formData.append('album_id', existingAlbum.value);
            }
            formData.append('photo_title', photoTitle.value.trim());

            selectedFiles.forEach((file) => {
                formData.append('photos[]', file);
            });

            uploadBtn.disabled = true;
            loading.style.display = 'block';
            progressBar.style.display = 'block';
            progressFill.style.width = '0%';

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressFill.style.width = percentComplete + '%';
                }
            });

            xhr.addEventListener('load', function() {
                loading.style.display = 'none';
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                progressFill.style.width = '0%';

                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        uploadForm.reset();
                        selectedFiles = [];
                        updateFileDisplay();
                        handleAlbumOptionChange();
                        loadAlbums();
                    } else {
                        Swal.fire('Error', response.message || 'Upload failed.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Success', 'Upload completed successfully!', 'success');
                    uploadForm.reset();
                    selectedFiles = [];
                    updateFileDisplay();
                    handleAlbumOptionChange();
                    loadAlbums();
                }
            });

            xhr.addEventListener('error', function() {
                loading.style.display = 'none';
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                Swal.fire('Error', 'Upload failed. Please try again.', 'error');
            });

            xhr.open('POST', 'upload.php', true);
            xhr.send(formData);
        }
    </script>

</body>
</html>