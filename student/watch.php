<?php
require_once '../config.php';

if (!isStudent()) {
    redirect('../index.php');
}

$videoId = $_GET['id'] ?? null;
if (!$videoId) redirect('dashboard.php'); // Or index.php

$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$stmt->execute([$videoId]);
$video = $stmt->fetch();

if (!$video) {
    die("Video not found!");
}

$username = $_SESSION['username'];
$grade = $_SESSION['grade_level'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watching: <?php echo htmlspecialchars($video['title']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="student-page">
    <header class="student-header">
        <div class="header-left">
            <button onclick="history.back()" class="btn-back" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer; margin-right:20px;">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="student-welcome">
                <h1>Watching Video</h1>
                <p><?php echo htmlspecialchars($video['title']); ?></p>
            </div>
        </div>
        <div class="header-right">
            <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </header>

    <main class="dashboard-body" style="padding: 40px;">
        <div class="glass-panel" style="max-width: 1000px; margin: 0 auto; padding: 0; overflow: hidden;">
            <div class="video-container" style="background: #000; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center;">
                <video controls style="width: 100%; height: 100%;" poster="<?php echo $video['thumbnail_path'] ? '../E-Learning'.$video['thumbnail_path'] : '../assets/images/default-thumb.png'; ?>">
                    <source src="<?php echo '../E-Learning'.$video['file_path']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div style="padding: 30px;">
                <h2 style="font-size: 2rem; margin-bottom: 10px;"><?php echo htmlspecialchars($video['title']); ?></h2>
                <p style="font-size: 1.1rem; color: var(--text-light); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
