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
    <title>Uploaded Files</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        /* Table styling */
        #videosTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        #videosTable thead {
            background-color: #3a7bd5;
            color: white;
        }

        #videosTable th,
        #videosTable td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        #videosTable tr:hover {
            background-color: #f5f5f5;
        }

        #videosTable td:last-child {
            display: flex;
            gap: 10px;
        }

        /* Action buttons */
        .btn-download, .btn-view,
        .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .btn-download {
            background-color: #007bff;
        }

        .btn-view {
            background-color:rgb(22, 162, 0);
        }

        .btn-download:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #a71d2a;
        }

        /* Pagination controls */
        #paginationControls {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        #paginationControls button {
            padding: 6px 12px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        #paginationControls button:hover:not(.active):not(:disabled) {
            background-color: #e2e6ea;
        }

        #paginationControls button.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        #paginationControls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Category Filter */
        #categoryFilter {
            margin-bottom: 1rem;
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            background-color: #fff;
            font-size: 1rem;
        }
        .table-wrapper {
            overflow-x: auto;
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
        <label for="categoryFilter">Filter by STEM Focus Area</label>
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <!-- Category options will be added dynamically -->
        </select>
        <div class="table-container">
            <div class="table-wrapper">
                <table id="videosTable" aria-label="Uploaded Videos Table">
                    <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Grade/Class</th>
                        <th>STEM Focus Area</th>
                        <th>Activity Type</th>
                        <th>Caption</th>
                        <th>Uploader</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Rows inserted here dynamically -->
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="paginationControls">
                <!-- Pagination buttons inserted here dynamically -->
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            const tableBody = document.querySelector('#videosTable tbody');
            const paginationControls = document.getElementById('paginationControls');
            const categoryFilter = document.getElementById('categoryFilter');

            let currentPage = 1;
            let allVideos = [];

            function fetchVideos(page = 1, category = '') {
                let url = `fetch_videos.php?page=${page}`;
                if (category) {
                    url += `&category=${encodeURIComponent(category)}`;
                }

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            Swal.fire('Error', data.error, 'error');
                            return;
                        }

                        allVideos = data.videos;
                        renderTable(allVideos);
                        renderPagination(data.totalVideos, data.limit, data.page);
                        populateCategoryFilter(data.categories);
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Failed to fetch videos.', 'error');
                        console.error(err);
                    });
            }

            function renderTable(videos) {
                tableBody.innerHTML = '';

                if (videos.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No files found.</td></tr>';
                    return;
                }

                videos.forEach(video => {
                    const tr = document.createElement('tr');

                    tr.innerHTML = `
                        <td>${video.file_name}</td>
                        <td>${video.grade || 'N/A'}</td>
                        <td>${video.category || 'N/A'}</td>
                        <td>${video.activity || 'N/A'}</td>
                        <td>${video.caption || 'No caption'}</td>
                        <td>${video.name}</td>
                    `;

                    const actionsTd = document.createElement('td');

                    const viewBtn = document.createElement('button');
                    viewBtn.textContent = 'View';
                    viewBtn.classList.add('btn-download');
                    viewBtn.onclick = () => {
                        const url = `../uploads/${encodeURIComponent(video.file_path)}`;
                        window.open(url, '_blank');
                    };
                    actionsTd.appendChild(viewBtn);

                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Delete';
                    deleteBtn.classList.add('btn-delete');
                    deleteBtn.onclick = () => confirmDelete(video.id, video.file_path);
                    actionsTd.appendChild(deleteBtn);

                    tr.appendChild(actionsTd);
                    tableBody.appendChild(tr);
                });
            }

            function populateCategoryFilter(categories) {
                const selected = categoryFilter.value;
                categoryFilter.innerHTML = '<option value="">All Categories</option>';

                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    if (category === selected) option.selected = true;
                    categoryFilter.appendChild(option);
                });
            }

            categoryFilter.addEventListener('change', () => {
                currentPage = 1;
                fetchVideos(currentPage, categoryFilter.value);
            });

            function renderPagination(total, limit, page) {
                paginationControls.innerHTML = '';
                const totalPages = Math.ceil(total / limit);

                const createButton = (text, disabled, onClick) => {
                    const btn = document.createElement('button');
                    btn.textContent = text;
                    btn.disabled = disabled;
                    if (!disabled) btn.addEventListener('click', onClick);
                    return btn;
                };

                paginationControls.appendChild(createButton('Prev', page === 1, () => {
                    currentPage--;
                    fetchVideos(currentPage, categoryFilter.value);
                }));

                let startPage = Math.max(1, page - 3);
                let endPage = Math.min(totalPages, page + 3);

                for (let i = startPage; i <= endPage; i++) {
                    const btn = createButton(i, i === page, () => {
                        currentPage = i;
                        fetchVideos(currentPage, categoryFilter.value);
                    });
                    if (i === page) btn.classList.add('active');
                    paginationControls.appendChild(btn);
                }

                paginationControls.appendChild(createButton('Next', page === totalPages, () => {
                    currentPage++;
                    fetchVideos(currentPage, categoryFilter.value);
                }));
            }

            function confirmDelete(id, filePath) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the video.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteVideo(id, filePath);
                    }
                });
            }

            function deleteVideo(id, filePath) {
                fetch('delete_video.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id, file_path: filePath})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', 'Video has been deleted.', 'success');
                        fetchVideos(currentPage, categoryFilter.value);
                    } else {
                        Swal.fire('Error', data.error || 'Failed to delete video.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to delete video.', 'error');
                });
            }

            // Load on first visit
            fetchVideos(currentPage);

        </script>
    </div>
</body>
</html>