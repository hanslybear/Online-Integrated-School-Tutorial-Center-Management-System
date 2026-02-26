<!--Student Dashboard Page-->
<?php
session_start();
include "db_connect.php";

$student_id = $_SESSION['student_id'];

// Get student data
$sql = "SELECT student_name, email, subject_tutorials, computer_tutorials, sessions_per_week, schedule_preference
        FROM users 
        WHERE id = ? AND status = 'approved'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: homepage.php?error=Access denied");
    exit();
}

$student = $result->fetch_assoc();
$student_name  = $student['student_name'];
$student_email = $student['email'];
$subject_tutorials = $student['subject_tutorials'];
$computer_tutorials = $student['computer_tutorials'];
$sessions_per_week = $student['sessions_per_week'];
$schedule_preference = $student['schedule_preference'];

// Check if student has enrolled in tutorials
$has_enrolled = !empty($subject_tutorials) || !empty($computer_tutorials);
  
// Fetch schedules only for this specific student
$schedules = null;

if ($has_enrolled) {
    // Query to get schedules where this student is enrolled, including tutor information
    $schedule_sql = "SELECT cs.*, u.student_name, t.tutor_name, t.profile_image as tutor_image
                     FROM class_schedules cs
                     LEFT JOIN users u ON cs.students_enrolled = u.id
                     LEFT JOIN tutors t ON cs.tutor_id = t.id
                     WHERE cs.students_enrolled = ?
                     ORDER BY cs.schedule_date ASC, cs.start_time ASC";
    
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $schedules = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
 <link rel="icon" type="image/png" href="actslogo.png">
<style>
.program-selection-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 40px;
  margin-bottom: 32px;
  color: white;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
}

.program-selection-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.program-selection-banner::after {
  content: '';
  position: absolute;
  bottom: -30%;
  left: -5%;
  width: 300px;
  height: 300px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
}

.banner-content {
  position: relative;
  z-index: 1;
}

.banner-content h3 {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 12px;
}

.banner-content p {
  font-size: 1.1rem;
  opacity: 0.95;
  margin-bottom: 24px;
}

.btn-choose-program {
  background: white;
  color: #667eea;
  border: none;
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.btn-choose-program:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  background: #f8f9ff;
  color: #5a67d8;
}

/* QR Code Quick Access Card */
.qr-quick-access {
  background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 32px;
  color: white;
  box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3);
  position: relative;
  overflow: hidden;
}

.qr-quick-access::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.qr-quick-access-content {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.qr-quick-access-text h4 {
  color: white;
  font-weight: 700;
  margin-bottom: 8px;
  font-size: 1.3rem;
}

.qr-quick-access-text p {
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  font-size: 1rem;
}

.btn-view-qr {
  background: white;
  color: #8b5cf6;
  border: none;
  padding: 12px 28px;
  border-radius: 12px;
  font-weight: 700;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  text-decoration: none;
}

.btn-view-qr:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  background: #f8f9ff;
  color: #7c3aed;
}

.tutorial-step {
  margin-bottom: 32px;
}

.step-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.step-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.step-subtitle {
  font-size: 0.9rem;
  color: #6B7280;
  margin-left: 52px;
  margin-top: 4px;
}

.tutorial-options {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
  margin-left: 52px;
}

.tutorial-option {
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.tutorial-option:hover {
  transform: translateY(-4px);
  border-color: #667eea;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
}

.tutorial-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.tutorial-option.selected::after {
  content: '\f00c';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
}

.tutorial-icon {
  font-size: 2.5rem;
  margin-bottom: 12px;
  color: #667eea;
}

.tutorial-name {
  font-weight: 600;
  color: #1a202c;
  font-size: 1rem;
}

.session-selector {
  display: flex;
  gap: 12px;
  justify-content: flex-start;
  flex-wrap: wrap;
  margin-left: 52px;
}

.session-btn {
  padding: 12px 24px;
  border: 2px solid #E5E7EB;
  background: white;
  border-radius: 10px;
  font-weight: 600;
  color: #4a5568;
  transition: all 0.3s ease;
  cursor: pointer;
  min-width: 80px;
}

.session-btn:hover {
  border-color: #667eea;
  background: #f8f9ff;
  color: #667eea;
}

.session-btn.selected {
  border-color: #667eea;
  background: #667eea;
  color: white;
}

.schedule-preference-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-left: 52px;
}

.preference-card {
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 12px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.preference-card:hover {
  transform: translateY(-4px);
  border-color: #667eea;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
}

.preference-card.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.preference-card.selected::after {
  content: '\f00c';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  top: 16px;
  right: 16px;
  width: 28px;
  height: 28px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.preference-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  font-size: 1.8rem;
  color: #667eea;
}

.preference-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 8px;
}

.preference-desc {
  color: #6B7280;
  font-size: 0.9rem;
  line-height: 1.5;
}

.enrolled-badge {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  padding: 16px 24px;
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.enrolled-badge h5 {
  color: white;
  margin-bottom: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

.enrolled-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 12px;
}

.enrolled-item {
  background: rgba(255, 255, 255, 0.2);
  padding: 12px;
  border-radius: 8px;
}

.enrolled-item-label {
  font-size: 0.85rem;
  opacity: 0.9;
  margin-bottom: 4px;
}

.enrolled-item-value {
  font-weight: 700;
  font-size: 1.1rem;
}

.divider {
  height: 2px;
  background: linear-gradient(90deg, transparent, #E5E7EB, transparent);
  margin: 24px 0;
}

.selection-counter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(102, 126, 234, 0.1);
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #667eea;
  font-weight: 600;
}

/* Enhanced Schedule Item Styles */
.schedule-item {
  background: white;
  border-left: 4px solid #667eea;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.schedule-item:hover {
  transform: translateX(8px);
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2);
}

.schedule-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}

.schedule-date-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 0.9rem;
}

.schedule-time {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6B7280;
  font-weight: 600;
  font-size: 0.95rem;
}

.schedule-body {
  margin-bottom: 12px;
}

.schedule-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 12px;
}

.schedule-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.schedule-student-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.schedule-tutor-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 16px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  border-radius: 10px;
}

.tutor-avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  object-fit: cover;
}

.tutor-avatar-small.placeholder {
  background: linear-gradient(135deg, #ec4899, #8b5cf6);
}

.student-avatar-small {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.student-details {
  display: flex;
  flex-direction: column;
}

.student-name {
  font-weight: 700;
  color: #1a202c;
  font-size: 0.95rem;
}

.student-label {
  font-size: 0.8rem;
  color: #6B7280;
}

/* Modal Styles */
.modal-content {
  border: none;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px 20px 0 0;
  padding: 24px 32px;
  border: none;
}

.modal-title {
  font-weight: 700;
  font-size: 1.5rem;
}

.btn-close {
  filter: brightness(0) invert(1);
}

.modal-body {
  padding: 32px;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 20px 32px;
  border-top: 2px solid #E5E7EB;
}

.btn-confirm-enrollment {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 12px 32px;
  border-radius: 10px;
  font-weight: 700;
  transition: all 0.3s ease;
}

.btn-confirm-enrollment:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-confirm-enrollment:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}



</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="student-profile">
      <div class="student-avatar">
        <?php echo strtoupper(substr($student_name, 0, 1)); ?>
      </div>
      <div class="student-info">
        <h4><?php echo htmlspecialchars($student_name); ?></h4>
        <p><?php echo htmlspecialchars($student_email); ?></p>
      </div>
    </div>
  </div>
  
  <div class="sidebar-menu">
    <a href="studentdashboard.php" class="active">
      <i class="fas fa-home"></i>
      <span>Dashboard</span>
    </a>
    <a href="student_myclass.php">
      <i class="fas fa-book-open"></i>
      <span>My Classes</span>
    </a>
    <a href="student_tutorprofile.php">
  <i class="fas fa-chalkboard-teacher"></i>
  <span>List of Tutor</span>
</a>
    <a href="student_attendance.php">
      <i class="fas fa-clipboard-check"></i>
      <span>Attendance</span>
    </a>
    <a href="student_billing.php">
      <i class="fas fa-file-invoice-dollar"></i>
      <span>Billing</span>
    </a>
    <a href="student_profile.php">
      <i class="fas fa-user-circle"></i>
      <span>My Profile</span>
    </a>
    <a href="student_feedback.php">
      <i class="fas fa-comment-dots"></i>
      <span>Feedback & Reports</span>
    </a>
    <a href="logout.php" class="logout">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<div class="main">
  
  <!-- QR CODE QUICK ACCESS CARD -->
  <div class="qr-quick-access">
    <div class="qr-quick-access-content">
      <div class="qr-quick-access-text">
        <h4><i class="fas fa-qrcode"></i> Attendance QR Code</h4>
        <p>Use your QR code for quick attendance check-in</p>
      </div>
      <a href="student_qr_display.php" class="btn-view-qr">
        <i class="fas fa-eye"></i>
        View My QR Code
      </a>
    </div>
  </div>
  
  <?php if (!$has_enrolled): ?>
    <!-- Tutorial Selection Banner -->
    <div class="program-selection-banner">
      <div class="banner-content">
        <h3><i class="fas fa-graduation-cap"></i> Choose Your Tutorial Services</h3>
        <p>Select one or more tutorial services to get started with personalized learning</p>
        <button class="btn-choose-program" data-bs-toggle="modal" data-bs-target="#tutorialModal">
          <i class="fas fa-plus-circle"></i>
          Enroll in Tutorial Services
        </button>
      </div>
    </div>
  <?php else: ?>
    <!-- Enrolled Tutorials Display -->
    <div class="enrolled-badge">
      <h5>
        <i class="fas fa-check-circle"></i>
        Your Tutorial Enrollment
      </h5>
      <div class="enrolled-details">
        <?php if (!empty($subject_tutorials)): ?>
          <div class="enrolled-item">
            <div class="enrolled-item-label">Subject Tutorials</div>
            <div class="enrolled-item-value"><?php echo htmlspecialchars($subject_tutorials); ?></div>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($computer_tutorials)): ?>
          <div class="enrolled-item">
            <div class="enrolled-item-label">Computer Tutorials</div>
            <div class="enrolled-item-value"><?php echo htmlspecialchars($computer_tutorials); ?></div>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($sessions_per_week)): ?>
          <div class="enrolled-item">
            <div class="enrolled-item-label">Sessions Per Week</div>
            <div class="enrolled-item-value"><?php echo htmlspecialchars($sessions_per_week); ?> sessions</div>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($schedule_preference)): ?>
          <div class="enrolled-item">
            <div class="enrolled-item-label">Schedule Type</div>
            <div class="enrolled-item-value"><?php echo $schedule_preference === 'self' ? 'Self-Scheduled' : 'Tutor-Suggested'; ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="quick-stats">
    <div class="stat-card primary">
      <div class="stat-icon">
        <i class="fas fa-book"></i>
      </div>
      <div class="stat-label">Active Tutorials</div>
      <div class="stat-value">2</div>
    </div>

    
    <div class="stat-card warning">
      <div class="stat-icon">
        <i class="fas fa-tasks"></i>
      </div>
      <div class="stat-label">Pending Tasks</div>
      <div class="stat-value">4</div>
    </div>
    
    <div class="stat-card info">
      <div class="stat-icon">
        <i class="fas fa-award"></i>
      </div>
      <div class="stat-label">Overall Progress</div>
      <div class="stat-value">85%</div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="content-card">
        <h5><i class="fas fa-calendar-day"></i> Tutorial Schedule Dashboard</h5>

        <?php if ($schedules && $schedules->num_rows > 0): ?>
          <?php while ($row = $schedules->fetch_assoc()): 
            $formatted_date = date('l, F j, Y', strtotime($row['schedule_date']));
            $student_initial = strtoupper(substr($row['student_name'], 0, 1));
            $tutor_name = isset($row['tutor_name']) ? $row['tutor_name'] : 'Not Assigned';
            $tutor_initial = $tutor_name !== 'Not Assigned' ? strtoupper(substr($tutor_name, 0, 1)) : 'T';
            $tutor_image = isset($row['tutor_image']) ? $row['tutor_image'] : null;
          ?>
            <div class="schedule-item">
              <!-- Schedule Header with Date and Time -->
              <div class="schedule-header">
                <span class="schedule-date-badge">
                  <i class="fas fa-calendar"></i>
                  <?php echo $formatted_date; ?>
                </span>
                <div class="schedule-time">
                  <i class="fas fa-clock"></i>
                  <?php echo date("h:i A", strtotime($row['start_time'])) ?>
                  -
                  <?php echo date("h:i A", strtotime($row['end_time'])) ?>
                </div>
              </div>
              
              <!-- Schedule Body with Subject -->
              <div class="schedule-body">
                <div class="schedule-title">
                  <?php echo htmlspecialchars($row['subject']); ?>
                </div>
              </div>
              
              <!-- Schedule Footer with Student and Tutor Info -->
              <div class="schedule-footer">
                <div class="schedule-student-info">
                  <div class="student-avatar-small">
                    <?php echo $student_initial; ?>
                  </div>
                  <div class="student-details">
                    <span class="student-label">Student</span>
                    <span class="student-name"><?php echo htmlspecialchars($row['student_name']); ?></span>
                  </div>
                </div>
                
                <!-- Tutor Information -->
                <div class="schedule-tutor-info">
                  <?php if ($tutor_image && file_exists($tutor_image)): ?>
                    <img src="<?php echo htmlspecialchars($tutor_image); ?>" 
                         alt="<?php echo htmlspecialchars($tutor_name); ?>" 
                         class="tutor-avatar-small">
                  <?php else: ?>
                    <div class="tutor-avatar-small placeholder">
                      <?php echo $tutor_initial; ?>
                    </div>
                  <?php endif; ?>
                  <div class="student-details">
                    <span class="student-label">Tutor</span>
                    <span class="student-name"><?php echo htmlspecialchars($tutor_name); ?></span>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-muted">
            <?php echo $has_enrolled ? 'No schedules available for your enrolled tutorials yet.' : 'Please enroll in tutorials to view your schedule.'; ?>
          </p>
        <?php endif; ?>

      </div>

    

    </div>

    <div class="col-lg-4">
      <div class="content-card">
        <h5><i class="fas fa-bullhorn"></i> Recent Announcements</h5>
        <!-- announcements here -->
      </div>
    </div>

  </div>
</div>

<!-- Tutorial Selection Modal -->
<div class="modal fade" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tutorialModalLabel">
          <i class="fas fa-graduation-cap"></i> Enroll in Tutorial Services
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="tutorialEnrollmentForm" method="POST" action="save_tutorial_enrollment.php">
          
          <!-- Step 1: Subject Tutorials -->
          <div class="tutorial-step">
            <div class="step-header">
              <div class="step-number">1</div>
              <h6 class="step-title">Subject Tutorials</h6>
            </div>
            <p class="step-subtitle">Select the subjects you need help with (optional)</p>
            <div class="step-subtitle mb-3">
              <span class="selection-counter">
                <i class="fas fa-check-circle"></i>
                Selected: <span id="subjectCount">0</span>
              </span>
            </div>
            
            <div class="tutorial-options">
              <div class="tutorial-option" onclick="toggleSubject(this, 'English')">
                <div class="tutorial-icon">📚</div>
                <div class="tutorial-name">English</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleSubject(this, 'Math')">
                <div class="tutorial-icon">📐</div>
                <div class="tutorial-name">Math</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleSubject(this, 'Filipino')">
                <div class="tutorial-icon">🇵🇭</div>
                <div class="tutorial-name">Filipino</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleSubject(this, 'Science')">
                <div class="tutorial-icon">🔬</div>
                <div class="tutorial-name">Science</div>
              </div>
            </div>
            
            <input type="hidden" name="subject_tutorials" id="selectedSubjects">
          </div>

          <div class="divider"></div>

          <!-- Step 2: Computer Tutorials -->
          <div class="tutorial-step">
            <div class="step-header">
              <div class="step-number">2</div>
              <h6 class="step-title">Computer Tutorials</h6>
            </div>
            <p class="step-subtitle">Choose computer skills to learn (optional)</p>
            <div class="step-subtitle mb-3">
              <span class="selection-counter">
                <i class="fas fa-check-circle"></i>
                Selected: <span id="computerCount">0</span>
              </span>
            </div>
            
            <div class="tutorial-options">
              <div class="tutorial-option" onclick="toggleComputer(this, 'Java')">
                <div class="tutorial-icon">☕</div>
                <div class="tutorial-name">Java</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'C++')">
                <div class="tutorial-icon">💻</div>
                <div class="tutorial-name">C++</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'Python')">
                <div class="tutorial-icon">🐍</div>
                <div class="tutorial-name">Python</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'Photo Editing')">
                <div class="tutorial-icon">🎨</div>
                <div class="tutorial-name">Photo Editing</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'Video Editing')">
                <div class="tutorial-icon">🎬</div>
                <div class="tutorial-name">Video Editing</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'MS Word')">
                <div class="tutorial-icon">📝</div>
                <div class="tutorial-name">MS Word</div>
              </div>
              
              <div class="tutorial-option" onclick="toggleComputer(this, 'MS Excel')">
                <div class="tutorial-icon">📊</div>
                <div class="tutorial-name">MS Excel</div>
              </div>
            </div>
            
            <input type="hidden" name="computer_tutorials" id="selectedComputers">
          </div>

          <div class="divider"></div>

          <!-- Step 3: Sessions Per Week -->
          <div class="tutorial-step">
            <div class="step-header">
              <div class="step-number">3</div>
              <h6 class="step-title">Sessions Per Week</h6>
            </div>
            <p class="step-subtitle">How many sessions do you want per week?</p>
            
            <div class="session-selector">
              <button type="button" class="session-btn" onclick="selectSessions(this, '1')">1 session</button>
              <button type="button" class="session-btn" onclick="selectSessions(this, '2')">2 sessions</button>
              <button type="button" class="session-btn" onclick="selectSessions(this, '3')">3 sessions</button>
              <button type="button" class="session-btn" onclick="selectSessions(this, '4')">4 sessions</button>
              <button type="button" class="session-btn" onclick="selectSessions(this, '5+')">5+ sessions</button>
            </div>
            
            <input type="hidden" name="sessions_per_week" id="selectedSessions">
          </div>

          <div class="divider"></div>

          <!-- Step 4: Schedule Preference -->
          <div class="tutorial-step">
            <div class="step-header">
              <div class="step-number">4</div>
              <h6 class="step-title">Schedule Preference</h6>
            </div>
            <p class="step-subtitle">How would you like to schedule your sessions?</p>
            
            <div class="schedule-preference-options">
              <div class="preference-card" onclick="selectPreference(this, 'self')">
                <div class="preference-icon">
                  <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="preference-title">Self-Scheduled</div>
                <div class="preference-desc">
                  Choose your own schedule based on available time slots. Perfect for students who want flexibility.
                </div>
              </div>
              
              <div class="preference-card" onclick="selectPreference(this, 'tutor')">
                <div class="preference-icon">
                  <i class="fas fa-user-clock"></i>
                </div>
                <div class="preference-title">Tutor-Suggested</div>
                <div class="preference-desc">
                  Let our tutors create an optimized schedule for you based on your learning needs and availability.
                </div>
              </div>
            </div>
            
            <input type="hidden" name="schedule_preference" id="selectedPreference">
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-confirm-enrollment" id="confirmBtn" disabled onclick="submitEnrollment()">
          <i class="fas fa-check"></i> Confirm Enrollment
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
// Tutorial selection variables
let selectedSubjects = [];
let selectedComputers = [];
let selectedSessionsValue = '';
let selectedPreferenceValue = '';

function toggleSubject(element, subject) {
  element.classList.toggle('selected');
  
  if (selectedSubjects.includes(subject)) {
    selectedSubjects = selectedSubjects.filter(s => s !== subject);
  } else {
    selectedSubjects.push(subject);
  }
  
  document.getElementById('selectedSubjects').value = selectedSubjects.join(', ');
  document.getElementById('subjectCount').textContent = selectedSubjects.length;
  checkAllSelections();
}

function toggleComputer(element, computer) {
  element.classList.toggle('selected');
  
  if (selectedComputers.includes(computer)) {
    selectedComputers = selectedComputers.filter(c => c !== computer);
  } else {
    selectedComputers.push(computer);
  }
  
  document.getElementById('selectedComputers').value = selectedComputers.join(', ');
  document.getElementById('computerCount').textContent = selectedComputers.length;
  checkAllSelections();
}

function selectSessions(btn, sessions) {
  document.querySelectorAll('.session-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  selectedSessionsValue = sessions;
  document.getElementById('selectedSessions').value = sessions;
  checkAllSelections();
}

function selectPreference(card, preference) {
  document.querySelectorAll('.preference-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
  selectedPreferenceValue = preference;
  document.getElementById('selectedPreference').value = preference;
  checkAllSelections();
}

function checkAllSelections() {
  const confirmBtn = document.getElementById('confirmBtn');
  const hasAtLeastOneTutorial = selectedSubjects.length > 0 || selectedComputers.length > 0;
  
  if (hasAtLeastOneTutorial && selectedSessionsValue && selectedPreferenceValue) {
    confirmBtn.disabled = false;
  } else {
    confirmBtn.disabled = true;
  }
}

function submitEnrollment() {
  const form = document.getElementById('tutorialEnrollmentForm');
  
  // Validate that at least one tutorial is selected
  if (selectedSubjects.length === 0 && selectedComputers.length === 0) {
    alert('Please select at least one tutorial service.');
    return;
  }
  
  // Validate sessions per week
  if (!selectedSessionsValue) {
    alert('Please select the number of sessions per week.');
    return;
  }
  
  // Validate schedule preference
  if (!selectedPreferenceValue) {
    alert('Please select your schedule preference.');
    return;
  }
  
  // Submit the form
  form.submit();
}

// Reset form when modal is closed
document.getElementById('tutorialModal').addEventListener('hidden.bs.modal', function () {
  // Reset selections
  selectedSubjects = [];
  selectedComputers = [];
  selectedSessionsValue = '';
  selectedPreferenceValue = '';
  
  // Reset UI
  document.querySelectorAll('.tutorial-option').forEach(el => el.classList.remove('selected'));
  document.querySelectorAll('.session-btn').forEach(el => el.classList.remove('selected'));
  document.querySelectorAll('.preference-card').forEach(el => el.classList.remove('selected'));
  
  // Reset counters
  document.getElementById('subjectCount').textContent = '0';
  document.getElementById('computerCount').textContent = '0';
  
  // Reset hidden inputs
  document.getElementById('selectedSubjects').value = '';
  document.getElementById('selectedComputers').value = '';
  document.getElementById('selectedSessions').value = '';
  document.getElementById('selectedPreference').value = '';
  
  // Disable confirm button
  document.getElementById('confirmBtn').disabled = true;
});
</script>

</body>
</html>