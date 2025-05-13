<?php
// Include database connection
require 'db.php';

// Basic input validation
if (!isset($_GET['lesson_id']) || !is_numeric($_GET['lesson_id'])) {
    http_response_code(400);
    exit('Invalid lesson ID specified.');
}

try {
    // Clean any previous output
    if (ob_get_level()) ob_end_clean();
    
    $lesson_id = (int)$_GET['lesson_id'];
    
    // Fetch the file data
    $stmt = $pdo->prepare("SELECT file_attachment, file_name, file_mime_type FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row || empty($row['file_attachment'])) {
        http_response_code(404);
        exit('No file found for this lesson.');
    }
    
    // Get file size using mb_strlen for binary data
    $fileSize = function_exists('mb_strlen') 
        ? mb_strlen($row['file_attachment'], '8bit')
        : strlen($row['file_attachment']);
    
    // Set proper MIME type if available, otherwise use safe default
    $mime_type = !empty($row['file_mime_type']) ? $row['file_mime_type'] : 'application/octet-stream';
    
    // Set filename if available, otherwise generate one
    $filename = !empty($row['file_name']) ? $row['file_name'] : "lesson_file_{$lesson_id}.bin";
    
    // Force the download to happen
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Pragma: public');
    header('Expires: 0');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . $fileSize);
    
    // Disable output buffering completely
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output the file data directly
    echo $row['file_attachment'];
    exit;
} catch (Exception $e) {
    // Log the error
    error_log("Error in lesson file download: " . $e->getMessage());
    
    http_response_code(500);
    exit('An error occurred while retrieving the file.');
}
?>