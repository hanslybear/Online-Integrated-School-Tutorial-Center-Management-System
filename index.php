<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ACTS Learning & Development Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.4/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="actslogo.png">


    <style>
        /* MODERN TUTOR PORTFOLIO SECTION */
.tutor-portfolio-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 100px 0;
  position: relative;
  overflow: hidden;
}

.tutor-portfolio-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
  background-size: 50px 50px;
}

.tutor-portfolio-container {
  position: relative;
  z-index: 1;
}

.portfolio-header {
  text-align: center;
  margin-bottom: 60px;
  color: white;
}

.portfolio-header h2 {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 15px;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.portfolio-header p {
  font-size: 1.2rem;
  opacity: 0.95;
}

.tutor-profile-card {
  background: white;
  border-radius: 30px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0,0,0,0.3);
  transition: transform 0.4s ease;
}

.tutor-profile-card:hover {
  transform: translateY(-10px);
}

.tutor-image-container {
  position: relative;
  height: 450px;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.tutor-image-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.tutor-profile-card:hover .tutor-image-container img {
  transform: scale(1.05);
}

.tutor-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255,255,255,0.95);
  padding: 10px 20px;
  border-radius: 50px;
  font-weight: 600;
  color: #667eea;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  gap: 8px;
}

.tutor-badge i {
  font-size: 1.1rem;
}

.tutor-content {
  padding: 40px;
}

.tutor-header {
  text-align: center;
  padding-bottom: 30px;
  border-bottom: 3px solid #f0f0f0;
  margin-bottom: 30px;
}

.tutor-name {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 8px;
}

.tutor-title {
  font-size: 1.1rem;
  color: #667eea;
  font-weight: 600;
  margin-bottom: 15px;
}

.tutor-bio {
  color: #6b7280;
  line-height: 1.6;
  font-size: 0.95rem;
}

.credentials-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-top: 30px;
}

.credential-block h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.credential-block h5 i {
  color: #667eea;
  font-size: 1.3rem;
}

.credential-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.credential-list li {
  padding: 10px 0;
  color: #4b5563;
  font-size: 0.95rem;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.credential-list li:last-child {
  border-bottom: none;
}

.credential-list li i {
  color: #10b981;
  font-size: 1rem;
}

.specializations {
  margin-top: 30px;
  padding-top: 30px;
  border-top: 3px solid #f0f0f0;
}

.specializations h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 20px;
  text-align: center;
}

.skills-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
}

.skill-tag {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 10px 20px;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.skill-tag:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(102, 126, 234, 0.5);
}

.tutor-cta {
  margin-top: 40px;
  text-align: center;
  padding-top: 30px;
  border-top: 3px solid #f0f0f0;
}

.cta-button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 16px 40px;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  border: none;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.cta-button:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
}

.cta-button i {
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .credentials-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .portfolio-header h2 {
    font-size: 2rem;
  }
  
  .tutor-image-container {
    height: 350px;
  }
  
  .tutor-content {
    padding: 30px 20px;
  }
}

/* ENHANCED MODAL STYLES */
:root {
  --primary: #667eea;
  --secondary: #764ba2;
  --success: #10b981;
  --danger: #ef4444;
  --dark: #1a202c;
  --light: #f8fafc;
  --muted: #6b7280;
}

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.modal-content {
  border: none;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.modal-header {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  padding: 24px 32px;
  border-bottom: none;
}

.modal-header .modal-title {
  font-weight: 700;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-header .btn-close {
  filter: brightness(0) invert(1);
  opacity: 0.8;
}

.modal-header .btn-close:hover {
  opacity: 1;
}

.modal-body {
  padding: 32px;
  background: white;
}

.modal-footer {
  background: var(--light);
  border-top: 2px solid #e5e7eb;
  padding: 20px 32px;
  justify-content: center;
}

.form-label {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.form-label .required {
  color: var(--danger);
  margin-left: 4px;
}

.form-control, .form-select {
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  padding: 12px 16px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
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
  color: var(--muted);
  transition: color 0.3s ease;
  font-size: 1.1rem;
}

.password-toggle:hover {
  color: var(--primary);
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none;
  padding: 12px 32px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.modal-footer a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.modal-footer a:hover {
  color: var(--secondary);
  text-decoration: underline;
}

.info-badge {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(99, 102, 241, 0.1));
  border-left: 4px solid #3b82f6;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 20px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-badge i {
  color: #3b82f6;
  font-size: 1.2rem;
}

.password-requirements {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
  border-left: 4px solid var(--success);
  border-radius: 12px;
  padding: 12px 16px;
  margin-top: 8px;
  font-size: 0.8rem;
}

.password-requirements ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
  list-style: none;
}

.password-requirements li {
  margin-bottom: 4px;
  color: var(--dark);
  position: relative;
  padding-left: 8px;
}

.password-requirements li:before {
  content: "✓";
  color: var(--success);
  font-weight: 700;
  position: absolute;
  left: -12px;
}

.invalid-feedback {
  font-size: 0.85rem;
  margin-top: 4px;
}

/* Two column layout for signup */
.signup-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.signup-columns .full-width {
  grid-column: 1 / -1;
}

@media (max-width: 768px) {
  .signup-columns {
    grid-template-columns: 1fr;
  }
  
  .modal-body {
    padding: 24px;
  }
}

/* Welcome message for sign in */
.welcome-message {
  text-align: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
}

.welcome-message h6 {
  color: var(--dark);
  font-weight: 600;
  margin-bottom: 8px;
}

.welcome-message p {
  color: var(--muted);
  font-size: 0.9rem;
  margin: 0;
}


.logo-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.logo-circle img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* keeps logo proportions */
}



    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
           <span class="logo-circle">
    <img src="actslogo.png" alt="ACTS Logo">
</span>
            <span class="brand-text">ACTS Learning & Development Center</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#actsNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="actsNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#offer-tutorial">Offer Tutorial</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>

            <div class="navbar-buttons">
                <button type="button" class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#signInModal">
    Sign in
</button>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-white">
                <h1 class="hero-title">
                    Smart Learning Starts at <span>ACTS Learning & Development Center</span>
                </h1>
                <p class="hero-text">
                    Helping students achieve academic excellence through
                </p>

                <div class="hero-buttons">
                    <a href="enrollment.php" class="btn btn-light btn-lg me-2">Enroll Now</a>
            
                </div>
            </div>

            <div class="col-md-6 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/2995/2995620.png"
                     class="img-fluid hero-img"
                     alt="Online Learning">
            </div>
        </div>
    </div>
</section>

<!-- OFFER TUTORIAL SECTION -->
<section id="offer-tutorial" class="offer-section">
    <div class="container">
        <div class="section-title text-center">
            <h2>Offer Tutorial</h2>
            <p>Choose from our available tutorials.</p>
        </div>

        <div class="row">
            <!-- COMPUTER SERVICES -->
            <div class="col-md-6">
                <div class="offer-card">
                    <h4>Computer Services Tutorials</h4>
                    <ul>
                        <li>Java</li>
                        <li>C++</li>
                        <li>Python</li>
                        <li>Photoshop</li>
                        <li>Video Editing</li>
                    </ul>
                </div>
            </div>

            <!-- LEARNING SERVICES -->
            <div class="col-md-6">
                <div class="offer-card">
                    <h4>Learning Services Tutorials</h4>
                    <ul>
                        <li>English</li>
                        <li>Math</li>
                        <li>Filipino</li>
                        <li>Science</li>
                        <li>Reading</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION -->
<section id="about" class="about-section">
    <div class="container">
        <div class="section-title text-center">
            <h2>About ACTS Learning & Development Center</h2>
            <p>Your partner in achieving academic success.</p>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="about-card">
                    <h4>Our Mission</h4>
                    <p>
                        To provide quality and accessible learning opportunities through
                        interactive online tutorials, ensuring students achieve their academic goals.
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="about-card">
                    <h4>Our Vision</h4>
                    <p>
                        To be a leading learning center that empowers students through
                        innovative and modern education systems.
                    </p>
                </div>
            </div>
        </div>

        <div class="about-why mt-5 text-center">
            <h4>Why Choose Us?</h4>
            <p>
                We provide expert tutors, easy enrollment, progress monitoring, and a supportive learning environment.
            </p>
        </div>
    </div>
</section>

<!-- CONTACT SECTION -->
<section id="contact" class="contact-section">
  <div class="container">
    <div class="section-title text-center">
      <h2>Contact Us</h2>
      <p>Get in touch with us for any inquiries or information.</p>
    </div>

    <div class="contact-grid">
      <div class="contact-card">
        <div class="contact-icon"><i class="bi bi-telephone"></i></div>
        <h4>Phone</h4>
        <p>+63 985 201 4387</p>
      </div>

      <div class="contact-card">
        <div class="contact-icon"><i class="bi bi-envelope"></i></div>
        <h4>Email</h4>
        <p>actslearningcenter@gmail.com</p>
      </div>

      <div class="contact-card">
        <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
        <h4>Address</h4>
        <p>Sultan Kudarat Presient Quirino, Capilar Street</p>
      </div>

      <div class="contact-card">
        <div class="contact-icon"><i class="bi bi-clock"></i></div>
        <h4>Hours</h4>
        <p>Mon - Sat | 8:00 AM - 6:00 PM</p>
      </div>
    </div>
  </div>
</section>

<!-- MODERN TUTOR PORTFOLIO SECTION -->

<!-- ENHANCED SIGN IN MODAL -->
<div class="modal fade" id="signInModal" tabindex="-1" aria-labelledby="signInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="signInModalLabel">
                    <i class="bi bi-box-arrow-in-right"></i> Student Sign In
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="welcome-message">
                    <h6>Welcome Back!</h6>
                    <p>Sign in to access your student dashboard</p>
                </div>

                <form action="signin_process.php" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="signin_email" class="form-label">
                            <i class="bi bi-envelope"></i> Email Address <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" id="signin_email" name="email" 
                               placeholder="your.email@example.com" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="signin_password" class="form-label">
                            <i class="bi bi-lock"></i> Password <span class="required">*</span>
                        </label>
                        <div class="password-toggle-container">
                            <input type="password" class="form-control" id="signin_password" name="password" 
                                   placeholder="Enter your password" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePasswordVisibility('signin_password')"></i>
                        </div>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </div>
                </form>
            </div>

        


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password visibility toggle
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentElement.querySelector('.password-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password validation for signup
function validateSignupPasswords() {
    const password = document.getElementById('password_signup');
    const confirmPassword = document.getElementById('confirm_password_signup');
    const feedback = document.getElementById('password_match_feedback');
    
    if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity('Passwords do not match');
        feedback.textContent = 'Passwords must match.';
        return false;
    } else {
        confirmPassword.setCustomValidity('');
        return true;
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Password validation
    const passwordSignup = document.getElementById('password_signup');
    const confirmPasswordSignup = document.getElementById('confirm_password_signup');
    
    if (passwordSignup && confirmPasswordSignup) {
        passwordSignup.addEventListener('input', validateSignupPasswords);
        confirmPasswordSignup.addEventListener('input', validateSignupPasswords);
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Additional check for signup form passwords
            if (form.id === 'signupForm') {
                if (!validateSignupPasswords()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>
</body>
</html>