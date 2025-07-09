<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_FILES['photos']) || empty($_FILES['photos']['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'No photos selected']);
    exit;
}

include_once '../database.php'; // Include database connection file
$host = $host;
$dbname = $dbname;
$username = $username_db;
$password = $epassword;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Create tables if they don't exist
$createTables = "
CREATE TABLE IF NOT EXISTS albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    album_id INT,
    title VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES albums(id)
);
";

try {
    $pdo->exec($createTables);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

// Validate album option
if (empty($_POST['album_option'])) {
    echo json_encode(['success' => false, 'message' => 'Album option is required']);
    exit;
}

$albumOption = $_POST['album_option'];
$albumId = null;
$albumName = '';

if ($albumOption === 'new') {
    if (empty($_POST['album_name']) || trim($_POST['album_name']) === '') {
        echo json_encode(['success' => false, 'message' => 'New album name is required']);
        exit;
    }
    $albumName = trim($_POST['album_name']);
} elseif ($albumOption === 'existing') {
    if (empty($_POST['album_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please select an existing album']);
        exit;
    }
    $albumId = (int)$_POST['album_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid album option']);
    exit;
}

// Validate photo title
$photoTitle = trim($_POST['photo_title'] ?? '');
if ($photoTitle === '') {
    echo json_encode(['success' => false, 'message' => 'Photo title is required']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
    exit;
}

try {
    // Handle album creation or verification
    if ($albumOption === 'new') {
        $stmt = $pdo->prepare("INSERT INTO albums (name) VALUES (?)");
        try {
            $stmt->execute([$albumName]);
            $albumId = $pdo->lastInsertId();
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $stmt = $pdo->prepare("SELECT id FROM albums WHERE name = ?");
                $stmt->execute([$albumName]);
                $albumId = $stmt->fetchColumn();
                if (!$albumId) {
                    echo json_encode(['success' => false, 'message' => 'Album name already exists but could not retrieve ID']);
                    exit;
                }
            } else {
                throw $e;
            }
        }
    } else {
        // Verify existing album exists
        $stmt = $pdo->prepare("SELECT name FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $albumName = $stmt->fetchColumn();
        
        if (!$albumName) {
            echo json_encode(['success' => false, 'message' => 'Selected album does not exist']);
            exit;
        }
    }

    $uploadedCount = 0;
    $errors = [];

    // Allowed MIME types and extensions
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Process each uploaded file
    foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading " . $_FILES['photos']['name'][$key];
            continue;
        }

        $originalName = $_FILES['photos']['name'][$key];
        $fileSize = $_FILES['photos']['size'][$key];
        $fileType = $_FILES['photos']['type'][$key];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate type and extension
        if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Invalid file type for {$originalName}";
            continue;
        }

        // Validate file size (5MB max)
        if ($fileSize > 5 * 1024 * 1024) {
            $errors[] = "File too large: {$originalName}";
            continue;
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            $stmt = $pdo->prepare("INSERT INTO photos (filename, original_name, album_id, title) VALUES (?, ?, ?, ?)");
            $stmt->execute([$filename, $originalName, $albumId, $photoTitle]);
            $uploadedCount++;
        } else {
            $errors[] = "Failed to move file: {$originalName}";
        }
    }

    if ($uploadedCount > 0) {
        $message = "Successfully uploaded {$uploadedCount} photo(s) to '{$albumName}' album!";
        if (!empty($errors)) {
            $message .= " Some errors: " . implode('; ', $errors);
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No photos were uploaded. ' . implode('; ', $errors)]);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
}
?>
