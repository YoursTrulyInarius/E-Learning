<?php
require_once '../config.php';

// Allow json requests
$inputJSON = file_get_contents('php://input');
if(!empty($inputJSON)) {
    $_POST = json_decode($inputJSON, TRUE);
}

header('Content-Type: application/json');

if (!isTeacher()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$teacher_id = $_SESSION['user_id'];
$teacher_grade = $_SESSION['grade_level'];

if ($action === 'roster') {
    try {
        // Fetch all students in this teacher's specialized grade
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'student' AND grade_level = ? ORDER BY username ASC");
        $stmt->execute([$teacher_grade]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch today's attendance for these students
        $today = date('Y-m-d');
        $stmtStatus = $pdo->prepare("SELECT student_id, status FROM attendance WHERE teacher_id = ? AND date = ?");
        $stmtStatus->execute([$teacher_id, $today]);
        $dailyRecords = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR); // student_id => status

        // Map records to roster
        foreach ($students as &$student) {
            $student['today_status'] = $dailyRecords[$student['id']] ?? null;
            
            // Get stats for this student
            $stmtStats = $pdo->prepare("
                SELECT 
                    COUNT(IF(status = 'present', 1, NULL)) as present_count,
                    COUNT(IF(status = 'absent', 1, NULL)) as absent_count
                FROM attendance 
                WHERE student_id = ?
            ");
            $stmtStats->execute([$student['id']]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            $student['stats'] = $stats;
        }

        echo json_encode(['success' => true, 'data' => $students, 'grade' => $teacher_grade, 'date' => $today]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
} elseif ($action === 'save') {
    $attendanceData = $_POST['attendance'] ?? []; // Array of [student_id => status]
    $date = date('Y-m-d');

    if (empty($attendanceData)) {
        echo json_encode(['success' => false, 'message' => 'No attendance data provided.']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, teacher_id, date, status) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");

        foreach ($attendanceData as $student_id => $status) {
            $stmt->execute([$student_id, $teacher_id, $date, $status]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Attendance saved successfully.']);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}
