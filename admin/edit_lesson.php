<?php
session_start();
require 'db.php';

if (!isset($_GET['lesson_id'])) {
    die("Lesson ID not provided.");
}

$lesson_id = intval($_GET['lesson_id']);
$message = "";
$success = false;

// Fetch lesson details
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    die("Lesson not found.");
}

$course_id = $lesson['course_id']; // assuming each lesson has a `course_id` column

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title && $content) {
        $stmt = $pdo->prepare("UPDATE lessons SET title = ?, content = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $lesson_id])) {
            $success = true;
            $message = "Lesson updated successfully.";
        } else {
            $message = "Failed to update lesson.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <meta charset="UTF-8">
    <title>Edit Lesson</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .bodys { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; }
        form { display: flex; flex-direction: column; }
        label { margin-top: 10px; font-weight: bold; }
        input[type="text"], textarea {
            padding: 10px; font-size: 16px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            margin-top: 20px; padding: 10px;
            background-color: #007bff; color: white;
            border: none; border-radius: 5px;
            cursor: pointer;
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

        <div class="container">
            <div class="bodys">
                <h2>Edit Lesson</h2>

                <form method="POST">
                    <label for="title">Lesson Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" required>

                    <label for="content">Lesson Content</label>
                    <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($lesson['content']) ?></textarea>

                    <button type="submit">Update Lesson</button>
                </form>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <script>
                        Swal.fire({
                            icon: <?= $success ? "'success'" : "'error'" ?>,
                            title: <?= $success ? "'Updated!'" : "'Update Failed'" ?>,
                            text: <?= json_encode($message) ?>,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            <?php if ($success): ?>
                            window.location.href = "view_lessons.php?course_id=<?= $course_id ?>";
                            <?php endif; ?>
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>

        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });

        </script>
    </div>
</body>
</html>
