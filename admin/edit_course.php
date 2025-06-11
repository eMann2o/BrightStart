<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    die("Course ID not provided.");
}

$id = intval($_GET['id']);
$message = "";
$success = false;

// Fetch course details
$stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found.");
}

$module_id = $course['id']; // To redirect back to the module page

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['content']);

    if ($title && $description) {
        $stmt = $pdo->prepare("UPDATE modules SET title = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$title, $description, $id])) {
            $success = true;
            $message = "Course updated successfully.";
        } else {
            $message = "Failed to update course.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<?php
include_once '../database.php';//include database connection file 
require 'db.php'; 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../logo.PNG" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <meta charset="UTF-8">
    <title>Edit Course</title>
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
                <h2>Edit Course</h2>

                <form method="POST">
                    <label for="title">Course Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>

                    <label for="content">Course Description</label>
                    <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($course['description']) ?></textarea>

                    <button type="submit">Update Course</button>
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
                            window.location.href = "courses.php";
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
