<!--Student Tutor Profile Page - FIXED VERSION-->
<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: homepage.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student data
$sql = "SELECT student_name, email FROM users WHERE id = ? AND status = 'approved'";
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
$student_name = $student['student_name'];
$student_email = $student['email'];

// Fetch all active tutors
$tutors = [];
$tutorQuery = "SELECT * FROM tutors WHERE status = 'active' ORDER BY rating DESC, tutor_name ASC";
$tutorResult = mysqli_query($conn, $tutorQuery);

if ($tutorResult) {
    while ($row = mysqli_fetch_assoc($tutorResult)) {
        $tutors[] = $row;
    }
}

// Determine which field is the primary key
$idField = 'id'; // default
if (!empty($tutors)) {
    if (isset($tutors[0]['tutor_id'])) {
        $idField = 'tutor_id';
    } elseif (isset($tutors[0]['id'])) {
        $idField = 'id';
    }
}

// Calculate statistics
$totalTutors = count($tutors);
$avgRating = $totalTutors > 0 ? round(array_sum(array_column($tutors, 'rating')) / $totalTutors, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>List of Tutors | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
body {
    background: #f8fafc;
}

.header-section {
    margin-bottom: 32px;
}

.header-section h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.header-section p {
    color: #6B7280;
    margin: 0;
    font-size: 1rem;
}

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

.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card.primary::before {
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-card.success::before {
    background: linear-gradient(90deg, #10b981, #059669);
}

.stat-card.warning::before {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 16px;
}

.stat-card.primary .stat-icon {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    color: #667eea;
}

.stat-card.success .stat-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
    color: #10b981;
}

.stat-card.warning .stat-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
    color: #f59e0b;
}

.stat-label {
    font-size: 0.9rem;
    color: #6B7280;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a202c;
}

.search-filter-bar {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.search-box {
    position: relative;
    margin-bottom: 16px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px 12px 48px;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #6B7280;
    font-size: 1.1rem;
}

.filter-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    border: 2px solid #E5E7EB;
    background: white;
    border-radius: 10px;
    font-weight: 600;
    color: #6B7280;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    border-color: #667eea;
    color: #667eea;
    background: #f8f9ff;
}

.filter-tab.active {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.tutors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.tutor-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    cursor: pointer;
}

.tutor-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
}

.tutor-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 32px 24px 70px;
    position: relative;
    text-align: center;
}

.tutor-status-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.4);
}

.tutor-avatar-wrapper {
    position: relative;
    display: inline-block;
}

.tutor-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    object-fit: cover;
    background: white;
}

.tutor-avatar.placeholder {
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
}

.tutor-rating-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    background: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #f59e0b;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 4px;
}

.tutor-card-body {
    padding: 28px 24px 24px;
    margin-top: -40px;
    position: relative;
}

.tutor-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
    text-align: center;
}

.tutor-title {
    color: #667eea;
    font-weight: 600;
    text-align: center;
    margin-bottom: 16px;
    font-size: 0.9rem;
}

.tutor-stats-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 16px;
    padding: 14px;
    background: #F9FAFB;
    border-radius: 10px;
}

.tutor-stat-item {
    text-align: center;
}

.tutor-stat-item-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #667eea;
    display: block;
}

.tutor-stat-item-label {
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
    text-transform: uppercase;
}

.tutor-specialization {
    margin-bottom: 16px;
}

.specialization-label {
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.specialization-text {
    color: #1a202c;
    font-size: 0.9rem;
    line-height: 1.5;
}

.tutor-subjects {
    margin-bottom: 16px;
}

.tutor-subjects-label {
    font-size: 0.75rem;
    color: #6B7280;
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
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.btn-view-profile {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-view-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.empty-state i {
    font-size: 4rem;
    color: #D1D5DB;
    margin-bottom: 20px;
}

.empty-state h5 {
    color: #6B7280;
    font-weight: 600;
    margin-bottom: 8px;
}

.empty-state p {
    color: #9CA3AF;
    margin: 0;
}

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

.tutor-profile-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 2px solid #E5E7EB;
}

.tutor-profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin: 0 auto 16px;
    border: 4px solid #667eea;
    object-fit: cover;
}

.tutor-profile-avatar.placeholder {
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    color: white;
}

.tutor-profile-name {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
}

.tutor-profile-title {
    color: #667eea;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 12px;
}

.tutor-profile-rating {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
    padding: 6px 14px;
    border-radius: 20px;
    color: #f59e0b;
    font-weight: 700;
    border: 2px solid rgba(245, 158, 11, 0.2);
    font-size: 0.9rem;
}

.tutor-profile-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #667eea;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.info-item {
    background: #F9FAFB;
    padding: 14px;
    border-radius: 10px;
    border-left: 3px solid #667eea;
}

.info-label {
    font-size: 0.75rem;
    color: #6B7280;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.info-value {
    color: #1a202c;
    font-weight: 600;
    font-size: 0.95rem;
}

@media (max-width: 768px) {
    .tutors-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-stats {
        grid-template-columns: 1fr;
    }
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
    <a href="studentdashboard.php">
      <i class="fas fa-home"></i>
      <span>Dashboard</span>
    </a>
    <a href="student_myclass.php">
      <i class="fas fa-book-open"></i>
      <span>My Classes</span>
    </a>
    <a href="student_tutorprofile.php" class="active">
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
  <div class="header-section">
    <h2>Our Tutors 👨‍🏫</h2>
    <p>Discover the talented educators dedicated to your success</p>
  </div>

  <div class="quick-stats">
    <div class="stat-card primary">
      <div class="stat-icon">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-label">Total Tutors</div>
      <div class="stat-value"><?php echo $totalTutors; ?></div>
    </div>


    <div class="stat-card warning">
      <div class="stat-icon">
        <i class="fas fa-award"></i>
      </div>
      <div class="stat-label">Subjects Offered</div>
      <div class="stat-value">10</div>
    </div>
  </div>

  <div class="search-filter-bar">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" id="tutorSearch" placeholder="Search tutors by name, subject, or specialization...">
    </div>
    
    <div class="filter-tabs">
      <button class="filter-tab active" onclick="filterByCategory('all')">
        <i class="fas fa-th"></i> All Tutors
      </button>
      <button class="filter-tab" onclick="filterByCategory('programming')">
        <i class="fas fa-code"></i> Programming
      </button>
      <button class="filter-tab" onclick="filterByCategory('subjects')">
        <i class="fas fa-book"></i> Academic Subjects
      </button>
      <button class="filter-tab" onclick="filterByCategory('microsoft')">
        <i class="fab fa-microsoft"></i> Microsoft Office
      </button>
      <button class="filter-tab" onclick="filterByCategory('creative')">
        <i class="fas fa-palette"></i> Creative Arts
      </button>
    </div>
  </div>

  <?php if (!empty($tutors)): ?>
    <div class="tutors-grid" id="tutorsGrid">
      <?php foreach ($tutors as $tutor): ?>
        <?php $tutorId = isset($tutor[$idField]) ? $tutor[$idField] : 0; ?>
        <div class="tutor-card" 
             data-tutor-id="<?php echo $tutorId; ?>"
             data-tutor-name="<?php echo strtolower($tutor['tutor_name']); ?>" 
             data-tutor-subjects="<?php echo strtolower($tutor['subjects']); ?>"
             data-tutor-specialization="<?php echo strtolower($tutor['specialization']); ?>"
             onclick="viewTutorProfile(<?php echo $tutorId; ?>)">
          
          <div class="tutor-card-header">
            <div class="tutor-status-badge">Active</div>
            
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

          <div class="tutor-card-body">
            <h3 class="tutor-name"><?php echo htmlspecialchars($tutor['tutor_name']); ?></h3>
            <p class="tutor-title"><?php echo htmlspecialchars($tutor['education']); ?></p>

            <div class="tutor-stats-row">
              <div class="tutor-stat-item">
                <span class="tutor-stat-item-value"><?php echo $tutor['total_students']; ?></span>
                <span class="tutor-stat-item-label">Students</span>
              </div>
              <div class="tutor-stat-item">
                <span class="tutor-stat-item-value"><?php echo $tutor['experience']; ?></span>
                <span class="tutor-stat-item-label">Experience</span>
              </div>
            </div>

            <div class="tutor-specialization">
              <div class="specialization-label">Specialization</div>
              <div class="specialization-text">
                <?php echo htmlspecialchars(substr($tutor['specialization'], 0, 70)) . (strlen($tutor['specialization']) > 70 ? '...' : ''); ?>
              </div>
            </div>

            <div class="tutor-subjects">
              <div class="tutor-subjects-label">Teaching Subjects</div>
              <div class="subject-tags">
                <?php 
                $subjects = explode(',', $tutor['subjects']);
                $displaySubjects = array_slice($subjects, 0, 3);
                foreach ($displaySubjects as $subject): 
                ?>
                  <span class="subject-tag"><?php echo trim(htmlspecialchars($subject)); ?></span>
                <?php endforeach; ?>
                <?php if (count($subjects) > 3): ?>
                  <span class="subject-tag">+<?php echo count($subjects) - 3; ?> more</span>
                <?php endif; ?>
              </div>
            </div>

            <button class="btn-view-profile" onclick="event.stopPropagation(); viewTutorProfile(<?php echo $tutorId; ?>)">
              <i class="fas fa-eye"></i> View Profile
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-user-tie"></i>
      <h5>No Tutors Available</h5>
      <p>There are currently no tutors available. Please check back later.</p>
    </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="tutorProfileModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-user-circle"></i> Tutor Profile
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="tutorProfileContent">
        <div style="text-align: center; padding: 40px;">
          <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: #667eea;"></i>
          <p style="margin-top: 20px; color: #6B7280;">Loading tutor profile...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
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

function filterByCategory(category) {
    const tutorCards = document.querySelectorAll('.tutor-card');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    filterTabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    tutorCards.forEach(card => {
        const subjects = card.getAttribute('data-tutor-subjects').toLowerCase();
        const specialization = card.getAttribute('data-tutor-specialization').toLowerCase();
        
        let show = false;
        
        if (category === 'all') {
            show = true;
        } else if (category === 'programming') {
            show = subjects.includes('java') || subjects.includes('c++') || 
                   subjects.includes('python') || specialization.includes('programming');
        } else if (category === 'subjects') {
            show = subjects.includes('english') || subjects.includes('math') || 
                   subjects.includes('science') || subjects.includes('filipino');
        } else if (category === 'microsoft') {
            show = subjects.includes('ms word') || subjects.includes('ms excel') || 
                   subjects.includes('microsoft');
        } else if (category === 'creative') {
            show = subjects.includes('photo editing') || subjects.includes('video editing') || 
                   specialization.includes('creative');
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

function viewTutorProfile(tutorId) {
    console.log('Loading tutor profile for ID:', tutorId);
    
    const modal = new bootstrap.Modal(document.getElementById('tutorProfileModal'));
    modal.show();
    
    document.getElementById('tutorProfileContent').innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: #667eea;"></i>
            <p style="margin-top: 20px; color: #6B7280;">Loading tutor profile...</p>
        </div>
    `;
    
    fetch('get_tutor_details.php?id=' + tutorId)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            
            if (data.success) {
                const tutor = data.tutor;
                const content = `
                    <div class="tutor-profile-header">
                        ${tutor.profile_image && tutor.profile_image !== 'null' ? 
                            `<img src="${tutor.profile_image}" class="tutor-profile-avatar" alt="${tutor.tutor_name}">` :
                            `<div class="tutor-profile-avatar placeholder">${tutor.tutor_name.charAt(0)}</div>`
                        }
                        <h3 class="tutor-profile-name">${tutor.tutor_name}</h3>
                        <p class="tutor-profile-title">${tutor.education}</p>
                        <div class="tutor-profile-rating">
                            <i class="fas fa-star"></i>
                            ${tutor.rating} Rating
                        </div>
                    </div>
                    
                    <div class="tutor-profile-section">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Contact Information
                        </h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">${tutor.email}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value">${tutor.phone}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tutor-profile-section">
                        <h4 class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Professional Details
                        </h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Experience</div>
                                <div class="info-value">${tutor.experience}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total Students</div>
                                <div class="info-value">${tutor.total_students} Students</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tutor-profile-section">
                        <h4 class="section-title">
                            <i class="fas fa-lightbulb"></i>
                            Specialization
                        </h4>
                        <p style="color: #1a202c; line-height: 1.8;">${tutor.specialization}</p>
                    </div>
                    
                    <div class="tutor-profile-section">
                        <h4 class="section-title">
                            <i class="fas fa-book"></i>
                            Teaching Subjects
                        </h4>
                        <div class="subject-tags">
                            ${tutor.subjects.split(',').map(subject => 
                                `<span class="subject-tag">${subject.trim()}</span>`
                            ).join('')}
                        </div>
                    </div>
                `;
                document.getElementById('tutorProfileContent').innerHTML = content;
            } else {
                document.getElementById('tutorProfileContent').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ef4444;"></i>
                        <h5 style="margin-top: 20px; color: #1a202c;">Error Loading Profile</h5>
                        <p style="color: #6B7280;">${data.message || 'Could not load tutor information'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('tutorProfileContent').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f59e0b;"></i>
                    <h5 style="margin-top: 20px; color: #1a202c;">Connection Error</h5>
                    <p style="color: #6B7280;">Unable to connect to the server. Please try again.</p>
                </div>
            `;
        });
}
</script>

</body>
</html>