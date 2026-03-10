<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'list') {
    // List lessons for teacher or student
    $grade = $_GET['grade'] ?? $_SESSION['grade_level'];
    $subject_id = $_GET['subject_id'] ?? null;
    
    $query = "SELECT l.*, s.name as subject_name, u.username as teacher_name 
              FROM lessons l
              JOIN subjects s ON l.subject_id = s.id
              JOIN users u ON l.teacher_id = u.id
              WHERE l.grade_level = ?";
    $params = [$grade];
    
    if ($subject_id) {
        $query .= " AND l.subject_id = ?";
        $params[] = $subject_id;
    }
    
    $query .= " ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $lessons = $stmt->fetchAll();
    
    // Attach activities and questions to each lesson
    foreach ($lessons as &$lesson) {
        $stmt_act = $pdo->prepare("SELECT * FROM activities WHERE lesson_id = ?");
        $stmt_act->execute([$lesson['id']]);
        $activities = $stmt_act->fetchAll();
        
        foreach ($activities as &$act) {
            $stmt_q = $pdo->prepare("SELECT * FROM activity_questions WHERE activity_id = ?");
            $stmt_q->execute([$act['id']]);
            $act['questions'] = $stmt_q->fetchAll();
        }
        
        $lesson['activities'] = $activities;
    }
    
    echo json_encode(['success' => true, 'data' => $lessons]);
}

elseif ($action === 'create' && isTeacher()) {
    $subject_id = $_POST['subject_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $grade = $_POST['grade_level'] ?? $_SESSION['grade_level'];
    
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO lessons (teacher_id, subject_id, grade_level, title, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $subject_id, $grade, $title, $content]);
        $lesson_id = $pdo->lastInsertId();
        
        // If activities were sent, add them
        if (isset($_POST['activities']) && is_array($_POST['activities'])) {
            $stmt_act = $pdo->prepare("INSERT INTO activities (lesson_id, title, description, points_reward, activity_type) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['activities'] as $act) {
                $stmt_act->execute([
                    $lesson_id, 
                    $act['title'], 
                    $act['description'] ?? '', 
                    $act['points'] ?? 10, 
                    $act['type'] ?? 'quiz'
                ]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Lesson created successfully', 'id' => $lesson_id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

elseif ($action === 'delete' && isTeacher()) {
    $lesson_id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$lesson_id, $_SESSION['user_id']]);
    echo json_encode(['success' => true, 'message' => 'Lesson deleted']);
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action or insufficient permissions']);
}
