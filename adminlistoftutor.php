<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php");
    exit();
}

// Handle success/error messages
$message = '';
$messageType = '';

if (isset($_SESSION['tutor_message'])) {
    $message = $_SESSION['tutor_message'];
    $messageType = $_SESSION['tutor_message_type'];
    unset($_SESSION['tutor_message']);
    unset($_SESSION['tutor_message_type']);
}

// Fetch all tutors from database with actual student count
$tutors = [];
$tutorQuery = "SELECT t.*, 
               COUNT(DISTINCT cs.students_enrolled) as actual_student_count
               FROM tutors t
               LEFT JOIN class_schedules cs ON t.id = cs.tutor_id
               GROUP BY t.id
               ORDER BY t.tutor_name ASC";
$tutorResult = mysqli_query($conn, $tutorQuery);

if ($tutorResult) {
    while ($row = mysqli_fetch_assoc($tutorResult)) {
        $tutors[] = $row;
    }
}

// Calculate statistics
$totalTutors = count($tutors);
$activeTutors = count(array_filter($tutors, function($t) { return $t['status'] === 'active'; }));
$totalStudents = array_sum(array_column($tutors, 'actual_student_count')); // Using actual count from query
$avgRating = $totalTutors > 0 ? round(array_sum(array_column($tutors, 'rating')) / $totalTutors, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tutor Management | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'DM Sans', sans-serif;
}

body {
    background: #f8fafc;
}

/* Alert Notifications */
.alert-custom {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    animation: slideIn 0.5s ease-out;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    color: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2);
}

.page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

/* Stats Cards */
.tutor-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.tutor-stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.tutor-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon-small {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 12px;
}

.stat-label-small {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.stat-value-large {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
}

/* Toolbar */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 16px;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 10px 16px 10px 42px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.btn-add-tutor {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-add-tutor:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
}

/* Tutor Grid */
.tutor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.tutor-card {
    background: white;
    border-radius: 16px;
    overflow: visible;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.tutor-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Card Header - Completely redesigned to prevent overlap */
.tutor-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 16px 16px 12px;
    position: relative;
    text-align: center;
    border-radius: 16px 16px 0 0;
}

.tutor-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.tutor-status-badge.active {
    background: rgba(16, 185, 129, 0.25);
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.5);
}

.tutor-status-badge.inactive {
    background: rgba(239, 68, 68, 0.25);
    color: #ef4444;
    border: 2px solid rgba(239, 68, 68, 0.5);
}

/* Avatar */
.tutor-avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 8px;
}

.tutor-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    object-fit: cover;
    background: white;
}

.tutor-avatar.placeholder {
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    font-weight: 800;
    color: white;
    font-family: 'Playfair Display', serif;
}

.tutor-rating-badge {
    position: absolute;
    bottom: -4px;
    right: -4px;
    background: white;
    padding: 3px 8px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #f59e0b;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Card Body - Clean spacing */
.tutor-card-body {
    padding: 16px;
}

/* Tutor Name - Fixed height to prevent overflow */
.tutor-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 6px;
    text-align: center;
    line-height: 1.3;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tutor-title {
    color: #667eea;
    font-weight: 600;
    text-align: center;
    margin-bottom: 14px;
    font-size: 0.8rem;
    line-height: 1.3;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Stats Row */
.tutor-stats-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 14px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
}

.tutor-stat-item {
    text-align: center;
}

.tutor-stat-item-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #667eea;
    display: block;
}

.tutor-stat-item-label {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
}

/* Info Grid */
.tutor-info-grid {
    display: grid;
    gap: 8px;
    margin-bottom: 14px;
}

.tutor-info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8fafc;
    border-radius: 8px;
}

.tutor-info-icon {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.tutor-info-content {
    flex: 1;
    min-width: 0;
}

.tutor-info-label {
    font-size: 0.65rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.tutor-info-value {
    color: #1a202c;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Subjects */
.tutor-subjects {
    margin-bottom: 14px;
}

.tutor-subjects-label {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.subject-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.subject-tag {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
    padding: 4px 10px;
    border-radius: 14px;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

/* Action Buttons - Updated for 4 buttons */
.tutor-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
}

.btn-action {
    padding: 8px 6px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.7rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: pointer;
}

.btn-view {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.btn-view:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-2px);
}

.btn-students {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.btn-students:hover {
    background: #10b981;
    color: white;
    transform: translateY(-2px);
}

.btn-edit {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.btn-edit:hover {
    background: #f59e0b;
    color: white;
    transform: translateY(-2px);
}

.btn-delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
    background: #ef4444;
    color: white;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.empty-state h4 {
    font-family: 'Playfair Display', serif;
    color: #1a202c;
    margin-bottom: 12px;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 20px;
}

/* Modal Styles */
.modal-content {
    border: none;
    border-radius: 16px;
}

.modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 20px 24px;
    border: none;
}

.modal-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.4rem;
}

.btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 24px;
    max-height: 70vh;
    overflow-y: auto;
}

.form-label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 8px;
}

.form-label .required {
    color: #ef4444;
}

.form-control, .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

/* Students List Styles */
.students-list {
    display: grid;
    gap: 12px;
}

.student-item {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.student-item:hover {
    border-color: #667eea;
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 800;
    color: white;
    font-family: 'Playfair Display', serif;
    flex-shrink: 0;
}

.student-info {
    flex: 1;
    min-width: 0;
}

.student-name {
    font-weight: 700;
    color: #1a202c;
    font-size: 1rem;
    margin-bottom: 4px;
}

.student-email {
    color: #64748b;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.student-tutorials {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-end;
}

.tutorial-badge {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid rgba(102, 126, 234, 0.2);
    white-space: nowrap;
}

.no-students {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
}

.no-students i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 16px;
    display: block;
}

@media (max-width: 768px) {
    .tutor-grid {
        grid-template-columns: 1fr;
    }
    
    .toolbar {
        flex-direction: column;
    }
    
    .search-box {
        max-width: 100%;
    }
    
    .student-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .student-tutorials {
        align-items: flex-start;
        width: 100%;
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
    <a href="adminstudents.php">
      <i class="fas fa-user-graduate"></i>
      <span>Enrolled Students</span>
    </a>
    <a href="adminlistoftutor.php" class="active">
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
  <!-- Success/Error Messages -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show alert-custom" role="alert">
      <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
      <strong><?php echo $messageType === 'success' ? 'Success!' : 'Error!'; ?></strong> <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <h2><i class="fas fa-chalkboard-teacher"></i> List of Tutors</h2>
  </div>

  <!-- Statistics Cards -->
  <div class="tutor-stats">
    <div class="tutor-stat-card">
      <div class="stat-icon-small">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-label-small">Total Tutors</div>
      <div class="stat-value-large"><?php echo $totalTutors; ?></div>
    </div>

    <div class="tutor-stat-card">
      <div class="stat-icon-small">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-label-small">Active Tutors</div>
      <div class="stat-value-large"><?php echo $activeTutors; ?></div>
    </div>

    <div class="tutor-stat-card">
      <div class="stat-icon-small">
        <i class="fas fa-user-graduate"></i>
      </div>
      <div class="stat-label-small">Total Students</div>
      <div class="stat-value-large"><?php echo $totalStudents; ?></div>
    </div>

    <div class="tutor-stat-card">
      <div class="stat-icon-small">
        <i class="fas fa-star"></i>
      </div>
      <div class="stat-label-small">Avg Rating</div>
      <div class="stat-value-large"><?php echo $avgRating; ?></div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" id="tutorSearch" placeholder="Search tutors by name, subject, or specialization...">
    </div>
    <button class="btn-add-tutor" data-bs-toggle="modal" data-bs-target="#addTutorModal">
      <i class="fas fa-plus-circle"></i>
      Add New Tutor
    </button>
  </div>

  <!-- Tutor Grid -->
  <?php if (!empty($tutors)): ?>
    <div class="tutor-grid" id="tutorGrid">
      <?php foreach ($tutors as $tutor): ?>
        <div class="tutor-card" data-tutor-name="<?php echo strtolower($tutor['tutor_name']); ?>" 
             data-tutor-subjects="<?php echo strtolower($tutor['subjects']); ?>"
             data-tutor-specialization="<?php echo strtolower($tutor['specialization']); ?>">
          
          <!-- Card Header -->
          <div class="tutor-card-header">
            <div class="tutor-status-badge <?php echo $tutor['status']; ?>">
              <?php echo ucfirst($tutor['status']); ?>
            </div>
            
            <div class="tutor-avatar-wrapper">
              <?php if (!empty($tutor['profile_image']) && file_exists($tutor['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($tutor['profile_image']); ?>" 
                     alt="<?php echo htmlspecialchars($tutor['tutor_name']); ?>" 
                     class="tutor-avatar">
              <?php else: ?>
                <div class="tutor-avatar placeholder">
                  <?php echo strtoupper(substr($tutor['tutor_name'], 0, 1)); ?>
                </div>
              <?php endif; ?>
              
              <div class="tutor-rating-badge">
                <i class="fas fa-star"></i>
                <?php echo $tutor['rating']; ?>
              </div>
            </div>
          </div>

          <!-- Card Body -->
          <div class="tutor-card-body">
            <h3 class="tutor-name"><?php echo htmlspecialchars($tutor['tutor_name']); ?></h3>
            <p class="tutor-title"><?php echo htmlspecialchars($tutor['education']); ?></p>

            <!-- Stats Row -->
            <div class="tutor-stats-row">
              <div class="tutor-stat-item">
                <span class="tutor-stat-item-value"><?php echo $tutor['actual_student_count']; ?></span>
                <span class="tutor-stat-item-label">Students</span>
              </div>
              <div class="tutor-stat-item">
                <span class="tutor-stat-item-value"><?php echo $tutor['experience']; ?></span>
                <span class="tutor-stat-item-label">Experience</span>
              </div>
            </div>

            <!-- Info Grid -->
            <div class="tutor-info-grid">
              <div class="tutor-info-item">
                <div class="tutor-info-icon">
                  <i class="fas fa-envelope"></i>
                </div>
                <div class="tutor-info-content">
                  <div class="tutor-info-label">Email</div>
                  <div class="tutor-info-value"><?php echo htmlspecialchars($tutor['email']); ?></div>
                </div>
              </div>

              <div class="tutor-info-item">
                <div class="tutor-info-icon">
                  <i class="fas fa-phone"></i>
                </div>
                <div class="tutor-info-content">
                  <div class="tutor-info-label">Phone</div>
                  <div class="tutor-info-value"><?php echo htmlspecialchars($tutor['phone']); ?></div>
                </div>
              </div>

              <div class="tutor-info-item">
                <div class="tutor-info-icon">
                  <i class="fas fa-lightbulb"></i>
                </div>
                <div class="tutor-info-content">
                  <div class="tutor-info-label">Specialization</div>
                  <div class="tutor-info-value"><?php echo htmlspecialchars($tutor['specialization']); ?></div>
                </div>
              </div>
            </div>

            <!-- Subjects -->
            <div class="tutor-subjects">
              <div class="tutor-subjects-label">Teaching Subjects</div>
              <div class="subject-tags">
                <?php 
                $subjects = explode(',', $tutor['subjects']);
                foreach ($subjects as $subject): 
                ?>
                  <span class="subject-tag"><?php echo trim(htmlspecialchars($subject)); ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Actions - Updated to 2x2 grid for 4 buttons -->
            <div class="tutor-actions">
              <button class="btn-action btn-view" onclick="viewTutor(<?php echo $tutor['id']; ?>, '<?php echo htmlspecialchars($tutor['tutor_name'], ENT_QUOTES); ?>')">
                <i class="fas fa-eye"></i>
                View
              </button>
              <button class="btn-action btn-students" onclick="viewTutorStudents(<?php echo $tutor['id']; ?>, '<?php echo htmlspecialchars($tutor['tutor_name'], ENT_QUOTES); ?>')">
                <i class="fas fa-user-graduate"></i>
                Students
              </button>
              <button class="btn-action btn-edit" onclick="editTutor(<?php echo $tutor['id']; ?>)">
                <i class="fas fa-edit"></i>
                Edit
              </button>
              <button class="btn-action btn-delete" onclick="confirmDelete(<?php echo $tutor['id']; ?>, '<?php echo htmlspecialchars($tutor['tutor_name'], ENT_QUOTES); ?>')">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-user-tie"></i>
      <h4>No Tutors Found</h4>
      <p>Start building your team by adding your first tutor</p>
      <button class="btn-add-tutor" data-bs-toggle="modal" data-bs-target="#addTutorModal">
        <i class="fas fa-plus-circle"></i>
        Add Your First Tutor
      </button>
    </div>
  <?php endif; ?>
</div>

<!-- Add Tutor Modal -->
<div class="modal fade" id="addTutorModal" tabindex="-1" aria-labelledby="addTutorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTutorModalLabel">
          <i class="fas fa-user-plus"></i> Add New Tutor
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="tutor_operations.php" method="POST" enctype="multipart/form-data" id="addTutorForm">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="tutor_name" class="form-label">Full Name <span class="required">*</span></label>
              <input type="text" class="form-control" id="tutor_name" name="tutor_name" required>
            </div>
            
            <div class="col-md-6">
              <label for="email" class="form-label">Email Address <span class="required">*</span></label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone Number <span class="required">*</span></label>
              <input type="text" class="form-control" id="phone" name="phone" placeholder="+63 912 345 6789" required>
            </div>
            
            <div class="col-md-6">
              <label for="education" class="form-label">Education/Credentials <span class="required">*</span></label>
              <input type="text" class="form-control" id="education" name="education" required>
            </div>
            
            <div class="col-md-6">
              <label for="experience" class="form-label">Years of Experience <span class="required">*</span></label>
              <input type="text" class="form-control" id="experience" name="experience" placeholder="e.g., 5 Years" required>
            </div>
            
            <div class="col-md-6">
              <label for="rating" class="form-label">Initial Rating</label>
              <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" step="0.1" value="5.0">
            </div>
            
            <div class="col-12">
              <label for="specialization" class="form-label">Specialization <span class="required">*</span></label>
              <textarea class="form-control" id="specialization" name="specialization" rows="2" required></textarea>
            </div>
            
            <div class="col-12">
              <label for="subjects" class="form-label">Teaching Subjects <span class="required">*</span></label>
              <input type="text" class="form-control" id="subjects" name="subjects" placeholder="Comma-separated" required>
            </div>
            
            <div class="col-md-6">
              <label for="status" class="form-label">Status <span class="required">*</span></label>
              <select class="form-select" id="status" name="status" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label for="profile_image" class="form-label">Profile Photo</label>
              <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
              <small class="text-muted">Optional. Max 5MB.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
            <i class="fas fa-save"></i> Save Tutor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Tutor Modal -->
<div class="modal fade" id="editTutorModal" tabindex="-1" aria-labelledby="editTutorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTutorModalLabel">
          <i class="fas fa-edit"></i> Edit Tutor
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="tutor_operations.php" method="POST" enctype="multipart/form-data" id="editTutorForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="tutor_id" id="edit_tutor_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="edit_tutor_name" class="form-label">Full Name <span class="required">*</span></label>
              <input type="text" class="form-control" id="edit_tutor_name" name="tutor_name" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_email" class="form-label">Email Address <span class="required">*</span></label>
              <input type="email" class="form-control" id="edit_email" name="email" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_phone" class="form-label">Phone Number <span class="required">*</span></label>
              <input type="text" class="form-control" id="edit_phone" name="phone" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_education" class="form-label">Education/Credentials <span class="required">*</span></label>
              <input type="text" class="form-control" id="edit_education" name="education" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_experience" class="form-label">Years of Experience <span class="required">*</span></label>
              <input type="text" class="form-control" id="edit_experience" name="experience" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_rating" class="form-label">Rating</label>
              <input type="number" class="form-control" id="edit_rating" name="rating" min="0" max="5" step="0.1">
            </div>
            
            <div class="col-12">
              <label for="edit_specialization" class="form-label">Specialization <span class="required">*</span></label>
              <textarea class="form-control" id="edit_specialization" name="specialization" rows="2" required></textarea>
            </div>
            
            <div class="col-12">
              <label for="edit_subjects" class="form-label">Teaching Subjects <span class="required">*</span></label>
              <input type="text" class="form-control" id="edit_subjects" name="subjects" required>
            </div>
            
            <div class="col-md-6">
              <label for="edit_status" class="form-label">Status <span class="required">*</span></label>
              <select class="form-select" id="edit_status" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label for="edit_profile_image" class="form-label">Profile Photo</label>
              <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
              <small class="text-muted">Leave empty to keep current photo.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
            <i class="fas fa-save"></i> Update Tutor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Tutor Modal -->
<div class="modal fade" id="viewTutorModal" tabindex="-1" aria-labelledby="viewTutorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTutorModalLabel">
          <i class="fas fa-user"></i> <span id="view_tutor_name_title"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewTutorContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- View Tutor Students Modal -->
<div class="modal fade" id="viewStudentsModal" tabindex="-1" aria-labelledby="viewStudentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewStudentsModalLabel">
          <i class="fas fa-user-graduate"></i> Students - <span id="students_tutor_name"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="studentsListContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alert after 5 seconds
setTimeout(function() {
    const alert = document.querySelector('.alert-custom');
    if (alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }
}, 5000);

// Search functionality
document.getElementById('tutorSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tutorCards = document.querySelectorAll('.tutor-card');
    
    tutorCards.forEach(card => {
        const name = card.getAttribute('data-tutor-name');
        const subjects = card.getAttribute('data-tutor-subjects');
        const specialization = card.getAttribute('data-tutor-specialization');
        
        const matches = name.includes(searchTerm) || 
                       subjects.includes(searchTerm) || 
                       specialization.includes(searchTerm);
        
        card.style.display = matches ? 'block' : 'none';
    });
});

// View tutor details
function viewTutor(tutorId, tutorName) {
    document.getElementById('view_tutor_name_title').textContent = tutorName;
    
    fetch('get_tutor_details.php?id=' + tutorId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tutor = data.tutor;
                const content = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            ${tutor.profile_image && tutor.profile_image !== 'null' ? 
                                `<img src="${tutor.profile_image}" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">` :
                                `<div class="tutor-avatar placeholder" style="width: 150px; height: 150px; margin: 0 auto; font-size: 4rem;">${tutor.tutor_name.charAt(0)}</div>`
                            }
                            <h4 class="mt-3">${tutor.tutor_name}</h4>
                            <p class="text-muted">${tutor.education}</p>
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tr><th>Email:</th><td>${tutor.email}</td></tr>
                                <tr><th>Phone:</th><td>${tutor.phone}</td></tr>
                                <tr><th>Experience:</th><td>${tutor.experience}</td></tr>
                                <tr><th>Rating:</th><td><i class="fas fa-star text-warning"></i> ${tutor.rating}</td></tr>
                                <tr><th>Status:</th><td><span class="badge bg-${tutor.status === 'active' ? 'success' : 'danger'}">${tutor.status}</span></td></tr>
                                <tr><th>Assigned Students:</th><td><strong>${tutor.actual_student_count || 0}</strong> student(s)</td></tr>
                                <tr><th>Specialization:</th><td>${tutor.specialization}</td></tr>
                                <tr><th>Teaching Subjects:</th><td>${tutor.subjects}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                document.getElementById('viewTutorContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('viewTutorModal')).show();
            } else {
                alert('Error loading tutor details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading tutor details');
        });
}

// View tutor's students
function viewTutorStudents(tutorId, tutorName) {
    document.getElementById('students_tutor_name').textContent = tutorName;
    
    // Show loading state
    document.getElementById('studentsListContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading students...</p>
        </div>
    `;
    
    // Open modal immediately
    new bootstrap.Modal(document.getElementById('viewStudentsModal')).show();
    
    // Fetch students
    fetch('get_tutor_students.php?tutor_id=' + tutorId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.students && data.students.length > 0) {
                    let studentsHTML = '<div class="students-list">';
                    
                    data.students.forEach(student => {
                        const initial = student.student_name.charAt(0).toUpperCase();
                        const subjectTutorials = student.subject_tutorials ? student.subject_tutorials.split(',').map(s => s.trim()) : [];
                        const computerTutorials = student.computer_tutorials ? student.computer_tutorials.split(',').map(s => s.trim()) : [];
                        
                        studentsHTML += `
                            <div class="student-item">
                                <div class="student-avatar">${initial}</div>
                                <div class="student-info">
                                    <div class="student-name">${student.student_name}</div>
                                    <div class="student-email"><i class="fas fa-envelope me-1"></i>${student.email}</div>
                                </div>
                                <div class="student-tutorials">
                        `;
                        
                        if (subjectTutorials.length > 0) {
                            studentsHTML += `<div class="tutorial-badge"><i class="fas fa-book me-1"></i>${subjectTutorials.join(', ')}</div>`;
                        }
                        
                        if (computerTutorials.length > 0) {
                            studentsHTML += `<div class="tutorial-badge"><i class="fas fa-laptop-code me-1"></i>${computerTutorials.join(', ')}</div>`;
                        }
                        
                        studentsHTML += `
                                </div>
                            </div>
                        `;
                    });
                    
                    studentsHTML += '</div>';
                    studentsHTML += `
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong><i class="fas fa-info-circle me-2"></i>Total Students: ${data.students.length}</strong>
                        </div>
                    `;
                    
                    document.getElementById('studentsListContent').innerHTML = studentsHTML;
                } else {
                    document.getElementById('studentsListContent').innerHTML = `
                        <div class="no-students">
                            <i class="fas fa-user-slash"></i>
                            <h5>No Students Assigned</h5>
                            <p class="text-muted">This tutor currently has no students assigned to them.</p>
                        </div>
                    `;
                }
            } else {
                document.getElementById('studentsListContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: ${data.message || 'Failed to load students'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('studentsListContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    An error occurred while loading students. Please try again.
                </div>
            `;
        });
}

// Edit tutor
function editTutor(tutorId) {
    fetch('get_tutor_details.php?id=' + tutorId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tutor = data.tutor;
                
                document.getElementById('edit_tutor_id').value = tutor.id;
                document.getElementById('edit_tutor_name').value = tutor.tutor_name;
                document.getElementById('edit_email').value = tutor.email;
                document.getElementById('edit_phone').value = tutor.phone;
                document.getElementById('edit_education').value = tutor.education;
                document.getElementById('edit_experience').value = tutor.experience;
                document.getElementById('edit_rating').value = tutor.rating;
                document.getElementById('edit_specialization').value = tutor.specialization;
                document.getElementById('edit_subjects').value = tutor.subjects;
                document.getElementById('edit_status').value = tutor.status;
                
                new bootstrap.Modal(document.getElementById('editTutorModal')).show();
            } else {
                alert('Error loading tutor details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading tutor details');
        });
}

// Confirm delete tutor
function confirmDelete(tutorId, tutorName) {
    if (confirm('Are you sure you want to delete ' + tutorName + '? This action cannot be undone.')) {
        deleteTutor(tutorId);
    }
}

// Delete tutor
function deleteTutor(tutorId) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('tutor_id', tutorId);
    
    fetch('tutor_operations.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);  
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the tutor');
    });
}
</script>

</body>
</html>