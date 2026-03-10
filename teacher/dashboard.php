<?php
require_once '../config.php';

if (!isTeacher()) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LittleLearners Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="teacher-page grade-theme-<?php echo $_SESSION['grade_level']; ?>">
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i> LittleLearners
            </a>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" onclick="switchTab('library', this)"><i class="fas fa-video"></i> Video Library</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="switchTab('attendance', this)"><i class="fas fa-calendar-check text-success"></i> Attendance</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="switchTab('lessons', this)"><i class="fas fa-chalkboard-teacher text-complement"></i> Lessons & Activities</a>
                </li>
                <li class="nav-item">
                    <a href="upload.php" class="nav-link"><i class="fas fa-cloud-upload-alt"></i> Upload Content</a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <button onclick="logout()" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h2 id="page-title">Video Library</h2>
                <div class="user-profile">
                    <span>Welcome, Teacher <?php echo htmlspecialchars($_SESSION['username']); ?>! 
                    <span class="badge badge-grade grade-<?php echo $_SESSION['grade_level']; ?>" style="margin-left: 10px;"><?php echo $_SESSION['grade_level']; ?> Specialist</span></span>
                    <div class="avatar"><i class="fas fa-user"></i></div>
                </div>
            </header>

            <div id="library" class="tab-content">
                <div class="stats-row">
                    <div class="stat-card glass-panel bounce-hover">
                        <div class="stat-icon" style="background: var(--gr1-color);"><i class="fas fa-film"></i></div>
                        <div class="stat-info">
                            <h3>Total Videos</h3>
                            <p id="total-videos">0</p>
                        </div>
                    </div>
                    <div class="stat-card glass-panel bounce-hover">
                        <div class="stat-icon" style="background: var(--gr3-color);"><i class="fas fa-hdd"></i></div>
                        <div class="stat-info">
                            <h3>Storage Used</h3>
                            <p id="total-storage">0 MB</p>
                        </div>
                    </div>
                    <div class="stat-card glass-panel bounce-hover" onclick="window.location.href='upload.php'" style="cursor: pointer;">
                        <div class="stat-icon" style="background: var(--gr5-color);"><i class="fas fa-plus"></i></div>
                        <div class="stat-info">
                            <h3>Quick Action</h3>
                            <p>Upload New Video</p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h3>All Uploaded Content</h3>
                    </div>

                    <div class="table-responsive glass-panel">
                        <table class="video-table">
                            <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Title</th>
                                    <th>Grades</th>
                                    <th>Subjects</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="video-list">
                                <tr><td colspan="6" class="text-center">Loading videos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- End Library Tab -->

            <!-- Lessons Tab -->
            <div id="lessons" class="tab-content" style="display: none;">
                <div class="section-header">
                    <h3>My Lessons & Activities</h3>
                    <button class="btn btn-primary" onclick="toggleLessonForm(true)"><i class="fas fa-plus"></i> Create New Lesson</button>
                </div>

                <div id="lesson-form-wrapper" class="glass-panel" style="display: none; margin-bottom: 30px; padding: 30px;">
                    <h4 id="form-title" style="margin-bottom: 20px;">Create New Lesson</h4>
                    <form id="lesson-form">
                        <input type="hidden" name="action" value="create">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Lesson Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Intro to Addition" required style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Subject</label>
                            <select name="subject_id" id="lesson-subject-select" class="form-control" required style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px;">
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Lesson Content</label>
                            <textarea name="content" class="form-control" rows="6" placeholder="Write your lesson content here..." required style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px;"></textarea>
                        </div>
                        <div class="activities-section" style="margin-top: 20px; border-top: 1px solid #E2E8F0; padding-top: 20px;">
                            <label><strong>Add Activity (Optional)</strong></label>
                            <div id="activities-list"></div>
                            <button type="button" class="btn btn-sm btn-outline" onclick="addActivityFields()" style="margin-top: 10px;">+ Add Activity</button>
                        </div>
                        <div style="margin-top: 30px; display: flex; gap: 15px;">
                            <button type="submit" class="btn btn-primary">Save Lesson</button>
                            <button type="button" class="btn btn-outline" onclick="toggleLessonForm(false)">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="stats-row" id="lessons-grid"></div>
            </div>


            <div id="attendance" class="tab-content" style="display: none;">
                <div class="glass-panel" style="padding: 30px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3>Daily Attendance: <span id="current-date"><?php echo date('F j, Y'); ?></span></h3>
                        <p class="text-muted">Grade <?php echo $_SESSION['grade_level']; ?> Student Roster</p>
                    </div>
                    <button class="btn btn-primary" onclick="saveAttendance()">Save Changes <i class="fas fa-save"></i></button>
                </div>

                <div class="table-responsive glass-panel">
                    <table class="video-table attendance-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Status Today</th>
                                <th>Historical Summary</th>
                                <th>Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-list">
                            <tr><td colspan="4" class="text-center">Loading students...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadVideos();
            loadAttendance();

            // Handle URL Hash for Tab Switching
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const targetLink = document.querySelector(`.nav-link[onclick*="'${hash}'"]`);
                if (targetLink) targetLink.click();
            }
        });

        async function loadVideos() {
            // Existing logic
        }

        function switchTab(tabId, el) {
            // Update Navigation
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            el.classList.add('active');

            // Update Page Title
            const titles = {
                'library': 'Video Library',
                'attendance': 'Attendance Rosters',
                'lessons': 'Lessons & Activities'
            };
            document.getElementById('page-title').textContent = titles[tabId] || 'Dashboard';

            // Switch Content
            document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';

            if (tabId === 'lessons') {
                loadLessons();
                loadSubjectsForLessons();
            }
        }

        function toggleLessonForm(show) {
            const wrapper = document.getElementById('lesson-form-wrapper');
            wrapper.style.display = show ? 'block' : 'none';
            if (!show) {
                document.getElementById('lesson-form').reset();
                document.getElementById('activities-list').innerHTML = '';
            }
        }

        let activityCount = 0;
        function addActivityFields() {
            const list = document.getElementById('activities-list');
            const div = document.createElement('div');
            div.className = 'glass-panel';
            div.style.padding = '15px';
            div.style.marginBottom = '10px';
            div.style.borderLeft = '4px solid var(--primary)';
            
            div.innerHTML = `
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" name="activities[${activityCount}][title]" placeholder="Activity Title (e.g., Quiz 1)" required style="flex: 2; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                    <select name="activities[${activityCount}][type]" style="flex: 1; padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="interactive">Interactive Task</option>
                    </select>
                </div>
                <textarea name="activities[${activityCount}][description]" placeholder="Short instructions..." style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd;"></textarea>
                <div style="margin-top: 5px;">
                    <label class="text-xs">Reward Points:</label>
                    <input type="number" name="activities[${activityCount}][points]" value="10" style="width: 60px; padding: 5px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()" style="margin-top: 5px;">Remove</button>
            `;
            list.appendChild(div);
            activityCount++;
        }

        async function loadSubjectsForLessons() {
            const select = document.getElementById('lesson-subject-select');
            if (select.children.length > 0) return; // Already loaded
            
            const res = await apiRequest('/E-Learning/api/videos.php?action=subjects');
            if (res.success) {
                select.innerHTML = '<option value="" disabled selected>Select a Subject</option>';
                res.data.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    select.appendChild(opt);
                });
            }
        }

        async function loadLessons() {
            const grid = document.getElementById('lessons-grid');
            grid.innerHTML = '<div class="glass-panel" style="grid-column: 1/-1; padding: 20px;">Loading lessons...</div>';
            
            const res = await apiRequest('/E-Learning/api/lessons.php?action=list');
            if (res.success) {
                const lessons = res.data;
                if (lessons.length === 0) {
                    grid.innerHTML = '<div class="glass-panel" style="grid-column: 1/-1; padding: 20px;">No lessons created yet. Start by creating one!</div>';
                    return;
                }

                grid.innerHTML = '';
                lessons.forEach(l => {
                    const card = document.createElement('div');
                    card.className = 'stat-card glass-panel bounce-hover';
                    card.style.flexDirection = 'column';
                    card.style.alignItems = 'flex-start';
                    card.style.gap = '10px';
                    
                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span class="badge badge-subject" style="background: var(--complement);">${escapeHtml(l.subject_name)}</span>
                            <span class="text-xs text-muted">${new Date(l.created_at).toLocaleDateString()}</span>
                        </div>
                        <h3 style="margin: 5px 0;">${escapeHtml(l.title)}</h3>
                        <p class="text-sm" style="color: var(--text-light); line-height: 1.4;">${escapeHtml(l.content.substring(0, 80))}...</p>
                        <div style="margin-top: 10px; display: flex; gap: 10px; width: 100%; border-top: 1px solid #eee; padding-top: 10px;">
                            <span class="text-xs"><strong>${l.activities.length}</strong> Activities</span>
                            <button class="btn-icon btn-danger" style="margin-left: auto;" onclick="deleteLesson(${l.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }
        }

        document.getElementById('lesson-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            // Convert activities to array
            const res = await apiRequest('/E-Learning/api/lessons.php', 'POST', formData);
            if (res.success) {
                alert('Lesson created successfully!');
                toggleLessonForm(false);
                loadLessons();
            } else {
                alert('Error: ' + res.message);
            }
        });

        async function deleteLesson(id) {
            if (confirm('Are you sure you want to delete this lesson?')) {
                const res = await apiRequest('/E-Learning/api/lessons.php', 'POST', { action: 'delete', id });
                if (res.success) {
                    loadLessons();
                }
            }
        }

        async function loadVideos() {
            const tbody = document.getElementById('video-list');
            const res = await apiRequest('/E-Learning/api/videos.php?action=list');
            
            if (res.success) {
                const videos = res.data;
                document.getElementById('total-videos').textContent = videos.length;
                
                let totalBytes = 0;
                videos.forEach(v => totalBytes += parseInt(v.file_size));
                document.getElementById('total-storage').textContent = (totalBytes / (1024 * 1024)).toFixed(2) + ' MB';

                if (videos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No videos uploaded yet.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                videos.forEach(video => {
                    const tr = document.createElement('tr');
                    
                    const thumbUrl = video.thumbnail_path ? `/E-Learning${video.thumbnail_path}` : '../assets/images/default-thumb.png'; // Placeholder

                    tr.innerHTML = `
                        <td>
                            <div class="list-thumb" style="background-image: url('${thumbUrl}')">
                                ${!video.thumbnail_path ? '<i class="fas fa-video"></i>' : ''}
                            </div>
                        </td>
                        <td>
                            <strong>${escapeHtml(video.title)}</strong>
                            <div class="text-sm text-muted">${new Date(video.upload_date).toLocaleDateString()}</div>
                        </td>
                        <td>
                            <div class="badge-container">
                                ${video.grades.map(g => `<span class="badge badge-grade grade-${g}">${g}</span>`).join('')}
                            </div>
                        </td>
                        <td>
                            <div class="badge-container">
                                ${video.subjects.map(s => `<span class="badge badge-subject">${escapeHtml(s)}</span>`).join('')}
                            </div>
                        </td>
                        <td class="text-sm">${(video.file_size / (1024 * 1024)).toFixed(1)} MB</td>
                        <td>
                            <button class="btn-icon btn-danger" onclick="deleteVideo(${video.id}, '${escapeHtml(video.title)}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error: ${res.message}</td></tr>`;
            }
        }

        async function deleteVideo(id, title) {
            if (confirm(`Are you sure you want to delete "${title}"? This cannot be undone.`)) {
                const data = new FormData();
                data.append('action', 'delete');
                data.append('video_id', id);
                
                const res = await apiRequest('/E-Learning/api/videos.php', 'POST', data);
                if (res.success) {
                    loadVideos(); // Reload list
                } else {
                    alert('Error deleting video: ' + res.message);
                }
            }
        }

        async function loadAttendance() {
            const tbody = document.getElementById('attendance-list');
            const res = await apiRequest('../api/attendance.php?action=roster');
            
            if (res.success) {
                const students = res.data;
                if (students.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No students registered in your grade yet.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                students.forEach(student => {
                    const tr = document.createElement('tr');
                    const stats = student.stats;
                    const totalDays = parseInt(stats.present_count) + parseInt(stats.absent_count);
                    const rate = totalDays > 0 ? Math.round((parseInt(stats.present_count) / totalDays) * 100) : 0;
                    
                    tr.innerHTML = `
                        <td><strong>${escapeHtml(student.username)}</strong></td>
                        <td>
                            <div class="attendance-options">
                                <label class="status-btn present">
                                    <input type="radio" name="att_${student.id}" value="present" ${student.today_status === 'present' ? 'checked' : ''}>
                                    <span>P</span>
                                </label>
                                <label class="status-btn absent">
                                    <input type="radio" name="att_${student.id}" value="absent" ${student.today_status === 'absent' ? 'checked' : ''}>
                                    <span>A</span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="stats-summary">
                                <span class="text-success"><i class="fas fa-check-circle"></i> ${stats.present_count} Present</span>
                                <span class="text-danger" style="margin-left:10px;"><i class="fas fa-times-circle"></i> ${stats.absent_count} Absent</span>
                            </div>
                        </td>
                        <td>
                            <div class="rate-bar-container">
                                <div class="rate-bar-bg">
                                    <div class="rate-bar" style="width: ${rate}%; background: ${rate > 80 ? '#06D6A0' : (rate > 50 ? '#FFE66D' : '#FF6B6B')}"></div>
                                </div>
                                <span class="rate-text">${rate}%</span>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error: ${res.message}</td></tr>`;
            }
        }

        async function saveAttendance() {
            const rosterTable = document.getElementById('attendance-list');
            const attendance = {};
            let count = 0;
            
            rosterTable.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                const studentId = input.name.split('_')[1];
                attendance[studentId] = input.value;
                count++;
            });

            if (count === 0) {
                alert('Please mark attendance for at least one student.');
                return;
            }

            const res = await apiRequest('../api/attendance.php', 'POST', { action: 'save', attendance });
            if (res.success) {
                alert('Attendance saved successfully!');
                loadAttendance(); // Refresh to update stats
            } else {
                alert('Error: ' + res.message);
            }
        }

        function escapeHtml(unsafe) {
            return (unsafe || '').toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
