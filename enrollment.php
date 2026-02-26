<?php
session_start();
include "db_connect.php";

$showComplete = isset($_GET['step']) && $_GET['step'] == 'complete';

// Only pre-fill if there's an error message (meaning form was just submitted with errors)
$shouldPrefill = isset($_SESSION['error_message']);

// Pre-fill values from session if available AND there was an error
$prefill = [
  'student_name'    => $shouldPrefill && isset($_SESSION['student_name']) ? $_SESSION['student_name'] : '',
  'parents_name'    => $shouldPrefill && isset($_SESSION['parents_name']) ? $_SESSION['parents_name'] : '',
  'address'         => $shouldPrefill && isset($_SESSION['address']) ? $_SESSION['address'] : '',
  'birthday'        => $shouldPrefill && isset($_SESSION['birthday']) ? $_SESSION['birthday'] : '',
  'contact'         => $shouldPrefill && isset($_SESSION['contact']) ? $_SESSION['contact'] : '',
  'parents_contact' => $shouldPrefill && isset($_SESSION['parents_contact']) ? $_SESSION['parents_contact'] : '',
  'email'           => $shouldPrefill && isset($_SESSION['email']) ? $_SESSION['email'] : '',
  'gender'          => $shouldPrefill && isset($_SESSION['gender']) ? $_SESSION['gender'] : '',
  'password'        => '',
  'confirm_password' => '',
];

// Get error message if exists
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the error message after displaying (will be shown once)
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Enrollment - ACTS Learning Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.4/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">
  <style>
    :root{
      --primary: #667eea;
      --secondary: #764ba2;
      --success: #10b981;
      --danger: #ef4444;
      --warning: #f59e0b;
      --info: #3b82f6;
      --dark: #1a202c;
      --light: #f8fafc;
      --muted: #6b7280;
    }
    
    * {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    body { 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 40px 20px;
    }
    
    .enrollment-container {
      max-width: 900px;
      margin: 0 auto;
    }
    
    .card.enroll {
      border: none;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      background: white;
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 32px 40px;
      border: none;
    }
    
    .card-header h3 {
      margin: 0 0 8px 0;
      font-weight: 700;
      font-size: 1.8rem;
    }
    
    .card-header p {
      margin: 0;
      opacity: 0.9;
      font-size: 1rem;
    }
    
    .card-body {
      padding: 40px;
    }
    
    .form-section {
      margin-bottom: 32px;
    }
    
    .section-title {
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.2rem;
      padding-bottom: 12px;
      border-bottom: 2px solid var(--light);
    }
    
    .section-title i {
      color: var(--primary);
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
    
    .info-box {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(99, 102, 241, 0.1));
      border-left: 4px solid var(--info);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 24px;
    }
    
    .info-box i {
      color: var(--info);
      margin-right: 8px;
    }
    
    .info-box p {
      margin: 0;
      color: var(--dark);
      font-size: 0.9rem;
    }
    
    .password-requirements {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
      border-left: 4px solid var(--success);
      border-radius: 12px;
      padding: 16px;
      margin-top: 16px;
      font-size: 0.85rem;
    }
    
    .password-requirements ul {
      margin: 8px 0 0 0;
      padding-left: 20px;
    }
    
    .password-requirements li {
      color: var(--dark);
      margin-bottom: 4px;
    }
    
    .password-toggle {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--muted);
      transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
      color: var(--primary);
    }
    
    .position-relative {
      position: relative;
    }
    
    .btn {
      padding: 14px 32px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      transition: all 0.3s ease;
      border: none;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-secondary {
      border: 2px solid #e5e7eb;
      color: var(--dark);
      background: white;
    }
    
    .btn-outline-secondary:hover {
      background: var(--light);
      border-color: var(--muted);
    }
    
    .completion-screen {
      text-align: center;
      padding: 60px 40px;
    }
    
    .completion-icon {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, var(--success), #059669);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 24px;
      animation: scaleIn 0.5s ease;
    }
    
    .completion-icon i {
      font-size: 3rem;
      color: white;
    }
    
    @keyframes scaleIn {
      from { transform: scale(0); }
      to { transform: scale(1); }
    }
    
    .completion-screen h3 {
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 16px;
      font-size: 2rem;
    }
    
    .completion-screen p {
      color: var(--muted);
      font-size: 1.1rem;
      margin-bottom: 32px;
      line-height: 1.6;
    }
    
    .invalid-feedback {
      font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
      .card-body {
        padding: 24px;
      }
      .card-header {
        padding: 24px;
      }
    }
  </style>
</head>
<body>

<div class="enrollment-container">
  <div class="card enroll">
    <?php if ($showComplete): ?>
      <div class="card-body">
        <div class="completion-screen">
          <div class="completion-icon">
            <i class="bi bi-check-lg"></i>
          </div>
          <h3>Enrollment Submitted Successfully!</h3>
          <p>Thank you for enrolling with <strong>ACTS Learning Center</strong>. We will review your application and contact you shortly.<br><br>
          You may select your tutorial preferences after approval through the student dashboard. Please wait for further updates via email.</p>
          <a href="enrollment.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Enrollment
          </a>
          <a href="index.php" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-house-fill"></i> Back to Home
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="card-header">
        <h3><i class="bi bi-mortarboard-fill"></i> Student Enrollment</h3>
        <p>Complete the form below to enroll in ACTS Learning Center</p>
      </div>

      <div class="card-body">
        <div class="info-box">
          <i class="bi bi-info-circle-fill"></i>
          <p><strong>Note:</strong> After approval, you'll be able to select your tutorial preferences through the student dashboard.</p>
        </div>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form id="enrollmentForm" class="needs-validation" novalidate method="POST" action="enrollment_process.php">
          
          <!-- Student Information -->
          <div class="form-section">
            <h5 class="section-title">
              <i class="bi bi-person-fill"></i>
              Student Information
            </h5>

            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="student_name" class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" id="student_name" name="student_name" class="form-control" 
                       
                       value="<?= htmlspecialchars($prefill['student_name']) ?>">
                <div class="invalid-feedback">Please enter the student's full name.</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="birthday" class="form-label">Date of Birth <span class="required">*</span></label>
                <input type="date" id="birthday" name="birthday" class="form-control" required 
                       value="<?= htmlspecialchars($prefill['birthday']) ?>">
                <div class="invalid-feedback">Please provide the birth date.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                <select id="gender" name="gender" class="form-select" required>
                  <option value="">Select Gender</option>
                  <option value="Male" <?= $prefill['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= $prefill['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
                <div class="invalid-feedback">Please select gender.</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="contact" class="form-label">Contact Number</label>
                <input type="tel" id="contact" name="contact" class="form-control" 
                       placeholder="+63 912 345 6789"
                       value="<?= htmlspecialchars($prefill['contact']) ?>">
              </div>


            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="address" class="form-label">Complete Address <span class="required">*</span></label>
                <textarea id="address" name="address" class="form-control" rows="3"
                          placeholder="House No., Street, Barangay, City, Province" 
                          required><?= htmlspecialchars($prefill['address']) ?></textarea>
                <div class="invalid-feedback">Please provide the complete address.</div>
              </div>
            </div>
          </div>

          <!-- Parent/Guardian Information -->
          <div class="form-section">
            <h5 class="section-title">
              <i class="bi bi-people-fill"></i>
              Parent/Guardian Information
            </h5>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="parents_name" class="form-label">Parent/Guardian Name <span class="required">*</span></label>
                <input type="text" id="parents_name" name="parents_name" class="form-control" 
                      
                       value="<?= htmlspecialchars($prefill['parents_name']) ?>">
                <div class="invalid-feedback">Please enter parent/guardian's name.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="parents_contact" class="form-label">Parent/Guardian Contact <span class="required">*</span></label>
                <input type="tel" id="parents_contact" name="parents_contact" class="form-control" 
                       placeholder="+63 912 345 6789" required 
                       value="<?= htmlspecialchars($prefill['parents_contact']) ?>">
                <div class="invalid-feedback">Please enter a contact phone number.</div>
              </div>
            </div>
          </div>

          <!-- Account Security -->
          <div class="form-section">
            <h5 class="section-title">
              <i class="bi bi-shield-lock-fill"></i>
              Account Security
            </h5>

            
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="student@example.com" required
                       value="<?= htmlspecialchars($prefill['email']) ?>">
                <div class="invalid-feedback">Please enter a valid email.</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <div class="position-relative">
                  <input type="password" id="password" name="password" class="form-control" 
                         placeholder="Create a secure password" required minlength="8">
                  <i class="bi bi-eye password-toggle" onclick="togglePassword('password')"></i>
                </div>
                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                <div class="position-relative">
                  <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                         placeholder="Re-enter your password" required minlength="8">
                  <i class="bi bi-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                </div>
                <div class="invalid-feedback" id="confirm_password_feedback">Passwords must match.</div>
              </div>
            </div>

            <div class="password-requirements">
              <strong><i class="bi bi-shield-check"></i> Password Requirements:</strong>
              <ul>
                <li>At least 8 characters long</li>
                <li>Include a mix of letters and numbers</li>
                <li>Use a unique password you haven't used elsewhere</li>
              </ul>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="form-check mb-4 p-3" style="background: var(--light); border-radius: 12px;">
            <input class="form-check-input" type="checkbox" id="confirmAgree" required>
            <label class="form-check-label fw-semibold" for="confirmAgree">
              I confirm that all information provided is accurate and complete. I agree to ACTS Learning Center's enrollment terms and conditions.
            </label>
            <div class="invalid-feedback">You must confirm before submitting.</div>
          </div>

          <!-- Submit Button -->
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle"></i> Submit Enrollment
            </button>
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left"></i> Back to Home
            </a>
          </div>

        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password toggle function
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const icon = field.nextElementSibling;
  
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

// Password validation
function validatePasswords() {
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const feedback = document.getElementById('confirm_password_feedback');
  
  if (password.value !== confirmPassword.value) {
    confirmPassword.setCustomValidity('Passwords do not match');
    feedback.textContent = 'Passwords must match.';
    return false;
  } else {
    confirmPassword.setCustomValidity('');
    return true;
  }
}

// Add event listeners for password validation
document.getElementById('password').addEventListener('input', validatePasswords);
document.getElementById('confirm_password').addEventListener('input', validatePasswords);

// Form validation
(function() {
  'use strict';
  const form = document.getElementById('enrollmentForm');
  
  form.addEventListener('submit', function(event) {
    if (!form.checkValidity() || !validatePasswords()) {
      event.preventDefault();
      event.stopPropagation();
    }
    
    form.classList.add('was-validated');
  }, false);
})();
</script>

</body>
</html>