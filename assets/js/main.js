// main.js

document.addEventListener('DOMContentLoaded', () => {
    // Tab switching for Login Page / Modal
    const tabBtns = document.querySelectorAll('.tab-btn');
    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all
                tabBtns.forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.login-form').forEach(f => f.classList.remove('active'));

                // Add active to clicked
                btn.classList.add('active');
                const targetId = btn.getAttribute('data-target');
                document.getElementById(targetId).classList.add('active');
            });
        });
    }

    // Student Toggle (Login/Register)
    const toggleBtns = document.querySelectorAll('.student-toggle-btn');
    if (toggleBtns.length > 0) {
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                const modalTitle = document.getElementById('modal-title');
                const modalSubtitle = document.getElementById('modal-subtitle');

                toggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (type === 'login') {
                    document.getElementById('student-login-form').classList.remove('hidden');
                    document.getElementById('student-register-form').classList.add('hidden');
                    modalTitle.textContent = 'Welcome Back';
                    modalSubtitle.textContent = 'Log in to your learning account';
                } else {
                    document.getElementById('student-login-form').classList.add('hidden');
                    document.getElementById('student-register-form').classList.remove('hidden');
                    modalTitle.textContent = 'Join the Fun!';
                    modalSubtitle.textContent = 'Create your student account';
                }
            });
        });
    }

    // Teacher Toggle (Login/Register)
    const teacherToggleBtns = document.querySelectorAll('.teacher-toggle-btn');
    if (teacherToggleBtns.length > 0) {
        teacherToggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                const modalTitle = document.getElementById('modal-title');
                const modalSubtitle = document.getElementById('modal-subtitle');

                teacherToggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (type === 'login') {
                    document.getElementById('teacher-login-form').classList.remove('hidden');
                    document.getElementById('teacher-register-form').classList.add('hidden');
                    modalTitle.textContent = 'Welcome Back';
                    modalSubtitle.textContent = 'Log in to your teacher account';
                } else {
                    document.getElementById('teacher-login-form').classList.add('hidden');
                    document.getElementById('teacher-register-form').classList.remove('hidden');
                    modalTitle.textContent = 'Join our Educators';
                    modalSubtitle.textContent = 'Create your teacher account';
                }
            });
        });
    }

    // Modal Close logic
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
});

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Generic Fetch Wrapper
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method,
    };

    if (data && method !== 'GET') {
        if (data instanceof FormData) {
            options.body = data; // Browser sets Content-Type to multipart/form-data with boundary automatically
        } else {
            options.headers = { 'Content-Type': 'application/json' };
            options.body = JSON.stringify(data);
        }
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error("API Error:", error);
        return { success: false, message: "A network error occurred." };
    }
}

// Handle Login (Login Page)
async function handleLogin(event, role) {
    event.preventDefault();
    const form = event.target;
    const errorDiv = document.getElementById(`${role}-error`);
    const submitBtn = form.querySelector('button[type="submit"]');

    errorDiv.textContent = '';
    submitBtn.disabled = true;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.action = `login_${role}`;

    const res = await apiRequest('api/auth.php', 'POST', data);

    if (res.success) {
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Success!';
        window.location.href = res.redirect;
    } else {
        errorDiv.textContent = res.message || 'Login failed.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Handle Registration
async function handleRegister(event, role) {
    event.preventDefault();
    const form = event.target;
    const errorDiv = document.getElementById(`${role}-reg-error`);
    const submitBtn = form.querySelector('button[type="submit"]');

    errorDiv.textContent = '';
    submitBtn.disabled = true;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.action = `register_${role}`;

    const res = await apiRequest('api/auth.php', 'POST', data);

    if (res.success) {
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Welcome!';
        window.location.href = res.redirect;
    } else {
        errorDiv.textContent = res.message || 'Registration failed.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Handle Logout
async function logout() {
    const res = await apiRequest('/E-Learning/api/auth.php', 'POST', { action: 'logout' });
    if (res.success) {
        window.location.href = res.redirect;
    }
}

// Helper to render subjects into select dropdown
async function fetchSubjects(selectElementId) {
    const el = document.getElementById(selectElementId);
    if (!el) return;

    const res = await apiRequest('/E-Learning/api/videos.php?action=subjects');
    if (res.success) {
        el.innerHTML = '';
        res.data.forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub.id;
            opt.textContent = sub.name;
            el.appendChild(opt);
        });
    }
}
