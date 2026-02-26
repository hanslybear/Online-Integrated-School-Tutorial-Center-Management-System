<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Get student ID from URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id === 0) {
    header("Location: adminstudents.php");
    exit();
}

// Fetch student details
$sql = "SELECT * FROM users WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminstudents.php");
    exit();
}

$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $parents_name = mysqli_real_escape_string($conn, $_POST['parents_name']);
    $parents_contact = mysqli_real_escape_string($conn, $_POST['parents_contact']);
    $subject_tutorials = isset($_POST['subject_tutorials']) ? implode(', ', $_POST['subject_tutorials']) : '';
    $computer_tutorials = isset($_POST['computer_tutorials']) ? implode(', ', $_POST['computer_tutorials']) : '';
    $sessions_per_week = intval($_POST['sessions_per_week']);
    $schedule_preference = mysqli_real_escape_string($conn, $_POST['schedule_preference']);
    
    // Check if email already exists for another user
    $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $email_check_stmt = $conn->prepare($email_check_sql);
    $email_check_stmt->bind_param("si", $email, $student_id);
    $email_check_stmt->execute();
    $email_check_result = $email_check_stmt->get_result();
    
    if ($email_check_result->num_rows > 0) {
        $error_message = "This email address is already in use by another student.";
    } else {
        // Update query
        $update_sql = "UPDATE users SET 
                        student_name = ?,
                        email = ?,
                        contact = ?,
                        gender = ?,
                        address = ?,
                        birthday = ?,
                        parents_name = ?,
                        parents_contact = ?,
                        subject_tutorials = ?,
                        computer_tutorials = ?,
                        sessions_per_week = ?,
                        schedule_preference = ?
                       WHERE id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssssssisi", 
            $student_name, 
            $email, 
            $contact, 
            $gender, 
            $address, 
            $birthday, 
            $parents_name, 
            $parents_contact,
            $subject_tutorials,
            $computer_tutorials,
            $sessions_per_week,
            $schedule_preference,
            $student_id
        );
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Student profile updated successfully!";
            header("Location: adminview_student.php?id=" . $student_id);
            exit();
        } else {
            $error_message = "Failed to update student profile. Please try again.";
        }
        $update_stmt->close();
    }
    $email_check_stmt->close();
}

// Tutorial options
$subject_options = ['Math', 'Science', 'English', 'Filipino', 'History', 'MAPEH'];
$computer_options = ['MS Word', 'MS Excel', 'MS PowerPoint', 'Programming', 'Web Design', 'Photoshop'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student - <?php echo htmlspecialchars($student['student_name']); ?> | ACTS Admin</title>
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

.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 40px;
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
}

.page-header h2 {
  font-family: 'Playfair Display', serif;
  font-weight: 800;
  margin: 0 0 8px 0;
  font-size: 2.5rem;
}

.page-header p {
  font-size: 1.1rem;
  opacity: 0.95;
  margin: 0;
}

.btn-back {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 24px;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-back:hover {
  background: white;
  color: #667eea;
  border-color: white;
  transform: translateX(-4px);
}

.form-section {
  background: white;
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 24px;
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
  border-bottom: 2px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-title i {
  color: #667eea;
  font-size: 1.4rem;
}

.form-label {
  font-weight: 600;
  color: #1a202c;
  margin-bottom: 8px;
  font-size: 0.95rem;
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
}

.checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.form-check {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
  border: 2px solid rgba(102, 126, 234, 0.2);
  border-radius: 10px;
  padding: 12px 16px;
  margin: 0;
  transition: all 0.3s ease;
}

.form-check:hover {
  border-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.form-check-input {
  width: 1.2rem;
  height: 1.2rem;
  border: 2px solid #667eea;
  margin-top: 0;
}

.form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}

.form-check-label {
  font-weight: 600;
  color: #1a202c;
  margin-left: 8px;
  cursor: pointer;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 24px;
  border-top: 2px solid #f1f5f9;
  margin-top: 32px;
}

.btn-save {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
  font-size: 1rem;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}

.btn-cancel {
  background: #f1f5f9;
  color: #64748b;
  border: none;
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
  font-size: 1rem;
  text-decoration: none;
}

.btn-cancel:hover {
  background: #e2e8f0;
  color: #475569;
}

.alert {
  border-radius: 12px;
  border: none;
  padding: 16px 20px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
}

.alert-danger {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
  border-left: 4px solid #ef4444;
  color: #991b1b;
}

.alert i {
  font-size: 1.2rem;
}

.required {
  color: #ef4444;
  font-weight: 700;
}

@media (max-width: 768px) {
  .page-header {
    padding: 28px 24px;
  }
  
  .page-header h2 {
    font-size: 2rem;
  }
  
  .form-section {
    padding: 24px 20px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-save, .btn-cancel {
    width: 100%;
    justify-content: center;
  }
  
  .checkbox-group {
    grid-template-columns: 1fr;
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
  <a href="adminstudent_profile.php?id=<?php echo $student_id; ?>" class="btn-back">
    <i class="fas fa-arrow-left"></i>
    Back to Profile
  </a>

  <div class="page-header">
    <div class="page-header-content">
      <h2><i class="fas fa-user-edit"></i> Edit Student Profile</h2>
      <p>Update information for <?php echo htmlspecialchars($student['student_name']); ?></p>
    </div>
  </div>

  <?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      <?php echo $error_message; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <!-- Personal Information -->
    <div class="form-section">
      <h3 class="section-title">
        <i class="fas fa-user"></i>
        Personal Information
      </h3>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" name="student_name" class="form-control" value="<?php echo htmlspecialchars($student['student_name']); ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email Address <span class="required">*</span></label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Contact Number <span class="required">*</span></label>
          <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($student['contact']); ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Gender <span class="required">*</span></label>
          <select name="gender" class="form-select" required>
            <option value="">Select Gender</option>
            <option value="Male" <?php echo $student['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo $student['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?php echo $student['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="birthday" class="form-control" value="<?php echo $student['birthday']; ?>">
        </div>

        <div class="col-md-12">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
        </div>
      </div>
    </div>

    <!-- Parent/Guardian Information -->
    <div class="form-section">
      <h3 class="section-title">
        <i class="fas fa-user-friends"></i>
        Parent/Guardian Information
      </h3>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Parent/Guardian Name</label>
          <input type="text" name="parents_name" class="form-control" value="<?php echo htmlspecialchars($student['parents_name']); ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Parent/Guardian Contact</label>
          <input type="text" name="parents_contact" class="form-control" value="<?php echo htmlspecialchars($student['parents_contact']); ?>">
        </div>
      </div>
    </div>

    <!-- Tutorial Enrollment -->
    <div class="form-section">
      <h3 class="section-title">
        <i class="fas fa-book-reader"></i>
        Tutorial Enrollment
      </h3>

      <div class="mb-4">
        <label class="form-label">Subject Tutorials</label>
        <div class="checkbox-group">
          <?php 
            $selected_subjects = !empty($student['subject_tutorials']) ? explode(', ', $student['subject_tutorials']) : [];
            foreach ($subject_options as $subject): 
          ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="subject_tutorials[]" value="<?php echo $subject; ?>" id="subject_<?php echo $subject; ?>" <?php echo in_array($subject, $selected_subjects) ? 'checked' : ''; ?>>
              <label class="form-check-label" for="subject_<?php echo $subject; ?>">
                <?php echo $subject; ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Computer Tutorials</label>
        <div class="checkbox-group">
          <?php 
            $selected_computers = !empty($student['computer_tutorials']) ? explode(', ', $student['computer_tutorials']) : [];
            foreach ($computer_options as $computer): 
          ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="computer_tutorials[]" value="<?php echo $computer; ?>" id="computer_<?php echo str_replace(' ', '_', $computer); ?>" <?php echo in_array($computer, $selected_computers) ? 'checked' : ''; ?>>
              <label class="form-check-label" for="computer_<?php echo str_replace(' ', '_', $computer); ?>">
                <?php echo $computer; ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Sessions Per Week</label>
          <select name="sessions_per_week" class="form-select">
            <option value="1" <?php echo $student['sessions_per_week'] == 1 ? 'selected' : ''; ?>>1 session</option>
            <option value="2" <?php echo $student['sessions_per_week'] == 2 ? 'selected' : ''; ?>>2 sessions</option>
            <option value="3" <?php echo $student['sessions_per_week'] == 3 ? 'selected' : ''; ?>>3 sessions</option>
            <option value="4" <?php echo $student['sessions_per_week'] == 4 ? 'selected' : ''; ?>>4 sessions</option>
            <option value="5" <?php echo $student['sessions_per_week'] == 5 ? 'selected' : ''; ?>>5 sessions</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Schedule Preference</label>
          <select name="schedule_preference" class="form-select">
            <option value="self" <?php echo $student['schedule_preference'] === 'self' ? 'selected' : ''; ?>>Self Schedule</option>
            <option value="tutor" <?php echo $student['schedule_preference'] === 'tutor' ? 'selected' : ''; ?>>Tutor Schedule</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <a href="adminview_student.php?id=<?php echo $student_id; ?>" class="btn-cancel">
        <i class="fas fa-times"></i>
        Cancel
      </a>
      <button type="submit" class="btn-save">
        <i class="fas fa-save"></i>
        Save Changes
      </button>
    </div>
  </form>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>