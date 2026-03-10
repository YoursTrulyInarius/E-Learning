<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'submit_activity') {
    if (!isStudent()) {
        echo json_encode(['success' => false, 'message' => 'Only students can submit activities']);
        exit;
    }
    
    $student_id = $_SESSION['user_id'];
    $activity_id = $_POST['activity_id'];
    $points_earned = (int)($_POST['points'] ?? 10);
    
    try {
        $pdo->beginTransaction();
        
        // Record submission
        $stmt = $pdo->prepare("INSERT INTO activity_submissions (student_id, activity_id, points_earned) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $activity_id, $points_earned]);
        
        // Update user's total points
        $stmt_user = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt_user->execute([$points_earned, $student_id]);
        
        // Fetch new total
        $stmt_get = $pdo->prepare("SELECT points FROM users WHERE id = ?");
        $stmt_get->execute([$student_id]);
        $new_total = $stmt_get->fetchColumn();
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Points awarded!', 'points_earned' => $points_earned, 'total_points' => $new_total]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

elseif ($action === 'get_points') {
    $user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $points = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'points' => (int)$points]);
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
