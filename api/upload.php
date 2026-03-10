<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isTeacher()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

$teacher_grade = $_SESSION['grade_level'] ?? null;
$subjects = $_POST['subjects'] ?? [];
if (!is_array($subjects)) {
    $subjects = json_decode($subjects, true) ?? [];
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (!$teacher_grade) {
    echo json_encode(['success' => false, 'message' => 'Teacher grade level not found. Please log in again.']);
    exit;
}

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Video file is required or upload failed']);
    exit;
}

// Allow robust checking of mime types
$allowedMimeTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'application/octet-stream'];
$fileType = mime_content_type($_FILES['video']['tmp_name']);

// Relax mime check for octet-stream by checking extension
$ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
$allowedExts = ['mp4', 'mov', 'avi', 'mkv'];

if (!in_array($ext, $allowedExts)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only MP4, MOV, AVI, and MKV are allowed.']);
    exit;
}

$maxSize = 500 * 1024 * 1024; // 500MB
$fileSize = $_FILES['video']['size'];
if ($fileSize > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the 500MB limit.']);
    exit;
}

// Generate unique filename
$filename = uniqid('vid_', true) . '.' . $ext;
// Relative path to save in DB
$relativePath = '/uploads/videos/' . $filename;
// Absolute path
$destination = __DIR__ . '/..' . $relativePath;

if (!move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded video file']);
    exit;
}

// Handle optional thumbnail
$thumbnailPath = null;
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $thumbExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    $thumbFilename = uniqid('thumb_', true) . '.' . $thumbExt;
    $thumbRelativePath = '/uploads/thumbnails/' . $thumbFilename;
    $thumbDestination = __DIR__ . '/..' . $thumbRelativePath;
    
    // Ensure dir exists
    $thumbDir = dirname($thumbDestination);
    if (!file_exists($thumbDir)) {
        mkdir($thumbDir, 0777, true);
    }
    
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbDestination)) {
        $thumbnailPath = $thumbRelativePath;
    }
}

// Transaction
try {
    $pdo->beginTransaction();
    
    // Insert Video
    $stmt = $pdo->prepare("INSERT INTO videos (title, description, file_path, thumbnail_path, file_size, uploader_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $relativePath, $thumbnailPath, $fileSize, $_SESSION['user_id']]);
    $videoId = $pdo->lastInsertId();
    
    // Insert Grade (Automatic)
    $stmtGrade = $pdo->prepare("INSERT INTO video_grades (video_id, grade_level) VALUES (?, ?)");
    $stmtGrade->execute([$videoId, $teacher_grade]);
    
    // Insert Subjects
    if (!empty($subjects)) {
        $stmtSubject = $pdo->prepare("INSERT INTO video_subjects (video_id, subject_id) VALUES (?, ?)");
        foreach ($subjects as $subject) {
            $stmtSubject->execute([$videoId, $subject]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Video uploaded successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    // Delete file if db fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    if ($thumbnailPath && file_exists(__DIR__ . '/..' . $thumbnailPath)) {
        unlink(__DIR__ . '/..' . $thumbnailPath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
