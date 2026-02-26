<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Get student ID from URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch student details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminpending_students.php?error=Student not found");
    exit();
}

$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Details | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
.page-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  padding: 24px 32px;
  border-radius: 20px;
  margin-bottom: 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h2 {
  font-weight: 700;
  color: var(--dark);
  margin: 0;
  font-size: 1.75rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-header h2 i {
  color: var(--primary);
}

.btn-back {
  background: linear-gradient(135deg, #6B7280, #4B5563);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}

.btn-back:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(107, 114, 128, 0.3);
  color: white;
}

.student-profile-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  margin-bottom: 24px;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 24px;
  padding-bottom: 24px;
  border-bottom: 2px solid #F3F4F6;
  margin-bottom: 24px;
}

.profile-avatar-large {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 3rem;
  font-weight: 700;
  box-shadow: 0 8px 24px rgba(79, 70, 229, 0.3);
  position: relative;
}

.status-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 4px solid white;
}

.status-indicator.pending {
  background: var(--warning);
}

.status-indicator.approved {
  background: var(--success);
}

.status-indicator.rejected {
  background: var(--danger);
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 8px;
}

.profile-email {
  font-size: 1.1rem;
  color: #6B7280;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.profile-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.9rem;
  text-transform: uppercase;
}

.profile-status.pending {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: var(--warning);
  border: 2px solid var(--warning);
}

.profile-status.approved {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: var(--success);
  border: 2px solid var(--success);
}

.profile-status.rejected {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
  color: var(--danger);
  border: 2px solid var(--danger);
}

.action-buttons-top {
  display: flex;
  gap: 12px;
}

.btn-action-primary {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
}

.btn-action-primary.approve {
  background: linear-gradient(135deg, var(--success), #059669);
}

.btn-action-primary.reject {
  background: linear-gradient(135deg, var(--danger), #DC2626);
}

.btn-action-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  color: white;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.info-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.section-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-title i {
  color: var(--primary);
  font-size: 1.3rem;
}

.info-row {
  display: flex;
  align-items: start;
  padding: 12px 0;
  border-bottom: 1px solid #F3F4F6;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 600;
  color: #6B7280;
  min-width: 140px;
  font-size: 0.9rem;
}

.info-value {
  flex: 1;
  color: var(--dark);
  font-size: 0.95rem;
  font-weight: 500;
}

.timeline-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  margin-top: 24px;
}

.timeline {
  position: relative;
  padding-left: 40px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, var(--primary), var(--secondary));
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -29px;
  top: 5px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: white;
  border: 3px solid var(--primary);
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
}

.timeline-date {
  font-size: 0.85rem;
  color: #6B7280;
  margin-bottom: 4px;
}

.timeline-content {
  background: #F9FAFB;
  padding: 12px 16px;
  border-radius: 10px;
  border-left: 3px solid var(--primary);
}

.timeline-title {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 4px;
}

.timeline-desc {
  font-size: 0.9rem;
  color: #6B7280;
}

.notes-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  margin-top: 24px;
}

.notes-area {
  width: 100%;
  border: 2px solid #E5E7EB;
  border-radius: 12px;
  padding: 16px;
  min-height: 120px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.notes-area:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
}

.btn-save-notes {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  margin-top: 12px;
  transition: all 0.3s ease;
}

.btn-save-notes:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
  color: white;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.student-profile-section,
.info-section,
.timeline-section,
.notes-section {
  animation: fadeInUp 0.5s ease forwards;
}

.info-section:nth-child(1) { animation-delay: 0.1s; }
.info-section:nth-child(2) { animation-delay: 0.2s; }
.info-section:nth-child(3) { animation-delay: 0.3s; }

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .action-buttons-top {
    flex-direction: column;
    width: 100%;
  }

  .btn-action-primary {
    width: 100%;
    justify-content: center;
  }

  .info-grid {
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
    <a href=adminpending_students.php" class="active">
      <i class="fas fa-clock"></i>
      <span>Pending Students</span>
    </a>
    <a href="adminstudents.php">
      <i class="fas fa-user-graduate"></i>
      <span>Enrolled Students</span>
    </a>
    <a href="adminschedule.php">
      <i class="fas fa-calendar-alt"></i>
      <span>Class Schedule</span>
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
    <h2>
      <i class="fas fa-user-circle"></i>
      Student Application Details
    </h2>
    <a href="adminpending_students.php" class="btn-back">
      <i class="fas fa-arrow-left"></i>
      Back to List
    </a>
  </div>

  <!-- Student Profile Section -->
  <div class="student-profile-section">
    <div class="profile-header">
      <div class="profile-avatar-large">
        <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
        <div class="status-indicator <?php echo $student['status']; ?>"></div>
      </div>
      
      <div class="profile-info">
        <h1 class="profile-name"><?php echo htmlspecialchars($student['student_name']); ?></h1>
        <div class="profile-email">
          <i class="fas fa-envelope"></i>
          <?php echo htmlspecialchars($student['email']); ?>
        </div>
        <span class="profile-status <?php echo $student['status']; ?>">
          <i class="fas fa-circle"></i>
          <?php echo ucfirst($student['status']); ?>
        </span>
      </div>

      <?php if ($student['status'] === 'pending'): ?>
      <div class="action-buttons-top">
        <button class="btn-action-primary approve" onclick="approveStudent(<?php echo $student['id']; ?>)">
          <i class="fas fa-check-circle"></i>
          Approve
        </button>
        <button class="btn-action-primary reject" onclick="rejectStudent(<?php echo $student['id']; ?>)">
          <i class="fas fa-times-circle"></i>
          Reject
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Information Grid -->
  <div class="info-grid">
    <!-- Personal Information -->
    <div class="info-section">
      <h3 class="section-title">
        <i class="fas fa-user"></i>
        Personal Information
      </h3>
      <div class="info-row">
        <div class="info-label">Full Name:</div>
        <div class="info-value"><?php echo htmlspecialchars($student['student_name']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Contact Number:</div>
        <div class="info-value"><?php echo htmlspecialchars($student['contact']); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Date of Birth:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['birthday']) ? $student['birthday'] : 'N/A'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Gender:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['gender']) ? $student['gender'] : 'N/A'); ?></div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="info-section">
      <h3 class="section-title">
        <i class="fas fa-address-book"></i>
        Contact Information
      </h3>
      <div class="info-row">
        <div class="info-label">Address:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['address']) ? $student['address'] : 'N/A'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Parent/Guardian:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['parents_name']) ? $student['parents_name'] : 'N/A'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Parent Contact:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['parents_contact']) ? $student['parents_contact'] : 'N/A'); ?></div>
      </div>
    
    </div>

    <!-- Tutorial Preferences -->
    <div class="info-section">
      <h3 class="section-title">
        <i class="fas fa-graduation-cap"></i>
        Tutorial Preferences
      </h3>
      <div class="info-row">
        <div class="info-label">Subject Tutorials:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['subject_tutorials']) ? $student['subject_tutorials'] : 'Not selected'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Computer Tutorials:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['computer_tutorials']) ? $student['computer_tutorials'] : 'Not selected'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Sessions Per Week:</div>
        <div class="info-value"><?php echo htmlspecialchars(isset($student['sessions_per_week']) ? $student['sessions_per_week'] : 'N/A'); ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Schedule Preference:</div>
        <div class="info-value">
          <?php 
            if (isset($student['schedule_preference'])) {
              echo $student['schedule_preference'] === 'self' ? 'Self-Schedule' : 'Tutor-Suggested';
            } else {
              echo 'N/A';
            }
          ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Application Timeline -->
  <div class="timeline-section">
    <h3 class="section-title">
      <i class="fas fa-history"></i>
      Application Timeline
    </h3>
    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-date">
          <?php echo date('F d, Y h:i A', strtotime($student['created_at'])); ?>
        </div>
        <div class="timeline-content">
          <div class="timeline-title">Application Submitted</div>
          <div class="timeline-desc">Student submitted their application for tutorial services</div>
        </div>
      </div>

      <?php if ($student['status'] === 'approved'): ?>
      <div class="timeline-item">
        <div class="timeline-date">
          <?php echo date('F d, Y h:i A', strtotime(isset($student['updated_at']) ? $student['updated_at'] : $student['created_at'])); ?>
        </div>
        <div class="timeline-content">
          <div class="timeline-title">Application Approved</div>
          <div class="timeline-desc">Student application was approved by admin</div>
        </div>
      </div>
      <?php elseif ($student['status'] === 'rejected'): ?>
      <div class="timeline-item">
        <div class="timeline-date">
          <?php echo date('F d, Y h:i A', strtotime(isset($student['updated_at']) ? $student['updated_at'] : $student['created_at'])); ?>
        </div>
        <div class="timeline-content">
          <div class="timeline-title">Application Rejected</div>
          <div class="timeline-desc">Student application was rejected</div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Admin Notes -->
  <div class="notes-section">
    <h3 class="section-title">
      <i class="fas fa-sticky-note"></i>
      Admin Notes
    </h3>
    <textarea class="notes-area" placeholder="Add notes about this student application..."><?php echo htmlspecialchars(isset($student['admin_notes']) ? $student['admin_notes'] : ''); ?></textarea>
    <button class="btn-save-notes" onclick="saveNotes(<?php echo $student['id']; ?>)">
      <i class="fas fa-save"></i> Save Notes
    </button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function approveStudent(id) {
  if (confirm('Are you sure you want to approve this student?')) {
    window.location.href = 'approve_student.php?id=' + id;
  }
}

function rejectStudent(id) {
  if (confirm('Are you sure you want to reject this application?')) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    window.location.href = 'reject_student.php?id=' + id + (reason ? '&reason=' + encodeURIComponent(reason) : '');
  }
}

function saveNotes(id) {
  const notes = document.querySelector('.notes-area').value;
  
  fetch('save_student_notes.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'student_id=' + id + '&notes=' + encodeURIComponent(notes)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Notes saved successfully!');
    } else {
      alert('Failed to save notes. Please try again.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while saving notes.');
  });
}
</script>

</body>
</html>