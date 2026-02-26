<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $parents_name = mysqli_real_escape_string($conn, $_POST['parents_name']);
    $parents_contact = mysqli_real_escape_string($conn, $_POST['parents_contact']);
    $grade_level = mysqli_real_escape_string($conn, $_POST['grade_level']);
    
    // Tutorial selections
    $subject_tutorials = isset($_POST['subject_tutorials']) ? implode(', ', $_POST['subject_tutorials']) : '';
    $computer_tutorials = isset($_POST['computer_tutorials']) ? implode(', ', $_POST['computer_tutorials']) : '';
    
    $sessions_per_week = intval($_POST['sessions_per_week']);
    $schedule_preference = mysqli_real_escape_string($conn, $_POST['schedule_preference']);
    
    // Check if email already exists
    $email_check = "SELECT id FROM users WHERE email = ?";
    $email_stmt = $conn->prepare($email_check);
    $email_stmt->bind_param("s", $email);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();
    
    if ($email_result->num_rows > 0) {
        $error_message = "This email address is already registered.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate QR code (simple format: ACTS-EMAIL)
        $qr_code = "ACTS-" . strtoupper(substr($email, 0, strpos($email, '@')));
        
        // Insert new student
        $insert_sql = "INSERT INTO users (
            student_name, 
            email, 
            password, 
            contact, 
            gender, 
            birthday, 
            address, 
            parents_name, 
            parents_contact, 
            grade_level,
            subject_tutorials, 
            computer_tutorials, 
            sessions_per_week, 
            schedule_preference, 
            qr_code,
            status,
            user_type,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved', 'student', NOW())";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssssssssiss", 
            $student_name, 
            $email, 
            $hashed_password, 
            $contact, 
            $gender, 
            $birthday, 
            $address, 
            $parents_name, 
            $parents_contact,
            $grade_level,
            $subject_tutorials, 
            $computer_tutorials, 
            $sessions_per_week, 
            $schedule_preference,
            $qr_code
        );
        
        if ($insert_stmt->execute()) {
            $_SESSION['success_message'] = "Student added successfully!";
            header("Location: adminstudents.php");
            exit();
        } else {
            $error_message = "Error adding student: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
    $email_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Student | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
* {
    font-family: 'DM Sans', sans-serif;
}

body {
    background: #f8fafc;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 32px 40px;
    margin-bottom: 32px;
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-back {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
}

.form-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 3px solid #667eea;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #667eea;
}

.form-label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-label .required {
    color: #ef4444;
}

.form-control, .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

.tutorial-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    cursor: pointer;
}

.checkbox-item:hover {
    background: #f1f5f9;
    border-color: #667eea;
}

.checkbox-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    cursor: pointer;
    accent-color: #667eea;
}

.checkbox-item label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
    color: #1a202c;
}

.checkbox-item input[type="checkbox"]:checked + label {
    color: #667eea;
    font-weight: 600;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid #f1f5f9;
}

.btn-save {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
    color: white;
}

.btn-cancel {
    background: #f3f4f6;
    color: #1a202c;
    border: none;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
    color: #1a202c;
}

.alert-error {
    background: #fee2e2;
    border: 2px solid #ef4444;
    color: #991b1b;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-error i {
    font-size: 1.3rem;
}

.password-strength {
    height: 6px;
    border-radius: 3px;
    background: #e5e7eb;
    margin-top: 10px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.password-strength-weak {
    width: 33%;
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

.password-strength-medium {
    width: 66%;
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.password-strength-strong {
    width: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
}

.password-requirements {
    margin-top: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 0.85rem;
    color: #64748b;
}

.password-requirements ul {
    list-style: none;
    padding: 0;
    margin: 8px 0 0 0;
}

.password-requirements li {
    padding: 4px 0;
}

.password-requirements li i {
    color: #cbd5e1;
    margin-right: 6px;
}

@media (max-width: 768px) {
    .form-container {
        padding: 24px;
    }
    
    .page-header {
        padding: 24px;
    }
    
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .tutorial-checkboxes {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-save, .btn-cancel {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <h4><i class="fas fa-graduation-cap"></i> ACTS Admin</h4>
    <p>Learning Center Management</p>
  </div>
  
  <div class="sidebar-menu">
    <a href="admindashboard.php">
      <i class="fas fa-chart-line"></i>
      <span>Dashboard</span>
    </a>
    <a href="adminpending_students.php">
      <i class="fas fa-clock"></i>
      <span>Pending Students</span>
    </a>
    <a href="adminstudents.php" class="active">
      <i class="fas fa-user-graduate"></i>
      <span>Enrolled Students</span>
    </a>
    <a href="adminlistoftutor.php">
        <i class="fas fa-users"></i>
        <span>List of Tutors</span>
    </a>
    <a href="adminschedule.php">
      <i class="fas fa-calendar-alt"></i>
      <span>Tutorial Schedule</span>
    </a>
    <a href="adminbilling.php">
      <i class="fas fa-credit-card"></i>
      <span>Billing</span>
    </a>
    <a href="adminattendance.php">
      <i class="fas fa-clipboard-check"></i>
      <span>Attendance</span>
    </a>
    <a href="adminfeedback.php">
      <i class="fas fa-comments"></i>
      <span>Feedback</span>
    </a>
    <a href="logout.php" class="logout">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<div class="main">
  <div class="page-header">
    <div class="page-header-content">
      <h2>
        <i class="fas fa-user-plus"></i>
        Add New Student
      </h2>
      <a href="adminstudents.php" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Back to Students
      </a>
    </div>
  </div>

  <?php if (isset($error_message)): ?>
    <div class="alert-error">
      <i class="fas fa-exclamation-circle"></i>
      <span><?php echo $error_message; ?></span>
    </div>
  <?php endif; ?>

  <div class="form-container">
    <form method="POST" action="" id="addStudentForm">
      <!-- Personal Information -->
      <div class="section-title">
        <i class="fas fa-user"></i>
        Personal Information
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Student Name <span class="required">*</span></label>
          <input type="text" class="form-control" name="student_name" 
                 placeholder="Enter full name" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email Address <span class="required">*</span></label>
          <input type="email" class="form-control" name="email" 
                 placeholder="student@example.com" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Password <span class="required">*</span></label>
          <input type="password" class="form-control" name="password" id="password"
                 placeholder="Enter password" required minlength="8">
          <div class="password-strength">
            <div class="password-strength-bar" id="strengthBar"></div>
          </div>
          <div class="password-requirements">
            <strong>Password Requirements:</strong>
            <ul>
              <li><i class="fas fa-circle"></i> At least 8 characters long</li>
              <li><i class="fas fa-circle"></i> Mix of letters and numbers</li>
            </ul>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Contact Number <span class="required">*</span></label>
          <input type="text" class="form-control" name="contact" 
                 placeholder="+63 912 345 6789" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Gender</label>
          <select class="form-select" name="gender">
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Birth Date</label>
          <input type="date" class="form-control" name="birthday">
        </div>

        <div class="col-md-4">
          <label class="form-label">Grade Level</label>
          <select class="form-select" name="grade_level">
            <option value="">Select Grade Level</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
              <option value="Grade <?php echo $i; ?>">Grade <?php echo $i; ?></option>
            <?php endfor; ?>
            <option value="College">College</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2" 
                    placeholder="Enter complete address"></textarea>
        </div>
      </div>

      <!-- Parent/Guardian Information -->
      <div class="section-title">
        <i class="fas fa-users"></i>
        Parent/Guardian Information
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Parent/Guardian Name</label>
          <input type="text" class="form-control" name="parents_name" 
                 placeholder="Enter parent/guardian name">
        </div>

        <div class="col-md-6">
          <label class="form-label">Parent/Guardian Contact</label>
          <input type="text" class="form-control" name="parents_contact" 
                 placeholder="+63 912 345 6789">
        </div>
      </div>

      <!-- Tutorial Preferences -->
      <div class="section-title">
        <i class="fas fa-book"></i>
        Tutorial Preferences
      </div>

      <div class="mb-4">
        <label class="form-label">Subject Tutorials</label>
        <div class="tutorial-checkboxes">
          <?php 
          $subject_options = ['Math', 'Science', 'English', 'Filipino', 'History', 'MAPEH'];
          foreach ($subject_options as $subject): 
          ?>
            <div class="checkbox-item">
              <input type="checkbox" name="subject_tutorials[]" 
                     value="<?php echo $subject; ?>" 
                     id="subject_<?php echo $subject; ?>">
              <label for="subject_<?php echo $subject; ?>"><?php echo $subject; ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Computer Tutorials</label>
        <div class="tutorial-checkboxes">
          <?php 
          $computer_options = ['MS Word', 'MS Excel', 'MS PowerPoint', 'Programming', 'Web Design', 'Photoshop'];
          foreach ($computer_options as $computer): 
          ?>
            <div class="checkbox-item">
              <input type="checkbox" name="computer_tutorials[]" 
                     value="<?php echo $computer; ?>" 
                     id="computer_<?php echo str_replace(' ', '_', $computer); ?>">
              <label for="computer_<?php echo str_replace(' ', '_', $computer); ?>"><?php echo $computer; ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Schedule Information -->
      <div class="section-title">
        <i class="fas fa-calendar-alt"></i>
        Schedule Information
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label">Sessions Per Week <span class="required">*</span></label>
          <select class="form-select" name="sessions_per_week" required>
            <option value="">Select Sessions</option>
            <?php for ($i = 1; $i <= 7; $i++): ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?> session<?php echo $i > 1 ? 's' : ''; ?> per week</option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Schedule Preference <span class="required">*</span></label>
          <select class="form-select" name="schedule_preference" required>
            <option value="">Select Preference</option>
            <option value="self">Self-Scheduled</option>
            <option value="tutor">Tutor-Suggested Schedule</option>
          </select>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <a href="adminstudents.php" class="btn-cancel">
          <i class="fas fa-times"></i>
          Cancel
        </a>
        <button type="submit" class="btn-save">
          <i class="fas fa-save"></i>
          Add Student
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
  const password = e.target.value;
  const strengthBar = document.getElementById('strengthBar');
  
  let strength = 0;
  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;
  
  strengthBar.className = 'password-strength-bar';
  if (strength <= 1) {
    strengthBar.classList.add('password-strength-weak');
  } else if (strength <= 3) {
    strengthBar.classList.add('password-strength-medium');
  } else {
    strengthBar.classList.add('password-strength-strong');
  }
});

// Form validation
document.getElementById('addStudentForm').addEventListener('submit', function(e) {
    const studentName = document.querySelector('input[name="student_name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value;
    const contact = document.querySelector('input[name="contact"]').value.trim();
    
    if (!studentName || !email || !password || !contact) {
        e.preventDefault();
        alert('Please fill in all required fields!');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
        return false;
    }
    
    // Check if at least one tutorial is selected
    const subjectChecked = document.querySelectorAll('input[name="subject_tutorials[]"]:checked').length > 0;
    const computerChecked = document.querySelectorAll('input[name="computer_tutorials[]"]:checked').length > 0;
    
    if (!subjectChecked && !computerChecked) {
        e.preventDefault();
        alert('Please select at least one tutorial (Subject or Computer)!');
        return false;
    }
    
    return true;
});

// Checkbox animation
document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const item = this.closest('.checkbox-item');
        if (this.checked) {
            item.style.background = 'rgba(102, 126, 234, 0.1)';
            item.style.borderColor = '#667eea';
        } else {
            item.style.background = '#f8fafc';
            item.style.borderColor = '#e5e7eb';
        }
    });
});
</script>

</body>
</html>