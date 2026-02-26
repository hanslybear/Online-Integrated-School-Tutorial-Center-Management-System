<?php
session_start();
include "db_connect.php";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($student['student_name']); ?> - Profile | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
.profile-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px;
  border-radius: 20px;
  margin-bottom: 32px;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
}

.profile-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.profile-header-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 32px;
}

.profile-avatar-large {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 700;
  color: #667eea;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
}

.profile-info {
  flex: 1;
  color: white;
}

.profile-name {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: white;
}

.profile-meta {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
  margin-top: 16px;
}

.profile-meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.2);
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.95rem;
}

.profile-meta-item i {
  font-size: 1.1rem;
}

.profile-actions {
  display: flex;
  gap: 12px;
}

.btn-profile-action {
  padding: 12px 24px;
  border: 2px solid white;
  background: transparent;
  color: white;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.btn-profile-action:hover {
  background: white;
  color: #667eea;
  transform: translateY(-2px);
}

.btn-profile-action.primary {
  background: white;
  color: #667eea;
  border-color: white;
}

.btn-profile-action.primary:hover {
  background: rgba(255, 255, 255, 0.9);
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.section-card {
  background: white;
  border-radius: 16px;
  padding: 28px;
  margin-bottom: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #F3F4F6;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.section-title i {
  color: #667eea;
  font-size: 1.4rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-label {
  font-size: 0.8rem;
  color: #6B7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 1.05rem;
  color: #1a202c;
  font-weight: 600;
}

.info-value.empty {
  color: #9CA3AF;
  font-style: italic;
}

.tutorial-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.tutorial-box {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
  border: 2px solid rgba(102, 126, 234, 0.2);
  border-radius: 12px;
  padding: 20px;
}

.tutorial-box-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.tutorial-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.tutorial-box-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
}

.tutorial-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tutorial-tag {
  background: white;
  border: 2px solid #667eea;
  color: #667eea;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  border: 2px solid rgba(102, 126, 234, 0.1);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.2);
  border-color: #667eea;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.6rem;
  margin: 0 auto 12px;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.85rem;
  color: #6B7280;
  font-weight: 600;
  text-transform: uppercase;
}

.timeline {
  position: relative;
  padding-left: 32px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, #667eea, #764ba2);
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -28px;
  top: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #667eea;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #667eea;
}

.timeline-date {
  font-size: 0.8rem;
  color: #6B7280;
  font-weight: 600;
  margin-bottom: 4px;
}

.timeline-content {
  background: #F9FAFB;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1a202c;
}

.badge-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-status.active {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: #059669;
  border: 2px solid #10b981;
}

.badge-status i {
  font-size: 0.9rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #F9FAFB;
  border-radius: 10px;
  margin-bottom: 12px;
}

.contact-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
}

.contact-info {
  flex: 1;
}

.contact-label {
  font-size: 0.75rem;
  color: #6B7280;
  font-weight: 600;
  text-transform: uppercase;
}

.contact-value {
  font-size: 1rem;
  color: #1a202c;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6B7280;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 16px;
  opacity: 0.3;
}

.btn-back {
  background: #F3F4F6;
  color: #1a202c;
  border: none;
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
  background: #E5E7EB;
  color: #1a202c;
  transform: translateX(-4px);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section-card {
  animation: fadeInUp 0.5s ease forwards;
}

.section-card:nth-child(1) { animation-delay: 0.1s; }
.section-card:nth-child(2) { animation-delay: 0.2s; }
.section-card:nth-child(3) { animation-delay: 0.3s; }
.section-card:nth-child(4) { animation-delay: 0.4s; }

@media (max-width: 768px) {
  .profile-header-content {
    flex-direction: column;
    text-align: center;
  }

  .profile-actions {
    flex-direction: column;
    width: 100%;
  }

  .btn-profile-action {
    width: 100%;
    justify-content: center;
  }

  .profile-meta {
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
    <a href="adminpending_students.php">
      <i class="fas fa-clock"></i>
      <span>Pending Students</span>
    </a>
    <a href="adminstudents.php" class="active">
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
    <a href="feedback.php">
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
  <a href="adminstudent_profile.php" class="btn-back">
    <i class="fas fa-arrow-left"></i>
    Back to Students
  </a>

  <!-- Profile Header -->
  <div class="profile-header">
    <div class="profile-header-content">
      <div class="profile-avatar-large">
        <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
      </div>
      
      <div class="profile-info">
        <h1 class="profile-name"><?php echo htmlspecialchars($student['student_name']); ?></h1>
        <div class="profile-meta">
          <div class="profile-meta-item">
            <i class="fas fa-envelope"></i>
            <?php echo htmlspecialchars($student['email']); ?>
          </div>
          <div class="profile-meta-item">
            <i class="fas fa-phone"></i>
            <?php echo htmlspecialchars($student['contact']); ?>
          </div>
          <div class="profile-meta-item">
            <i class="fas fa-calendar"></i>
            Enrolled: <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
          </div>
        </div>
      </div>

      <div class="profile-actions">
        <button class="btn-profile-action primary" onclick="window.location.href='edit_student.php?id=<?php echo $student_id; ?>'">
          <i class="fas fa-edit"></i>
          Edit Profile
        </button>
        <button class="btn-profile-action" onclick="window.location.href='export_student_pdf.php?id=<?php echo $student_id; ?>'">
    <i class="fas fa-file-pdf"></i>
    Export PDF
</button>
        </button>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
      
      <!-- Tutorial Enrollment Section -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-book-reader"></i>
            Tutorial Enrollment
          </h3>
          <span class="badge-status active">
            <i class="fas fa-check-circle"></i>
            Active
          </span>
        </div>

        <div class="tutorial-section">
          <!-- Subject Tutorials -->
          <div class="tutorial-box">
            <div class="tutorial-box-header">
              <div class="tutorial-icon">
                <i class="fas fa-book-open"></i>
              </div>
              <div class="tutorial-box-title">Subject Tutorials</div>
            </div>
            <?php if (!empty($student['subject_tutorials'])): ?>
              <div class="tutorial-list">
                <?php 
                  $subjects = explode(', ', $student['subject_tutorials']);
                  foreach ($subjects as $subject): 
                ?>
                  <span class="tutorial-tag"><?php echo htmlspecialchars($subject); ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="info-value empty">No subject tutorials enrolled</p>
            <?php endif; ?>
          </div>

          <!-- Computer Tutorials -->
          <div class="tutorial-box">
            <div class="tutorial-box-header">
              <div class="tutorial-icon">
                <i class="fas fa-laptop-code"></i>
              </div>
              <div class="tutorial-box-title">Computer Tutorials</div>
            </div>
            <?php if (!empty($student['computer_tutorials'])): ?>
              <div class="tutorial-list">
                <?php 
                  $computers = explode(', ', $student['computer_tutorials']);
                  foreach ($computers as $computer): 
                ?>
                  <span class="tutorial-tag"><?php echo htmlspecialchars($computer); ?></span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="info-value empty">No computer tutorials enrolled</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Session Details -->
        <div class="stats-grid" style="margin-top: 24px;">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-value"><?php echo isset($student['sessions_per_week']) ? $student['sessions_per_week'] : '—'; ?></div>
            <div class="stat-label">Sessions Per Week</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value">
              <?php 
                if ($student['schedule_preference'] === 'self') {
                  echo 'Self';
                } elseif ($student['schedule_preference'] === 'tutor') {
                  echo 'Tutor';
                } else {
                  echo '—';
                }
              ?>
            </div>
            <div class="stat-label">Schedule Type</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value">95%</div>
            <div class="stat-label">Attendance Rate</div>
          </div>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-user"></i>
            Personal Information
          </h3>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <div class="info-label">Full Name</div>
            <div class="info-value"><?php echo htmlspecialchars($student['student_name']); ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Student ID</div>
            <div class="info-value">#<?php echo str_pad($student['id'], 6, '0', STR_PAD_LEFT); ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Contact Number</div>
            <div class="info-value"><?php echo htmlspecialchars($student['contact']); ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Gender</div>
            <div class="info-value"><?php echo htmlspecialchars($student['gender']); ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Parent/Guardian</div>
            <div class="info-value"><?php echo !empty($student['parents_name']) ? htmlspecialchars($student['parents_name']) : '—'; ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Parent Contact</div>
            <div class="info-value"><?php echo !empty($student['parents_contact']) ? htmlspecialchars($student['parents_contact']) : '—'; ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Address</div>
            <div class="info-value"><?php echo !empty($student['address']) ? htmlspecialchars($student['address']) : '—'; ?></div>
          </div>

          <div class="info-item">
            <div class="info-label">Date of Birth</div>
            <div class="info-value"><?php echo !empty($student['birthday']) ? date('M d, Y', strtotime($student['birthday'])) : '—'; ?></div>
          </div>
        </div>
      </div>

      <!-- Academic Performance -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-chart-bar"></i>
            Academic Performance
          </h3>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-label">Overall Grade</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-label">Assignments Done</div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-value">—</div>
            <div class="stat-label">Achievements</div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
      
      <!-- Quick Contact -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-address-book"></i>
            Quick Contact
          </h3>
        </div>

        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <div class="contact-info">
            <div class="contact-label">Email</div>
            <div class="contact-value"><?php echo htmlspecialchars($student['email']); ?></div>
          </div>
        </div>

        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-phone"></i>
          </div>
          <div class="contact-info">
            <div class="contact-label">Phone</div>
            <div class="contact-value"><?php echo htmlspecialchars($student['contact']); ?></div>
          </div>
        </div>

        <?php if (!empty($student['parent_name'])): ?>
        <div class="contact-item">
          <div class="contact-icon">
            <i class="fas fa-user-friends"></i>
          </div>
          <div class="contact-info">
            <div class="contact-label">Parent/Guardian</div>
            <div class="contact-value"><?php echo htmlspecialchars($student['parent_name']); ?></div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Activity Timeline -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-history"></i>
            Recent Activity
          </h3>
        </div>

        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-date"><?php echo date('M d, Y'); ?></div>
            <div class="timeline-content">Profile viewed by admin</div>
          </div>

          <div class="timeline-item">
            <div class="timeline-date"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></div>
            <div class="timeline-content">Enrolled in tutorial services</div>
          </div>

          <div class="timeline-item">
            <div class="timeline-date"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></div>
            <div class="timeline-content">Account created and approved</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section-card">
        <div class="section-header">
          <h3 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
          </h3>
        </div>

        <div class="d-grid gap-2">
          <button class="btn btn-outline-primary" onclick="window.location.href='edit_student.php?id=<?php echo $student_id; ?>'">
            <i class="fas fa-edit"></i> Edit Profile
          </button>
          <button class="btn btn-outline-info">
            <i class="fas fa-calendar-plus"></i> Schedule Session
          </button>
          <button class="btn btn-outline-success">
            <i class="fas fa-file-invoice"></i> View Billing
          </button>
          <button class="btn btn-outline-warning">
            <i class="fas fa-clipboard-check"></i> Take Attendance
          </button>
        </div>
      </div>

    </div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>