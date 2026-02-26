<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
}

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background Elements */
body::before {
    content: '';
    position: absolute;
    width: 600px;
    height: 600px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -300px;
    right: -300px;
    animation: float 20s ease-in-out infinite;
}

body::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 50%;
    bottom: -200px;
    left: -200px;
    animation: float 15s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

.login-container {
    max-width: 1000px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.login-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.6s ease-out;
    display: grid;
    grid-template-columns: 1fr 1fr;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Left Panel - Login Form */
.login-form-panel {
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-form-header {
    margin-bottom: 40px;
}

.login-form-header h2 {
    font-size: 2rem;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 8px;
}

.login-form-header p {
    color: #64748b;
    font-size: 1rem;
}

.info-badge {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
    border-left: 4px solid #3b82f6;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-badge i {
    color: #3b82f6;
    font-size: 1.2rem;
}

.info-badge span {
    color: #1e40af;
    font-size: 0.9rem;
    font-weight: 500;
}

.mb-3 {
    margin-bottom: 24px;
    position: relative;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 10px;
    font-size: 0.95rem;
}

.form-label .required {
    color: #ef4444;
    margin-left: 4px;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 48px;
    color: #64748b;
    font-size: 1.1rem;
    z-index: 1;
}

.form-control {
    width: 100%;
    padding: 14px 16px 14px 48px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
    color: #1a202c;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: white;
}

.form-control::placeholder {
    color: #9ca3af;
}

.password-toggle-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #64748b;
    font-size: 1.1rem;
    transition: color 0.3s ease;
    z-index: 2;
}

.password-toggle:hover {
    color: #667eea;
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 6px;
    display: none;
}

.was-validated .form-control:invalid ~ .invalid-feedback {
    display: block;
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
}

.remember-me label {
    font-size: 0.9rem;
    color: #64748b;
    cursor: pointer;
    user-select: none;
}

.forgot-password {
    font-size: 0.9rem;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #764ba2;
}

.btn {
    padding: 16px;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-primary {
    width: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.btn-primary:active {
    transform: translateY(0);
}

.divider {
    display: flex;
    align-items: center;
    margin: 32px 0;
    gap: 16px;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.divider span {
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
}

.back-home {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    color: #64748b;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-home:hover {
    background: white;
    border-color: #667eea;
    color: #667eea;
}

/* Right Panel - Branding */
.login-brand-panel {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-brand-panel::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -100px;
    right: -100px;
}

.logo-wrapper {
    position: relative;
    z-index: 1;
    margin-bottom: 40px;
}

.logo-image {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid white;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    background: white;
}

.brand-content {
    text-align: center;
    color: white;
    position: relative;
    z-index: 1;
}

.brand-content h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 12px;
    letter-spacing: 1px;
}

.brand-content p {
    font-size: 1.1rem;
    opacity: 0.95;
    margin-bottom: 32px;
    line-height: 1.6;
}

.info-cards {
    display: grid;
    gap: 16px;
    width: 100%;
    margin-top: 32px;
}

.info-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 16px;
}

.info-card-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.info-card-content h3 {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
    opacity: 0.9;
}

.info-card-content p {
    font-size: 0.95rem;
    margin: 0;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 968px) {
    .login-card {
        grid-template-columns: 1fr;
    }
    
    .login-brand-panel {
        order: -1;
        padding: 40px 30px;
    }
    
    .login-form-panel {
        padding: 40px 30px;
    }
    
    .logo-image {
        width: 140px;
        height: 140px;
    }
    
    .brand-content h1 {
        font-size: 2rem;
    }
    
    .info-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .login-form-panel {
        padding: 30px 24px;
    }
    
    .login-brand-panel {
        padding: 30px 24px;
    }
    
    .login-form-header h2 {
        font-size: 1.6rem;
    }
    
    .form-footer {
        flex-direction: column;
        gap: 12px;
    }
    
    .brand-content h1 {
        font-size: 1.6rem;
    }
}
</style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <!-- Left Panel - Login Form -->
        <div class="login-form-panel">
            <div class="login-form-header">
                <h2><i class="bi bi-shield-lock-fill"></i> Admin Login</h2>
                <p>Sign in to access the admin dashboard</p>
            </div>

            <div class="info-badge">
                <i class="bi bi-info-circle-fill"></i>
                <span>Authorized personnel only. Please enter your admin credentials.</span>
            </div>

            <!-- Login Form -->
            <form action="adminprocess.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="admin_username" class="form-label">
                        Username <span class="required">*</span>
                    </label>
                    <i class="bi bi-person-fill input-icon"></i>
                    <input type="text" class="form-control" id="admin_username" name="username" 
                           placeholder="Enter admin username" required>
                    <div class="invalid-feedback">Please enter your username.</div>
                </div>

                <div class="mb-3">
                    <label for="admin_password" class="form-label">
                        Password <span class="required">*</span>
                    </label>
                    <i class="bi bi-lock-fill input-icon"></i>
                    <div class="password-toggle-container">
                        <input type="password" class="form-control" id="admin_password" name="password" 
                               placeholder="Enter password" required>
                        <i class="bi bi-eye password-toggle" onclick="togglePasswordVisibility('admin_password')"></i>
                    </div>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </div>
            </form>

        </div>

        <!-- Right Panel - Branding -->
        <div class="login-brand-panel">
            <div class="logo-wrapper">
                <img src="actslogo.png" alt="ACTS Learning Center" class="logo-image">
            </div>

            <div class="brand-content">
                <h1>ACTS Learning & Development Center</h1>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggle = event.target;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggle.classList.remove('bi-eye');
        toggle.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggle.classList.remove('bi-eye-slash');
        toggle.classList.add('bi-eye');
    }
}

// Bootstrap form validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Auto-focus username on load
window.addEventListener('load', function() {
    document.getElementById('admin_username').focus();
});

// Enter key on username field focuses password
document.getElementById('admin_username').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('admin_password').focus();
    }
});
</script>

</body>
</html>