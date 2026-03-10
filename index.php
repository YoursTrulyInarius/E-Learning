<?php
require_once 'config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    if (isTeacher()) redirect('teacher/dashboard.php');
    if (isStudent()) {
        $grade = $_SESSION['grade_level'];
        if ($grade === 'K') redirect('student/K/kinder.php');
        if (in_array($grade, ['1','2','3','4','5','6'])) redirect("student/{$grade}/grade{$grade}.php");
        redirect('student/dashboard.php');
    }
}

// Fetch Real-time Data for the landing page
try {
    // 1. Fetch featured videos (Latest 4)
    $stmtVideos = $pdo->query("SELECT * FROM videos ORDER BY upload_date DESC LIMIT 4");
    $featuredVideos = $stmtVideos->fetchAll();

    // 2. Fetch our educators
    $stmtTeachers = $pdo->query("SELECT username FROM users WHERE role = 'teacher' LIMIT 4");
    $teachers = $stmtTeachers->fetchAll();

    // 3. Stats
    $videoCount = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

} catch (PDOException $e) {
    $featuredVideos = [];
    $teachers = [];
    $videoCount = 0;
    $studentCount = 0;
    $teacherCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LittleLearners Hub - Grade-Progressive E-Learning</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <header class="main-header">
        <a href="index.php" class="logo-link">
            <i class="fas fa-graduation-cap"></i>
            <h1>LittleLearners Hub</h1>
        </a>
        <div class="nav-actions">
            <button class="btn btn-outline" onclick="openModal('login-modal-overlay')">Log In</button>
            <button class="btn btn-primary" onclick="openModal('login-modal-overlay'); document.querySelector('.student-toggle-btn[data-type=register]').click();">Get Started</button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="bounce-anim" style="display: inline-block; margin-bottom: 20px;">
            <i class="fas fa-rocket" style="font-size: 4rem; color: var(--primary);"></i>
        </div>
        <h2>Learning That Grows With You!</h2>
        <p>A fun, safe, and organized video library specially designed for Kindergarten through Grade 6. Explore amazing educational content curated by your teachers.</p>
        <div class="hero-btns">
            <button class="btn btn-primary btn-large glow-effect" onclick="openModal('login-modal-overlay')">Explore My Grade <i class="fas fa-search"></i></button>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section-container" style="padding-top: 0; margin-top: -50px;">
        <div class="glass-panel" style="padding: 30px; display: flex; justify-content: space-around; text-align: center;">
            <div class="stat-item">
                <h4 style="font-size: 2.5rem; color: var(--primary);"><?php echo $videoCount; ?>+</h4>
                <p>Curated Videos</p>
            </div>
            <div class="stat-item">
                <h4 style="font-size: 2.5rem; color: var(--secondary);"><?php echo $teacherCount; ?>+</h4>
                <p>Expert Educators</p>
            </div>
            <div class="stat-item">
                <h4 style="font-size: 2.5rem; color: var(--gr1-color);"><?php echo $studentCount; ?>+</h4>
                <p>Happy Learners</p>
            </div>
        </div>
    </section>

    <!-- Featured Videos -->
    <section class="section-container">
        <div class="section-title-wrapper">
            <h3>New Adventures Await</h3>
            <p>Check out our latest educational videos uploaded by teachers.</p>
        </div>
        <div class="showcase-grid">
            <?php if (empty($featuredVideos)): ?>
                <div class="glass-panel" style="grid-column: 1/-1; padding: 40px; text-align: center;">
                    <i class="fas fa-video-slash" style="font-size: 3rem; color: #ccc; margin-bottom: 15px;"></i>
                    <p>Teachers are currently busy curating new content. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredVideos as $video): ?>
                <div class="video-card-mini glass-panel" style="padding: 15px; border-radius: var(--radius-md);">
                    <div class="video-thumb-preview" style="background-image: url('<?php echo $video['thumbnail_path'] ? 'E-Learning'.$video['thumbnail_path'] : 'assets/images/default-thumb.png'; ?>')">
                        <div class="play-icon"><i class="fas fa-play-circle"></i></div>
                    </div>
                    <h4 style="margin-bottom: 5px; font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($video['title']); ?></h4>
                    <span class="badge-subject" style="font-size: 0.7rem; padding: 2px 8px; background: #eee; border-radius: 10px; color: #666;">New Content</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Our Teachers -->
    <section class="section-container" style="background: rgba(255,255,255,0.3); border-radius: var(--radius-lg);">
        <div class="section-title-wrapper">
            <h3>Meet Our Educators</h3>
            <p>The dedicated teachers making learning possible every day.</p>
        </div>
        <div class="teacher-grid">
            <?php if (empty($teachers)): ?>
                <div class="teacher-card glass-panel" style="grid-column: 1/-1;">
                    <p>Default Admin is ready to help!</p>
                </div>
            <?php else: ?>
                <?php foreach ($teachers as $teacher): ?>
                <div class="teacher-card glass-panel">
                    <div class="teacher-avatar-large">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h4>Teacher <?php echo htmlspecialchars($teacher['username']); ?></h4>
                    <p class="text-sm text-muted">Curriculum Content Specialist</p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <style>
        /* Minimalist Modal Styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .auth-modal {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-hover);
            border: 1px solid #E2E8F0;
            position: relative;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }

        #modal-title {
            color: var(--text-dark);
            font-size: 2rem;
            margin-bottom: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
        }

        #modal-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }

        /* Clean Tab Navigation */
        .modal-tabs {
            display: flex;
            background: #F1F5F9;
            padding: 4px;
            border-radius: var(--radius-md);
            margin-bottom: 25px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            color: var(--text-light);
            font-weight: 700;
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .tab-btn.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        /* Form Improvements */
        .form-group.icon-input {
            position: relative;
            margin-bottom: 15px;
        }

        .form-group.icon-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .form-group.icon-input input, .grade-select {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #E2E8F0;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
            outline: none;
            background: #F8FAFC;
            font-family: inherit;
        }

        .form-group.icon-input input:focus, .grade-select:focus {
            border-color: var(--primary);
            background: var(--white);
        }

        .btn-block {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary { background: var(--primary); color: white; border: none; }
        .btn-secondary { background: var(--secondary); color: white; border: none; }
        .btn-outline { background: transparent; border: 2px solid #E2E8F0; color: var(--text-light); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        
        /* Toggle Styling */
        .auth-toggle, .teacher-toggle {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .toggle-btn, .teacher-toggle-btn {
            background: transparent;
            border: none;
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
            transition: var(--transition);
        }

        .toggle-btn.active, .teacher-toggle-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .hidden { display: none; }
        .error-msg {
            color: #EF4444;
            font-size: 0.85rem;
            margin-top: 10px;
            text-align: center;
            min-height: 1.2em;
        }
    </style>

    <!-- Login Modal Overlay -->
    <div class="modal-overlay" id="login-modal-overlay">
        <div class="auth-modal">
            <div class="logo-area">
                <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--primary);"></i>
                <h1 id="modal-title">Welcome Back</h1>
                <p id="modal-subtitle">Log in to your learning account</p>
            </div>

            <div class="login-tabs">
                <button class="tab-btn active" data-target="student-section">
                    <i class="fas fa-child"></i> Student
                </button>
                <button class="tab-btn" data-target="teacher-login">
                    <i class="fas fa-chalkboard-teacher"></i> Teacher
                </button>
            </div>

            <div class="login-forms">
                <!-- Student Section -->
                <div id="student-section" class="login-form active">
                    <div class="student-toggle" style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: center;">
                        <button class="btn btn-sm student-toggle-btn active" data-type="login" style="padding: 5px 15px; font-size: 0.9rem;">Login</button>
                        <button class="btn btn-sm student-toggle-btn" data-type="register" style="padding: 5px 15px; font-size: 0.9rem;">Register</button>
                    </div>

                    <!-- Student Login Form -->
                    <form id="student-login-form" onsubmit="handleLogin(event, 'student')">
                        <div class="form-group icon-input">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="form-group icon-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large btn-block glow-effect">Start Learning! <i class="fas fa-arrow-right"></i></button>
                        <div class="error-msg" id="student-error"></div>
                    </form>

                    <!-- Student Registration Form (Hidden by default) -->
                    <form id="student-register-form" class="hidden" onsubmit="handleRegister(event, 'student')">
                        <div class="form-group icon-input">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Choose Username" required>
                        </div>
                        <div class="form-group icon-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Choose Password" required>
                        </div>
                        <div class="form-group">
                            <select name="grade_level" class="grade-select" required style="width: 100%; padding: 15px; border-radius: var(--radius-md); border: 2px solid #eee; background: rgba(255,255,255,0.8); font-family: inherit; font-size: 1rem;">
                                <option value="" disabled selected>Select Your Grade</option>
                                <option value="K">Kindergarten</option>
                                <option value="1">Grade 1</option>
                                <option value="2">Grade 2</option>
                                <option value="3">Grade 3</option>
                                <option value="4">Grade 4</option>
                                <option value="5">Grade 5</option>
                                <option value="6">Grade 6</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-large btn-block glow-effect">Create My Account! <i class="fas fa-magic"></i></button>
                        <div class="error-msg" id="student-reg-error"></div>
                    </form>
                </div>

                <!-- Teacher Section -->
                <div id="teacher-login" class="login-form">
                    <div class="teacher-toggle" style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: center;">
                        <button class="btn btn-sm teacher-toggle-btn active" data-type="login" style="padding: 5px 15px; font-size: 0.9rem;">Login</button>
                        <button class="btn btn-sm teacher-toggle-btn" data-type="register" style="padding: 5px 15px; font-size: 0.9rem;">Register</button>
                    </div>

                    <!-- Teacher Login Form -->
                    <form id="teacher-login-form" onsubmit="handleLogin(event, 'teacher')">
                        <div class="form-group icon-input">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="form-group icon-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block">Log In</button>
                        <div class="error-msg" id="teacher-error"></div>
                    </form>

                    <!-- Teacher Registration Form -->
                    <form id="teacher-register-form" class="hidden" onsubmit="handleRegister(event, 'teacher')">
                        <div class="form-group icon-input">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Choose Username" required>
                        </div>
                        <div class="form-group icon-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Choose Password" required>
                        </div>
                        <div class="form-group">
                            <select name="grade_level" class="grade-select" required>
                                <option value="" disabled selected>Specialize in which Grade?</option>
                                <option value="K">Kindergarten</option>
                                <option value="1">Grade 1</option>
                                <option value="2">Grade 2</option>
                                <option value="3">Grade 3</option>
                                <option value="4">Grade 4</option>
                                <option value="5">Grade 5</option>
                                <option value="6">Grade 6</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block">Create Teacher Account</button>
                        <div class="error-msg" id="teacher-reg-error"></div>
                    </form>
                </div>
            </div>
            
            <button onclick="closeModal('login-modal-overlay')" class="btn btn-outline btn-block" style="margin-top: 20px; border: none; color: #999;">Cancel</button>
        </div>
    </div>

    <footer style="padding: 50px; text-align: center; border-top: 1px solid #eee;">
        <p>&copy; 2023 LittleLearners Hub. Made with <i class="fas fa-heart" style="color: var(--primary);"></i> for the future.</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
