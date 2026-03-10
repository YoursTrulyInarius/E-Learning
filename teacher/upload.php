<?php
require_once '../config.php';
if (!isTeacher()) redirect('index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video - LittleLearners Hub</title>
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
                    <a href="dashboard.php" class="nav-link"><i class="fas fa-video"></i> Video Library</a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php#attendance" class="nav-link"><i class="fas fa-calendar-check text-success"></i> Attendance</a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php#lessons" class="nav-link"><i class="fas fa-chalkboard-teacher text-complement"></i> Lessons & Activities</a>
                </li>
                <li class="nav-item">
                    <a href="upload.php" class="nav-link active"><i class="fas fa-cloud-upload-alt"></i> Upload Content</a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <button onclick="logout()" class="btn btn-outline btn-block"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <h2>Upload Content</h2>
                <div class="user-profile">
                    <span>Welcome, Teacher <?php echo htmlspecialchars($_SESSION['username']); ?>! 
                    <span class="badge badge-grade grade-<?php echo $_SESSION['grade_level']; ?>" style="margin-left: 10px;"><?php echo $_SESSION['grade_level']; ?> Specialist</span></span>
                    <div class="avatar"><i class="fas fa-user"></i></div>
                </div>
            </header>

            <div class="glass-panel" style="max-width: 800px; padding: 40px; margin: 0 auto;">
                <form id="upload-form" onsubmit="handleUpload(event)">
                    
                    <div class="upload-area" id="drop-zone" onclick="document.getElementById('video_file').click()">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <h3 id="file-name-display">Drag & Drop Video Here</h3>
                        <p class="text-muted text-sm">Or click to browse files (MP4, MOV, MKV max 500MB)</p>
                        <input type="file" id="video_file" name="video" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska" class="hidden" required onchange="updateFileName(this)">
                    </div>

                    <div class="progress-container" id="progress-container">
                        <div class="progress-bar" id="progress-bar"></div>
                    </div>
                    <div id="upload-status" class="text-center text-sm" style="margin-bottom: 20px; font-weight: 600;"></div>

                    <div class="form-group icon-input">
                        <i class="fas fa-heading"></i>
                        <input type="text" name="title" placeholder="Video Title (e.g. Learning to Count 1 to 10)" required>
                    </div>

                    <div class="form-group icon-input">
                        <i class="fas fa-align-left"></i>
                        <input type="text" name="description" placeholder="Short description for parents/teachers (optional)">
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label><strong>Subject Area</strong></label>
                            <div class="checkbox-grid" id="subjects-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                                <!-- Loaded via JS -->
                                <p class="text-sm text-muted">Loading subjects...</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Custom Thumbnail</strong> (Optional)</label>
                        <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display: block; margin-top: 10px;">
                    </div>

                    <button type="submit" class="btn btn-primary btn-large btn-block glow-effect" id="submit-btn">
                        Upload Video to Server <i class="fas fa-paper-plane"></i>
                    </button>
                    
                    <div id="form-error" class="error-msg text-center"></div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', loadCheckboxes);

        async function loadCheckboxes() {
            const grid = document.getElementById('subjects-grid');
            const res = await apiRequest('/E-Learning/api/videos.php?action=subjects');
            
            if (res.success) {
                grid.innerHTML = '';
                res.data.forEach(sub => {
                    const label = document.createElement('label');
                    label.className = 'checkbox-btn';
                    label.style.marginBottom = '5px';
                    label.innerHTML = `
                        <input type="checkbox" name="subjects[]" value="${sub.id}">
                        <span class="checkmark" style="justify-content: flex-start; gap: 10px;">
                            <i class="${sub.icon}"></i> ${escapeHtml(sub.name)}
                        </span>
                    `;
                    grid.appendChild(label);
                });
            }
        }

        // Drag and Drop
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('video_file');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files; // Assign to input
            updateFileName(fileInput);
        }, false);

        function updateFileName(input) {
            if (input.files && input.files[0]) {
                const name = input.files[0].name;
                const size = (input.files[0].size / (1024*1024)).toFixed(2);
                document.getElementById('file-name-display').innerHTML = `<i class="fas fa-file-video text-primary"></i> ${name} (${size} MB)`;
            }
        }

        function escapeHtml(unsafe) {
            return (unsafe || '').toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;");
        }

        // Custom AJAX upload to track progress
        function handleUpload(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('submit-btn');
            const errorDiv = document.getElementById('form-error');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const statusText = document.getElementById('upload-status');
            
            const formData = new FormData(form);
            
            // Validate checkboxes manually since FormData doesn't complain if empty
            if (!formData.has('subjects[]')) {
                errorDiv.textContent = 'Please select at least one subject area.';
                return;
            }

            errorDiv.textContent = '';
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading... Please wait';
            progressContainer.style.display = 'block';
            statusText.textContent = 'Starting upload...';
            progressBar.style.width = '0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/E-Learning/api/upload.php', true);

            // Upload progress
            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    statusText.textContent = `Uploading: ${Math.round(percentComplete)}%`;
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            statusText.textContent = 'Upload Complete!';
                            statusText.style.color = '#06D6A0';
                            btn.innerHTML = '<i class="fas fa-check"></i> Success!';
                            setTimeout(() => {
                                window.location.href = 'dashboard.php';
                            }, 1500);
                        } else {
                            throw new Error(res.message);
                        }
                    } catch (err) {
                        handleError(err.message || 'Error parsing server response.');
                    }
                } else {
                    handleError(`Server error: ${xhr.status}`);
                }
            };

            xhr.onerror = function() {
                handleError('Network error occurred during upload.');
            };

            function handleError(msg) {
                errorDiv.textContent = msg;
                statusText.textContent = 'Upload Failed';
                statusText.style.color = '#e74c3c';
                btn.disabled = false;
                btn.innerHTML = 'Try Again <i class="fas fa-redo"></i>';
                progressBar.style.backgroundColor = '#e74c3c';
            }

            xhr.send(formData);
        }
    </script>
</body>
</html>
