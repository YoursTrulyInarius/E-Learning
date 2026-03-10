<?php
require_once '../../config.php';

if (!isStudent() || $_SESSION['grade_level'] !== '1') {
    redirect('../../index.php');
}

$grade = $_SESSION['grade_level'];
$username = $_SESSION['username'];
$ui_mode = 'K-2';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Dashboard - Grade 1</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="student-page grade-1 ui-mode-K-2">
    
    <header class="student-header">
        <div class="header-left">
            <div class="student-avatar pulse-anim"><i class="fas fa-child"></i></div>
            <div class="student-welcome">
                <h1>Hi, <?php echo htmlspecialchars($username); ?>!</h1>
                <p>Welcome to Grade 1</p>
            </div>
        </div>
        <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
            <div class="points-badge pulse-anim">
                <i class="fas fa-coins"></i> <span id="student-points"><?php echo $_SESSION['points'] ?? 0; ?></span> Points
            </div>
            <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </header>

    <main class="dashboard-body">
        <section id="main-dashboard-content">
            <section class="discovery-section">
                <div class="section-title">
                    <i class="fas fa-lightbulb text-complement"></i>
                    <h2>Knowledge Explorer</h2>
                </div>
                <div class="discovery-grid" id="discovery-grid">
                    <div class="loading-card"><i class="fas fa-spinner fa-spin"></i> Finding something cool...</div>
                </div>
            </section>

            <section class="subjects-section" style="margin-bottom: 50px;">
                <div class="section-title">
                    <i class="fas fa-book text-secondary"></i>
                    <h2>What do you want to learn today?</h2>
                </div>
                <div class="subject-discovery-grid" id="subject-discovery-grid"></div>
            </section>

            <section class="video-section">
                <div class="section-title">
                    <i class="fas fa-play-circle text-primary"></i>
                    <h2>Educational Videos</h2>
                </div>
                <div class="video-grid" id="video-grid"></div>
            </section>
        </section>

        <!-- Subject View -->
        <section id="subject-view" class="hidden fade-in-up">
            <div class="subject-view-header">
                <button class="btn-back" onclick="closeSubject()"><i class="fas fa-arrow-left"></i> Back to Dashboard</button>
                <div id="subject-title-area">
                    <h2 id="current-subject-title" style="margin: 0; font-size: 2rem;">Subject Name</h2>
                    <p id="current-subject-subtitle" style="color: var(--text-light); margin: 0; font-weight: 700;">Explore your lessons</p>
                </div>
            </div>
            <div class="stats-row" id="lessons-grid"></div>
        </section>
    </main>

    <!-- Quiz/Lesson Modals would go here, same as kinder but with G1 theme -->
    <div id="lesson-detail-modal" class="modal-overlay">
        <div class="modal-content glass-panel" style="max-width: 800px; padding: 0;">
            <div id="lesson-header" style="padding: 30px; background: var(--primary); color: white; position: relative;">
                <button class="btn-icon" style="position: absolute; right: 20px; top: 20px; background: rgba(255,255,255,0.2); color: white;" onclick="closeModal('lesson-detail-modal')"><i class="fas fa-times"></i></button>
                <h2 id="lesson-modal-title" style="font-size: 2rem; margin: 0;">Lesson Title</h2>
            </div>
            <div id="lesson-modal-body" style="padding: 40px; font-size: 1.2rem; line-height: 1.8;"></div>
            <div id="lesson-footer" style="padding: 25px 40px; background: #F8FAFC; display: flex; justify-content: space-between;">
                <button class="btn btn-outline" onclick="downloadLessonPDF()">Save PDF</button>
                <div id="activity-start-btn-container"></div>
            </div>
        </div>
    </div>

    <!-- Quiz Modal -->
    <div id="quiz-modal" class="modal-overlay">
        <div class="modal-content glass-panel" style="max-width: 600px; padding: 40px;">
            <h2 id="quiz-title">Quiz Time!</h2>
            <div id="quiz-body">
                <div id="question-container" style="padding: 20px; background: #f8fafc; border-radius: 10px; margin: 20px 0;">
                    <h3 id="question-text"></h3>
                    <div id="options-grid" style="display: grid; gap: 10px; margin-top: 20px;"></div>
                </div>
            </div>
            <div id="quiz-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <span id="quiz-progress">Question 1 of X</span>
                <button id="next-q-btn" class="btn btn-primary" disabled>Next →</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboard();
        });

        async function loadDashboard() {
            const pointsRes = await apiRequest(`/E-Learning/api/points.php?action=get_points`);
            if (pointsRes.success) document.getElementById('student-points').textContent = pointsRes.points;

            const subjectRes = await apiRequest(`/E-Learning/api/videos.php?action=subjects`);
            if (subjectRes.success) renderSubjectGrid(subjectRes.data);

            const videoRes = await apiRequest(`/E-Learning/api/videos.php?action=list_student`);
            if (videoRes.success) renderVideos(videoRes.data);

            loadDiscoveryContent('1');
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
            grid.innerHTML = '<div class="loading-card">Loading lessons...</div>';
            const res = await apiRequest(`/E-Learning/api/lessons.php?action=list&subject_id=${id}`);
            if (res.success) {
                currentLessonData = res.data;
                grid.innerHTML = '';
                res.data.forEach(l => {
                    const card = document.createElement('div');
                    card.className = 'stat-card glass-panel bounce-hover';
                    card.innerHTML = `<h3>${escapeHtml(l.title)}</h3><p class="text-sm">${escapeHtml(l.content.substring(0, 80))}...</p>`;
                    card.onclick = () => openLessonDetail(l.id);
                    grid.appendChild(card);
                });
            }
        }

        function closeSubject() {
            document.getElementById('subject-view').classList.add('hidden');
            document.getElementById('main-dashboard-content').classList.remove('hidden');
        }

        let currentLessonData = [];
        let activeQuiz = null;
        let currentQuestionIndex = 0;
        let quizScore = 0;

        function openLessonDetail(id) {
            const lesson = currentLessonData.find(l => l.id == id);
            document.getElementById('lesson-modal-title').textContent = lesson.title;
            document.getElementById('lesson-modal-body').innerHTML = lesson.content.split('\n').map(p => `<p>${escapeHtml(p)}</p>`).join('');
            const container = document.getElementById('activity-start-btn-container');
            container.innerHTML = '';
            if (lesson.activities && lesson.activities.length > 0) {
                const act = lesson.activities[0];
                const btn = document.createElement('button');
                btn.className = 'btn btn-primary';
                btn.textContent = 'Take Quiz';
                btn.onclick = () => { closeModal('lesson-detail-modal'); openQuiz(act); };
                container.appendChild(btn);
            }
            openModal('lesson-detail-modal');
        }

        function openQuiz(activity) {
            activeQuiz = activity;
            currentQuestionIndex = 0;
            quizScore = 0;
            showQuestion();
            openModal('quiz-modal');
        }

        function showQuestion() {
            const q = activeQuiz.questions[currentQuestionIndex];
            document.getElementById('question-text').textContent = q.question_text;
            document.getElementById('quiz-progress').textContent = `Question ${currentQuestionIndex + 1} of ${activeQuiz.questions.length}`;
            const grid = document.getElementById('options-grid');
            grid.innerHTML = '';
            ['A', 'B', 'C', 'D'].forEach(opt => {
                const val = q[`option_${opt.toLowerCase()}`];
                if (val) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline';
                    btn.textContent = `${opt}: ${val}`;
                    btn.onclick = () => {
                        document.querySelectorAll('#options-grid button').forEach(b => b.classList.replace('btn-primary', 'btn-outline'));
                        btn.classList.replace('btn-outline', 'btn-primary');
                        selectedAnswer = opt;
                        document.getElementById('next-q-btn').disabled = false;
                    };
                    grid.appendChild(btn);
                }
            });
            document.getElementById('next-q-btn').disabled = true;
            document.getElementById('next-q-btn').textContent = (currentQuestionIndex === activeQuiz.questions.length - 1) ? 'Finish' : 'Next →';
        }

        let selectedAnswer = null;
        async function handleNextQuestion() {
            const q = activeQuiz.questions[currentQuestionIndex];
            if (selectedAnswer === q.correct_answer) quizScore++;
            if (currentQuestionIndex < activeQuiz.questions.length - 1) {
                currentQuestionIndex++;
                showQuestion();
            } else {
                finishQuiz();
            }
        }
        document.getElementById('next-q-btn').onclick = handleNextQuestion;

        async function finishQuiz() {
            const points = Math.round((quizScore / activeQuiz.questions.length) * activeQuiz.points_reward);
            document.getElementById('quiz-body').innerHTML = `<div style="text-align:center; padding:30px;"><h2>Amazing Job!</h2><p>Score: ${quizScore}/${activeQuiz.questions.length}</p><h3>+${points} Points!</h3></div>`;
            document.getElementById('next-q-btn').style.display = 'none';
            const btn = document.createElement('button');
            btn.className = 'btn btn-primary';
            btn.textContent = 'Close';
            btn.style.margin = '0 auto';
            btn.onclick = () => { closeModal('quiz-modal'); location.reload(); };
            document.getElementById('quiz-footer').innerHTML = '';
            document.getElementById('quiz-footer').appendChild(btn);
            if (points > 0) await completeActivity(activeQuiz.id, points);
        }

        async function completeActivity(id, points) {
            const data = new FormData();
            data.append('action', 'submit_activity');
            data.append('activity_id', id);
            data.append('points', points);
            await apiRequest('/E-Learning/api/points.php', 'POST', data);
        }

        function renderVideos(videos) {
            const grid = document.getElementById('video-grid');
            grid.innerHTML = '';
            videos.forEach(v => {
                const card = document.createElement('div');
                card.className = 'video-card bounce-hover';
                card.innerHTML = `<div class="video-preview" style="background-image: url('${v.thumbnail_path ? '/E-Learning'+v.thumbnail_path : '../../assets/images/default-thumb.png'}')" onclick="window.location.href='../watch.php?id=${v.id}'"><div class="play-overlay"><i class="fas fa-play"></i></div></div><div class="video-info"><h3>${escapeHtml(v.title)}</h3></div>`;
                grid.appendChild(card);
            });
        }

        async function loadDiscoveryContent(grade) {
            const grid = document.getElementById('discovery-grid');
            grid.innerHTML = `<div class="discovery-card" style="background: var(--primary); color: white; grid-column: 1/-1; padding: 30px; border-radius: 15px;"><h3>Daily Challenge!</h3><p>What is 1 + 1?</p><button class="btn btn-secondary" onclick="this.textContent = 'The answer is 2!'">Solve It!</button></div>`;
        }

        function escapeHtml(unsafe) {
            return (unsafe || '').toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        }
    </script>
</body>
</html>
