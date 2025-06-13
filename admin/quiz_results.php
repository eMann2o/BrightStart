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
      $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('siso', 'teacher', 'headteacher');"); 
      $stmt->execute();
  
      // Fetch all data from the query
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  } catch (PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
      exit();
}

$stmt = $pdo->query(" SELECT 
        u.name,
        l.title AS lesson_title,
        q.question AS theory_question,
        a.answer AS theory_answer,
        submission_group.created_at,
        submission_group.attempt_number,
        submission_group.mcq_score
    FROM (
        SELECT 
            a.user_id,
            q.lesson_id,
            a.created_at,
            ROW_NUMBER() OVER (PARTITION BY a.user_id, q.lesson_id ORDER BY a.created_at) AS attempt_number,
            (
                SELECT COUNT(*) 
                FROM lesson_quiz_answers a2
                JOIN lesson_quizzes q2 ON a2.quiz_id = q2.id
                WHERE 
                    a2.user_id = a.user_id AND 
                    q2.lesson_id = q.lesson_id AND 
                    a2.created_at = a.created_at AND 
                    q2.type = 'mcq' AND a2.is_correct = 1
            ) AS mcq_score
        FROM lesson_quiz_answers a
        JOIN lesson_quizzes q ON a.quiz_id = q.id
        WHERE q.type = 'theory'
        GROUP BY a.user_id, q.lesson_id, a.created_at
    ) AS submission_group
    JOIN users u ON u.id = submission_group.user_id
    JOIN lessons l ON l.id = submission_group.lesson_id
    JOIN lesson_quiz_answers a ON 
        a.user_id = submission_group.user_id AND 
        a.created_at = submission_group.created_at
    JOIN lesson_quizzes q ON a.quiz_id = q.id AND q.type = 'theory'
    ORDER BY submission_group.created_at DESC;

");
$rows = $stmt->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Results</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .filters {
            padding: 30px;
            background-color: #fafafa;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input, .filter-select {
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
            background: white;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .filter-input::placeholder {
            color: #9ca3af;
        }

        .table-container {
            overflow-x: auto;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            background-color: #3a7bd5;
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        th:first-child {
            border-top-left-radius: 0;
        }

        th:last-child {
            border-top-right-radius: 0;
        }

        td {
            padding: 16px 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) {
            background-color: #fdfdfd;
        }

        .student-name {
            font-weight: 600;
            color: #1f2937;
        }

        .lesson-title {
            color: #4f46e5;
            font-weight: 500;
        }

        .attempt-number {
            background-color: #10b981;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }

        .theory-question {
            max-width: 300px;
            color: #374151;
            line-height: 1.5;
        }

        .theory-answer {
            max-width: 350px;
            color: #4b5563;
            line-height: 1.5;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #4f46e5;
        }

        .score {
            font-weight: 700;
            font-size: 1.1rem;
            text-align: center;
        }

        .score-5 { color: #059669; }
        .score-4 { color: #65a30d; }
        .score-3 { color: #d97706; }
        .score-2 { color: #dc2626; }
        .score-1, .score-0 { color: #b91c1c; }

        .timestamp {
            color: #6b7280;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .pagination {
            padding: 30px;
            text-align: center;
            background-color: #fafafa;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .pagination-info {
            color: #666;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .pagination-btn {
            min-width: 44px;
            height: 44px;
            padding: 0 12px;
            border: none;
            border-radius: 8px;
            background-color: #f8f9fa;
            color: #495057;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .pagination-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .pagination-btn:hover:not(:disabled)::before {
            left: 100%;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: #212529;
        }

        .pagination-btn:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background-color: #f1f3f4;
            color: #9aa0a6;
        }

        .pagination-btn.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 3px 10px rgba(79, 70, 229, 0.3);
            transform: translateY(-1px);
        }

        .pagination-btn.active:hover {
            background-color: #3730a3;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .pagination-btn.nav-btn {
            padding: 0 16px;
            font-weight: 600;
        }

        .pagination-btn.nav-btn:hover:not(:disabled) {
            background-color: #4f46e5;
            color: white;
        }

        @media (max-width: 1000px) {
            .pagination {
                flex-direction: column;
                text-align: center;
            }
        }

        .no-results {
            text-align: center;
            padding: 60px 30px;
            color: #6b7280;
            font-size: 1.1rem;
        }

        @media (max-width: 1000px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: auto;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 12px 8px;
            }
            
            .theory-question, .theory-answer {
                max-width: 200px;
            }
        }

        .stats-bar {
            background-color: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .headers {
            padding: 30px;
            color: white;
            text-align: center;
        }
    </style>
    
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Bright<span>Start</span></h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" onclick="window.location.href='dashboard.php';">
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
                        <div class="user-name">
                            <?php
                            $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User";
                            echo htmlspecialchars($name);
                            ?>
                        </div>
                        <div class="user-role">
                            <?php
                            $role = isset($_SESSION['role']) ? $_SESSION['role'] : "Unknown User";
                            echo htmlspecialchars($role);
                            ?>
                        </div>
                    </div>
                    <div class="user-avatar" onclick="window.location.href='logout.php';"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                </div>
            </div>
        </div>
        
        <?php
            // Fetch lessons grouped by module
            $modules = $pdo->query("
                SELECT m.id AS module_id, m.title AS module_title, l.id AS lesson_id, l.title AS lesson_title
                FROM modules m
                JOIN lessons l ON l.id = m.id
                ORDER BY m.title, l.title
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Group lessons by module
            $grouped = [];
            foreach ($modules as $row) {
                $grouped[$row['module_title']][] = [
                    'id' => $row['lesson_id'],
                    'title' => $row['lesson_title']
                ];
            }
        ?>
        <div class="container">
            <div class="headers">
                <h1>Quiz Results</h1>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number" id="totalAttempts">0</div>
                    <div class="stat-label">Total Attempts</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="uniqueStudents">0</div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="avgScore">0</div>
                    <div class="stat-label">Avg Score</div>
                </div>
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label class="filter-label">Student Name</label>
                    <input type="text" id="nameFilter" class="filter-input" placeholder="Search for student name..." onkeyup="filterTable()">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Lesson</label>
                    <select id="lessonFilter" class="filter-select" onchange="filterTable()">
                        <option value="">All Lessons</option>
                        <?php foreach ($grouped as $module => $lessons): ?>
                            <optgroup label="<?= htmlspecialchars($module) ?>">
                                <?php foreach ($lessons as $lesson): ?>
                                    <option value="<?= strtolower($lesson['title']) ?>"><?= htmlspecialchars($lesson['title']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="quizTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Lesson</th>
                            <th>Attempt</th>
                            <th>Theory Question</th>
                            <th>Theory Answer</th>
                            <th>Score</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['lesson_title']) ?></td>
                                <td><?= $row['attempt_number'] ?></td>
                                <td><?= htmlspecialchars($row['theory_question']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['theory_answer'])) ?></td>
                                <td><?= $row['mcq_score'] ?>/5</td>
                                <td><?= $row['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="noResults" class="no-results" style="display: none;">
                    <h3>No results found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            </div>

            <div class="pagination" id="pagination">
            <div class="pagination-info">
                <span id="paginationInfo">Showing 1-10 of 12 results</span>
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn nav-btn" id="prevBtn" onclick="changePage(-1)">‹ Prev</button>
                <span id="pageNumbers"></span>
                <button class="pagination-btn nav-btn" id="nextBtn" onclick="changePage(1)">Next ›</button>
            </div>
        </div>
        </div>

        <script>

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            let currentPage = 1;
        const rowsPerPage = 10;
        let allRows = [];
        let filteredRows = [];

        function initializePagination() {
            const tbody = document.getElementById('tableBody');
            allRows = Array.from(tbody.getElementsByTagName('tr'));
            filteredRows = [...allRows];
            showPage(1);
            updatePaginationControls();
        }

        function showPage(page) {
            const startIndex = (page - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;

            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');

            // Show only the rows for current page
            for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
                filteredRows[i].style.display = '';
            }

            currentPage = page;
            updatePaginationInfo();
        }

        function updatePaginationControls() {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const pageNumbers = document.getElementById('pageNumbers');

            // Update button states
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;

            // Create page number buttons
            let pageNumbersHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                pageNumbersHTML += `<button class="pagination-btn ${activeClass}" onclick="goToPage(${i})">${i}</button>`;
            }
            pageNumbers.innerHTML = pageNumbersHTML;
        }

        function updatePaginationInfo() {
            const start = filteredRows.length > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
            const end = Math.min(currentPage * rowsPerPage, filteredRows.length);
            const total = filteredRows.length;
            
            document.getElementById('paginationInfo').textContent = 
                `Showing ${start}-${end} of ${total} results`;
        }

        function changePage(direction) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                showPage(newPage);
                updatePaginationControls();
            }
        }

        function goToPage(page) {
            showPage(page);
            updatePaginationControls();
        }

        function filterTable() {
            const nameFilter = document.getElementById('nameFilter').value.toLowerCase();
            const lessonFilter = document.getElementById('lessonFilter').value.toLowerCase();
            const noResults = document.getElementById('noResults');
            const table = document.getElementById('quizTable');
            
            // Filter rows based on search criteria
            filteredRows = allRows.filter(row => {
                const studentName = row.cells[0].textContent.toLowerCase();
                const lessonTitle = row.cells[1].textContent.toLowerCase();
                
                const nameMatch = studentName.includes(nameFilter);
                const lessonMatch = lessonFilter === '' || lessonTitle.includes(lessonFilter);
                
                return nameMatch && lessonMatch;
            });

            // Reset to page 1 when filtering
            currentPage = 1;
            
            // Show/hide no results message
            if (filteredRows.length === 0) {
                noResults.style.display = 'block';
                table.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                table.style.display = 'table';
                showPage(1);
            }

            updatePaginationControls();
            updateStats();
        }

        function updateStats() {
            const totalAttempts = filteredRows.length;
            const uniqueStudents = new Set(filteredRows.map(row => 
                row.cells[0].textContent
            )).size;
            
            const scores = filteredRows.map(row => {
                const scoreText = row.cells[5].textContent;
                return parseInt(scoreText.split('/')[0]);
            });
            
            const avgScore = scores.length > 0 ? 
                (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : 0;

            document.getElementById('totalAttempts').textContent = totalAttempts;
            document.getElementById('uniqueStudents').textContent = uniqueStudents;
            document.getElementById('avgScore').textContent = avgScore;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializePagination();
            updateStats();
        });
        </script>
    </div>
</body>
</html>