<?php
require_once '../../config.php';
$g = '6';
if (!isStudent() || $_SESSION['grade_level'] !== $g) redirect('../../index.php');
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Dashboard - Grade <?php echo $g; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="student-page grade-<?php echo $g; ?> ui-mode-5-6">
    <header class="student-header">
        <div class="header-left">
            <div class="student-avatar"><i class="fas fa-child"></i></div>
            <div class="student-welcome"><h1>Hi, <?php echo htmlspecialchars($username); ?>!</h1><p>Welcome to Grade <?php echo $g; ?></p></div>
        </div>
        <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
            <div class="points-badge"><i class="fas fa-coins"></i> <span id="student-points"><?php echo $_SESSION['points'] ?? 0; ?></span> Points</div>
            <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </header>
    <main class="dashboard-body">
        <section id="main-dashboard-content">
            <section class="subjects-section"><div class="section-title"><i class="fas fa-book text-secondary"></i><h2>Grade <?php echo $g; ?> Subjects</h2></div><div class="subject-discovery-grid" id="subject-discovery-grid"></div></section>
        </section>
        <section id="subject-view" class="hidden fade-in-up">
            <div class="subject-view-header"><button class="btn-back" onclick="closeSubject()">← Back</button><h2 id="current-subject-title"></h2></div>
            <div class="stats-row" id="lessons-grid"></div>
        </section>
    </main>
    <script src="../../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => { loadDashboard(); });
        async function loadDashboard() {
            const pointsRes = await apiRequest(`/E-Learning/api/points.php?action=get_points`);
            if (pointsRes.success) document.getElementById('student-points').textContent = pointsRes.points;
            const subjectRes = await apiRequest(`/E-Learning/api/videos.php?action=subjects`);
            if (subjectRes.success) renderSubjectGrid(subjectRes.data);
        }
        function renderSubjectGrid(subjects) {
            const grid = document.getElementById('subject-discovery-grid');
            grid.innerHTML = '';
            subjects.forEach(s => {
                const card = document.createElement('div');
                card.className = `subject-card fade-in-up`;
                card.innerHTML = `<i class="${s.icon}"></i><h3>${s.name}</h3>`;
                card.onclick = () => openSubject(s.id, s.name);
                grid.appendChild(card);
            });
        }
        async function openSubject(id, name) {
            document.getElementById('main-dashboard-content').classList.add('hidden');
            document.getElementById('subject-view').classList.remove('hidden');
            document.getElementById('current-subject-title').textContent = name;
            const grid = document.getElementById('lessons-grid');
            const res = await apiRequest(`/E-Learning/api/lessons.php?action=list&subject_id=${id}`);
            if (res.success) {
                grid.innerHTML = '';
                res.data.forEach(l => {
                    const card = document.createElement('div');
                    card.className = 'stat-card glass-panel bounce-hover';
                    card.innerHTML = `<h3>${l.title}</h3>`;
                    grid.appendChild(card);
                });
            }
        }
        function closeSubject() {
            document.getElementById('subject-view').classList.add('hidden');
            document.getElementById('main-dashboard-content').classList.remove('hidden');
        }
    </script>
</body>
</html>
