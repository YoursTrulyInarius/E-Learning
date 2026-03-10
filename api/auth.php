<?php
require_once '../config.php';

// Allow json requests
$inputJSON = file_get_contents('php://input');
if(!empty($inputJSON)) {
    $_POST = json_decode($inputJSON, TRUE);
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'login_teacher') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'teacher'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true, 'redirect' => 'teacher/dashboard.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid teacher username or password.']);
    }
    exit;
} elseif ($action === 'login_student') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'student'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['grade_level'] = $user['grade_level'];
        
        $redirect = 'student/dashboard.php';
        if ($user['grade_level'] === 'K') $redirect = 'student/K/kinder.php';
        else if (in_array($user['grade_level'], ['1','2','3','4','5','6'])) {
            $redirect = "student/{$user['grade_level']}/grade{$user['grade_level']}.php";
        }
        
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid student username or password.']);
    }
    exit;
} elseif ($action === 'register_student') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $grade_level = $_POST['grade_level'] ?? '';
    
    if (empty($username) || empty($password) || empty($grade_level)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken.']);
        exit;
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, grade_level) VALUES (?, ?, 'student', ?)");
    if ($stmt->execute([$username, $password_hash, $grade_level])) {
        // Automatically log them in
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'student';
        $_SESSION['username'] = $username;
        $_SESSION['grade_level'] = $grade_level;
        
        $redirect = 'student/dashboard.php';
        if ($grade_level === 'K') $redirect = 'student/K/kinder.php';
        else if (in_array($grade_level, ['1','2','3','4','5','6'])) {
            $redirect = "student/{$grade_level}/grade{$grade_level}.php";
        }
        
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed.']);
    }
    exit;
} elseif ($action === 'register_teacher') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $grade_level = $_POST['grade_level'] ?? ''; // Which grade they teach
    
    if (empty($username) || empty($password) || empty($grade_level)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken.']);
        exit;
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, grade_level) VALUES (?, ?, 'teacher', ?)");
    if ($stmt->execute([$username, $password_hash, $grade_level])) {
        // Automatically log them in
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'teacher';
        $_SESSION['username'] = $username;
        $_SESSION['grade_level'] = $grade_level;
        
        echo json_encode(['success' => true, 'redirect' => 'teacher/dashboard.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed.']);
    }
    exit;
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => 'index.php']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
