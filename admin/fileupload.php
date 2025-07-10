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
        /* Upload Form Styles - Modern Design Language */
.upload-container {
    max-width: 900px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.upload-header {
    padding: 32px 32px 24px;
    text-align: center;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.upload-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.upload-header p {
    font-size: 1rem;
    color: #6b7280;
    margin: 0;
}

#uploadForm {
    display: flex;
    gap: 32px;
    padding: 32px;
    align-items: flex-start;
}

.leftie {
    flex: 1;
    min-width: 0;
}

.rightie {
    flex: 1;
    min-width: 0;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    letter-spacing: 0.025em;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    background: #ffffff;
    transition: all 0.2s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
}

.form-group input {
    background-image: none;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input::placeholder {
    color: #9ca3af;
}

/* File Upload Area */
.file-upload-area {
    position: relative;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 48px 24px;
    text-align: center;
    background: #f9fafb;
    transition: all 0.2s ease;
    cursor: pointer;
    margin-bottom: 16px;
}

.file-upload-area:hover {
    border-color: #3b82f6;
    background: #f0f9ff;
}

.file-upload-area.dragover {
    border-color: #3b82f6;
    background: #dbeafe;
    transform: scale(1.02);
}

.file-upload-area input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.upload-icon {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.7;
}

.upload-text {
    font-size: 1rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 4px;
}

.upload-subtext {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Selected Files Display */
.selected-files {
    background: #f3f4f6;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    color: #374151;
    display: none;
}

.selected-files.show {
    display: block;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.file-item:last-child {
    border-bottom: none;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-icon {
    width: 24px;
    height: 24px;
    background: #3b82f6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
}

.file-name {
    font-weight: 500;
    color: #1f2937;
}

.file-size {
    color: #6b7280;
    font-size: 0.75rem;
}

.file-remove {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.file-remove:hover {
    background: #fee2e2;
}

/* File Preview */
.file-preview {
    display: none;
    margin-top: 16px;
}

.file-preview.show {
    display: block;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 8px;
    margin-top: 8px;
}

.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Upload Button */
.upload-btn {
    width: 100%;
    background: #3b82f6;
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.upload-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.upload-btn:active {
    transform: translateY(0);
}

.upload-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Progress Bar */
.progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 16px;
    display: none;
}

.progress-bar.show {
    display: block;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 4px;
}

/* Loading */
.loading {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px;
    background: #f0f9ff;
    border-radius: 8px;
    margin-bottom: 16px;
    color: #1e40af;
    font-size: 0.875rem;
}

.loading.show {
    display: flex;
}

.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid #bfdbfe;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Message */
.message {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    font-weight: 500;
    display: none;
}

.message.show {
    display: block;
}

.message.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.message.error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Responsive Design */
@media (max-width: 768px) {
    #uploadForm {
        flex-direction: column;
        gap: 24px;
        padding: 24px;
    }
    
    .upload-header {
        padding: 24px 24px 20px;
    }
    
    .upload-header h1 {
        font-size: 1.5rem;
    }
    
    .file-upload-area {
        padding: 32px 16px;
    }
    
    .upload-icon {
        font-size: 2.5rem;
    }
    
    .preview-grid {
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    }
}

@media (max-width: 480px) {
    .upload-container {
        margin: 16px;
        border-radius: 12px;
    }
    
    #uploadForm {
        padding: 16px;
    }
    
    .upload-header {
        padding: 20px 16px 16px;
    }
    
    .upload-header h1 {
        font-size: 1.25rem;
        flex-direction: column;
        gap: 8px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .file-upload-area {
        padding: 24px 12px;
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
                    <h1><i class="fa-solid fa-image"></i> Upload Photos</h1>
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
                            <label for="photoTitle">Photo Description</label>
                            <input type="text" id="photoTitle" name="photo_title" placeholder="Enter a description for your photos" require>
                        </div>
                    </div>

                    <div class="rightie">
                            <div class="form-group">
                            <label>Select Photos</label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <input type="file" id="photoFiles" name="photos[]" accept="image/*" multiple required>
                                <div class="upload-icon"><i class="fa-solid fa-folder-open"></i></div>
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
                Swal.fire('Error', 'Please enter a photo description.', 'error');
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