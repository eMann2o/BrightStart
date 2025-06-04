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
    <title>My Uploaded Files</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .table-container {
            max-width: 1200px;
            margin: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fade-in 0.8s ease-out;
        }

        .table-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 1.5rem 2rem;
            color: white;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .table-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .table-wrapper {
            overflow-x: auto;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
            background: white;
        }

        th {
            background: linear-gradient(135deg, #f8f9ff, #e8ebff);
            color: #4c5fa8;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #e1e5f0;
        }

        th:first-child {
            padding-left: 2rem;
        }

        th:last-child {
            padding-right: 2rem;
        }

        td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            color: #374151;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        td:first-child {
            padding-left: 2rem;
            font-weight: 500;
        }

        td:last-child {
            padding-right: 2rem;
        }

        tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        tbody tr:hover td {
            color: #1f2937;
        }

        /* Status badges */
        .status {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status.pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .status.inactive {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        /* Action buttons */
        .actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-view:hover {
            background: linear-gradient(135deg, #5a6fd8, #6c42a0);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* Pagination */
        .pagination {
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            background: rgba(248, 249, 255, 0.8);
        }

        .pagination button {
            padding: 0.75rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            color: #4c5fa8;
            border-radius: 8px;
            font-weight: 500;
            min-width: 44px;
        }

        .pagination button:hover:not(.disabled) {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-1px);
        }

        .pagination button.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .pagination button.disabled {
            background: rgba(0, 0, 0, 0.05);
            color: #9ca3af;
            cursor: not-allowed;
        }

        .pagination-info {
            margin: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Icons */
        .icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }

        /* Animations */
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .table-header {
                padding: 1rem 1.5rem;
            }

            table {
                min-width: 600px;
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem 1rem;
            }

            th:first-child, td:first-child {
                padding-left: 1.5rem;
            }

            th:last-child, td:last-child {
                padding-right: 1.5rem;
            }

            button {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }

            .actions {
                flex-direction: column;
                gap: 0.25rem;
            }

            .pagination {
                padding: 1.5rem;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .pagination {
                flex-direction: column;
                gap: 1rem;
            }

            .pagination-controls {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
                justify-content: center;
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
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
            </div>
            
            <div class="menu-item" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
            </div>
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fas fa-users"></i>
                <span>Participants</span>
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
        
        <div class="table-container">
            <table id="videosTable" aria-label="Uploaded Videos Table">
                <thead>
                <tr>
                    <th>File Name</th>
                    <th>Uploader Name</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Rows inserted here dynamically -->
                </tbody>
            </table>

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
            const sessionEmail = <?= json_encode($_SESSION['email']) ?>;

            let currentPage = 1;
            const limit = 10;

            function fetchVideos(page = 1) {
                fetch(`fetch_videos.php?page=${page}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                    }
                    renderTable(data.videos);
                    renderPagination(data.totalVideos, data.limit, data.page);
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to fetch files.', 'error');
                    console.error(err);
                });
            }

            function renderTable(videos, sessionEmail) {
    tableBody.innerHTML = '';

    // Filter videos by logged-in user's email
    const userVideos = videos.filter(video => video.uploader_email === sessionEmail);

    if (userVideos.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No files found.</td></tr>';
        return;
    }

    userVideos.forEach(video => {
        const tr = document.createElement('tr');

        // Video name
        const nameTd = document.createElement('td');
        nameTd.textContent = video.file_name;
        tr.appendChild(nameTd);

        // Uploader username
            const uploaderTd = document.createElement('td');
            uploaderTd.textContent = video.name;
            tr.appendChild(uploaderTd);

        // Actions 
        const actionsTd = document.createElement('td');
        actionsTd.style.display = 'flex';
        actionsTd.style.gap = '8px';
        actionsTd.style.flexWrap = 'wrap';

        // View button
        const viewBtn = document.createElement('button');
        viewBtn.textContent = 'View';
        viewBtn.classList.add('btn-view');
        viewBtn.addEventListener('click', () => {
            const url = `../uploads/${encodeURIComponent(video.file_path)}`;
            window.open(url, '_blank');
        });
        actionsTd.appendChild(viewBtn);

        // Download button
        const downloadBtn = document.createElement('button');
        downloadBtn.textContent = 'Download';
        downloadBtn.classList.add('btn-download');
        downloadBtn.addEventListener('click', () => {
            const url = `../uploads/${encodeURIComponent(video.file_path)}`;
            const a = document.createElement('a');
            a.href = url;
            a.download = video.file_name;
            a.click();
        });
        actionsTd.appendChild(downloadBtn);

        tr.appendChild(actionsTd);
        tableBody.appendChild(tr);
    });
}


            function renderPagination(total, limit, page) {
                paginationControls.innerHTML = '';

                const totalPages = Math.ceil(total / limit);

                const createPageButton = (p) => {
                const btn = document.createElement('button');
                btn.textContent = p;
                if (p === page) {
                    btn.classList.add('disabled');
                    btn.disabled = true;
                }
                btn.addEventListener('click', () => {
                    currentPage = p;
                    fetchVideos(currentPage);
                });
                return btn;
                };

                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.textContent = 'Prev';
                prevBtn.disabled = page === 1;
                prevBtn.classList.toggle('disabled', page === 1);
                prevBtn.addEventListener('click', () => {
                if (page > 1) {
                    currentPage = page - 1;
                    fetchVideos(currentPage);
                }
                });
                paginationControls.appendChild(prevBtn);

                // Page numbers (limit to max 7 pages shown)
                let startPage = Math.max(1, page - 3);
                let endPage = Math.min(totalPages, page + 3);

                if (endPage - startPage < 6) {
                if (startPage === 1) {
                    endPage = Math.min(startPage + 6, totalPages);
                } else if (endPage === totalPages) {
                    startPage = Math.max(endPage - 6, 1);
                }
                }

                for (let p = startPage; p <= endPage; p++) {
                paginationControls.appendChild(createPageButton(p));
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Next';
                nextBtn.disabled = page === totalPages;
                nextBtn.classList.toggle('disabled', page === totalPages);
                nextBtn.addEventListener('click', () => {
                if (page < totalPages) {
                    currentPage = page + 1;
                    fetchVideos(currentPage);
                }
                });
                paginationControls.appendChild(nextBtn);
            }

            // Initial fetch
            fetchVideos(currentPage);
        </script>
    </div>
</body>
</html>