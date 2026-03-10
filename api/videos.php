<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'list') {
    // List videos for teacher dashboard
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Teacher access required.']);
        exit;
    }
    
    $stmt = $pdo->query("SELECT * FROM videos ORDER BY upload_date DESC");
    $videos = $stmt->fetchAll();
    
    // Add grades and subjects for dashboard view
    foreach ($videos as &$video) {
        $stmt_grades = $pdo->prepare("SELECT grade_level FROM video_grades WHERE video_id = ?");
        $stmt_grades->execute([$video['id']]);
        $video['grades'] = array_column($stmt_grades->fetchAll(), 'grade_level');
        
        $stmt_subjects = $pdo->prepare("
            SELECT s.name FROM subjects s
            JOIN video_subjects vs ON s.id = vs.subject_id
            WHERE vs.video_id = ?
        ");
        $stmt_subjects->execute([$video['id']]);
        $video['subjects'] = array_column($stmt_subjects->fetchAll(), 'name');
    }
    echo json_encode(['success' => true, 'data' => $videos]);
    exit;
}

if ($action === 'list_student') {
    // List videos for specific student's grade
    if (!isStudent()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $grade_level = getGradeLevel();
    $subject_id = $_GET['subject_id'] ?? null;
    $search = $_GET['search'] ?? null;
    
    $query = "
        SELECT DISTINCT v.* FROM videos v
        JOIN video_grades vg ON v.id = vg.video_id
    ";
    
    $params = [];
    if ($subject_id) {
        $query .= " JOIN video_subjects vs ON v.id = vs.video_id";
    }
    
    $query .= " WHERE vg.grade_level = ?";
    $params[] = $grade_level;
    
    if ($subject_id) {
        $query .= " AND vs.subject_id = ?";
        $params[] = $subject_id;
    }
    
    if ($search && !empty(trim($search))) {
        $query .= " AND (v.title LIKE ? OR v.description LIKE ?)";
        $params[] = '%' . trim($search) . '%';
        $params[] = '%' . trim($search) . '%';
    }
    
    $query .= " ORDER BY v.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $videos = $stmt->fetchAll();
    
    // Add subjects for each video
    foreach ($videos as &$video) {
        $stmt_subjects = $pdo->prepare("
            SELECT s.name FROM subjects s
            JOIN video_subjects vs ON s.id = vs.subject_id
            WHERE vs.video_id = ?
        ");
        $stmt_subjects->execute([$video['id']]);
        $video['subjects'] = array_column($stmt_subjects->fetchAll(), 'name');
    }
    
    echo json_encode(['success' => true, 'data' => $videos]);
    exit;
}

if ($action === 'delete') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $video_id = $_POST['video_id'] ?? null;
    if ($video_id) {
        $stmt = $pdo->prepare("SELECT file_path, thumbnail_path FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $video = $stmt->fetch();
        if ($video) {
            // Delete actual files
            $fullPath = __DIR__ . '/../' . ltrim($video['file_path'], '/');
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            if ($video['thumbnail_path']) {
                $fullThumbPath = __DIR__ . '/../' . ltrim($video['thumbnail_path'], '/');
                if (file_exists($fullThumbPath)) {
                    unlink($fullThumbPath);
                }
            }
            // Delete from DB
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$video_id]);
            echo json_encode(['success' => true, 'message' => 'Video deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Video not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing video ID']);
    }
    exit;
}

if ($action === 'subjects') {
    $grade = getGradeLevel(); // Works for both students and teachers
    if ($grade) {
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE grade_level = ? ORDER BY name ASC");
        $stmt->execute([$grade]);
    } else {
        $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name ASC");
    }
    $subjects = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $subjects]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
