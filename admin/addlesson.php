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
    <title>Add a lesson</title>
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>


        .container {
        max-width: 1000px;
        margin: auto;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .container h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            margin-bottom: 10px;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: #fff;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .file-input {
            padding: 10px 0;
        }

        .file-input-label {
            display: block;
            margin-bottom: 8px;
        }

        .file-input-info {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 4px;
        }

        .btn {
    display: inline-block;
    background-color: #3498db;
    color: white;
    border: none;
    padding: 12px 20px;
    width: 100%;
    max-width: 250px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    margin-top: 15px;
    transition: all 0.3s ease;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.btn:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media screen and (max-width: 1000px) {
    .btn {
        width: 100%;
        padding: 15px 20px;
        font-size: 14px;
        max-width: none;
    }
}

@media screen and (max-width: 480px) {
    .btn {
        padding: 12px 16px;
        font-size: 13px;
    }
}

        /* File input styling */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-button {
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            padding: 8px 12px;
            background-color: #ecf0f1;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-input-button:hover {
            background-color: #dfe6e9;
        }


        .file-input-real {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-name {
            margin-left: 10px;
            font-size: 14px;
            color: #7f8c8d;
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
            <div class="menu-item active" onclick="window.location.href='courses.php';">
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
        
        <div class="welcome-section">
            
            
            <button class="customize-btn" onclick="window.location.href='addmodule.php';">
                Add a Course
                <i class="fas fa-cog"></i>
            </button>
            <button class="customize-btn" onclick="window.location.href='addcourse.php';">
                Add a Module
                <i class="fas fa-cog"></i>
            </button>
            <button class="customize-btn" onclick="window.location.href='addlesson.php';">
                Add a lesson
                <i class="fas fa-cog"></i>
            </button>
        </div>
        
        <section class="content">
            <div class="card">
                <div class="container">
                    <!-- add_lesson.php -->
                    <form method="POST" action="addlesson.php" enctype="multipart/form-data">
                        <h2>Create New Lesson</h2>

                        <div class="form-group">
                            <label for="course_id">Select Module:</label>
                            <select class="form-control" name="course_id" id="course_id" required>
                                <?php
                                require 'db.php';
                                $courses = $pdo->query("SELECT id, title FROM courses")->fetchAll();
                                foreach ($courses as $course) {
                                    echo "<option value='{$course['id']}'>{$course['title']}</option>";
                                }
                                ?>                                
                            </select> 
                            
                        
                        </div> 

                        <div class="form-group">
                            <label for="title">Lesson Title:</label>
                            <input type="text" class="form-control" name="title" id="title" maxlength="255" required placeholder="Enter lesson title">
                        </div>

                        <div class="form-group">
                            <label for="content">Content:</label>
                            <textarea class="form-control" name="content" id="content" required placeholder="Enter lesson content..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="file-input-label">Upload Video (MP4):</label>
                            <div class="file-input-wrapper">
                                <button type="button" class="file-input-button">Choose Video File</button>
                                <span class="file-input-name">No file chosen</span>
                                <input type="file" class="file-input-real" name="video" id="video" accept="video/mp4" required>
                            </div>
                            <div class="file-input-info">Maximum file size: 50MB</div>
                        </div>

                        <div class="form-group">
                            <label class="file-input-label">File Attachment (PDF, DOCX. PPT):</label>
                            <div class="file-input-wrapper">
                                <button type="button" class="file-input-button">Choose File</button>
                                <span class="file-input-name">No file chosen</span>
                                <input type="file" class="file-input-real" name="file" id="file" accept=".pdf,.docx,.pptx">
                            </div>
                            <div class="file-input-info">Optional: Upload supporting documents</div>
                        </div>

                        <hr>
                        <h3>Lesson Quiz (5 Multiple Choice & 1 Theory Question)</h3><br>

                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="form-group">
                                <label for="mcq_question<?= $i ?>">Question <?= $i ?>:</label>
                                <input type="text" class="form-control" name="mcq_question<?= $i ?>" id="mcq_question<?= $i ?>" placeholder="Enter question <?= $i ?>" required>
                                <label>Enter possible answers</label>

                                <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
                                    <input type="text" class="form-control mt-1" name="mcq<?= $i ?>_option<?= $option ?>" placeholder="Option <?= $option ?>" required>
                                <?php endforeach; ?>

                                <label for="mcq<?= $i ?>_answer">Correct Answer:</label>
                                <select name="mcq<?= $i ?>_answer" class="form-control" id="mcq<?= $i ?>_answer" required>
                                    <option value="">-- Select Correct Option --</option>
                                    <option value="A">Option A</option><br>
                                    <option value="B">Option B</option><br>
                                    <option value="C">Option C</option><br>
                                    <option value="D">Option D</option><br>
                                </select>
                            </div>
                        <?php endfor; ?>

                        <div class="form-group">
                            <label for="theory_question">Theory Question:</label>
                            <textarea name="theory_question" id="theory_question" class="form-control" placeholder="Enter theory question..." required></textarea>
                        </div>


                        <button type="submit" class="btn">Create Lesson</button>
                    </form>

                </div>
            </div>
        </section>
        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

            // File input display functionality
            document.querySelectorAll('.file-input-real').forEach(input => {
                const button = input.previousElementSibling.previousElementSibling;
                const fileNameDisplay = input.previousElementSibling;

                input.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                    } else {
                        fileNameDisplay.textContent = 'No file chosen';
                    }
                });

                // Trigger file dialog when button is clicked
                button.addEventListener('click', function() {
                    input.click();
                });
            });

        </script>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';

    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Validate video upload
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('⚠️ Video upload failed.'); window.location.href = 'addlesson.php';</script>";
        exit;
    }

    $videoBlob = file_get_contents($_FILES['video']['tmp_name']);

    // Handle optional file
    $fileBlob = null;
    $fileName = null;
    $fileMimeType = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['file']['name'];
        $fileMimeType = $_FILES['file']['type'];
        if (empty($fileMimeType) && function_exists('mime_content_type')) {
            $fileMimeType = mime_content_type($_FILES['file']['tmp_name']);
        }
        $fileBlob = file_get_contents($_FILES['file']['tmp_name']);
    }

    // Ensure file metadata columns exist
    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM lessons LIKE 'file_mime_type'");
        if ($checkColumn->rowCount() == 0) {
            $pdo->exec("ALTER TABLE lessons 
                        ADD COLUMN file_mime_type VARCHAR(100) AFTER file_attachment,
                        ADD COLUMN file_name VARCHAR(255) AFTER file_attachment,
                        MODIFY COLUMN file_attachment LONGBLOB");
        }
    } catch (PDOException $e) {
        error_log("Schema check failed: " . $e->getMessage());
    }

    // Insert lesson
    $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, content, video, file_attachment, file_name, file_mime_type) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$course_id, $title, $content, $videoBlob, $fileBlob, $fileName, $fileMimeType]);
    $lesson_id = $pdo->lastInsertId();

    // Insert 5 MCQs
    for ($i = 1; $i <= 5; $i++) {
        $q = trim($_POST["mcq_question$i"]);
        $a = trim($_POST["mcq{$i}_optionA"]);
        $b = trim($_POST["mcq{$i}_optionB"]);
        $c = trim($_POST["mcq{$i}_optionC"]);
        $d = trim($_POST["mcq{$i}_optionD"]);
        $ans = $_POST["mcq{$i}_answer"];

        $quizStmt = $pdo->prepare("INSERT INTO lesson_quizzes (lesson_id, question, option_a, option_b, option_c, option_d, correct_option, type)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'mcq')");
        $quizStmt->execute([$lesson_id, $q, $a, $b, $c, $d, $ans]);
    }

    // Insert theory question
    $theory = trim($_POST['theory_question']);
    if (!empty($theory)) {
        $theoryStmt = $pdo->prepare("INSERT INTO lesson_quizzes (lesson_id, question, type) VALUES (?, ?, 'theory')");
        $theoryStmt->execute([$lesson_id, $theory]);
    }

    echo "<script>alert('✅ Lesson and quiz created successfully!'); window.location.href = 'courses.php';</script>";
}
?>
