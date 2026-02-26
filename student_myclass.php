<!--Student My Class Page-->
<?php
session_start();
include "db_connect.php";

$student_id = $_SESSION['student_id'];

// Get student data
$sql = "SELECT student_name, email, subject_tutorials, computer_tutorials FROM users WHERE id = ? AND status = 'approved'";
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
$subject_tutorials = $student['subject_tutorials'];
$computer_tutorials = $student['computer_tutorials'];

// Parse enrolled tutorials
$enrolled_subjects = !empty($subject_tutorials) ? array_map('trim', explode(',', $subject_tutorials)) : [];
$enrolled_computers = !empty($computer_tutorials) ? array_map('trim', explode(',', $computer_tutorials)) : [];
$all_enrolled = array_merge($enrolled_subjects, $enrolled_computers);

// Fetch schedules for student
$schedules = [];
if (!empty($all_enrolled)) {
    $schedule_sql = "SELECT * FROM class_schedules WHERE students_enrolled = ? ORDER BY schedule_date ASC, start_time ASC";
    $stmt = $conn->prepare($schedule_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    while ($row = $schedule_result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

$student_initials = strtoupper(substr($student_name, 0, 1));

// Tutorial information
$tutorial_info = [
    'English' => ['icon' => 'fa-book', 'color' => '#667eea', 'description' => 'Grammar, Writing, Reading Comprehension'],
    'Math' => ['icon' => 'fa-calculator', 'color' => '#f59e0b', 'description' => 'Algebra, Geometry, Problem Solving'],
    'Reading' => ['icon' => 'fa-book-reader', 'color' => '#10b981', 'description' => 'Comprehension, Analysis, Critical Thinking'],
    'Science' => ['icon' => 'fa-flask', 'color' => '#3b82f6', 'description' => 'Biology, Chemistry, Physics'],
    'Filipino' => ['icon' => 'fa-language', 'color' => '#ef4444', 'description' => 'Gramatika, Pagsusulat, Pagbasa'],
    'Video Editing' => ['icon' => 'fa-video', 'color' => '#8b5cf6', 'description' => 'Adobe Premiere, Final Cut Pro'],
    'Photo Editing' => ['icon' => 'fa-image', 'color' => '#ec4899', 'description' => 'Photoshop, Lightroom, GIMP'],
    'Java' => ['icon' => 'fa-code', 'color' => '#f59e0b', 'description' => 'Object-Oriented Programming'],
    'Python' => ['icon' => 'fa-terminal', 'color' => '#3b82f6', 'description' => 'Data Structures, Algorithms'],
    'C++' => ['icon' => 'fa-braces', 'color' => '#6366f1', 'description' => 'Systems Programming, Algorithms'],
    'MS Word' => ['icon' => 'fa-file-word', 'color' => '#2b5797', 'description' => 'Document Creation, Formatting'],
    'MS Excel' => ['icon' => 'fa-file-excel', 'color' => '#217346', 'description' => 'Spreadsheets, Formulas, Charts']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Classes | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
/* === MATCHING STUDENTDASHBOARD.PHP DESIGN === */

body {
    background: #f8fafc;
}

/* Header Section - Exact Match */
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

/* QR Quick Access Card - Exact Match from Dashboard */
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

/* Stats Cards - Exact Match from Dashboard */
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

.stat-card.info::before {
    background: linear-gradient(90deg, #3b82f6, #2563eb);
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

.stat-card.info .stat-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
    color: #3b82f6;
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

/* Classes Grid */
.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.class-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.class-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
}

.class-card-header {
    padding: 28px 24px;
    position: relative;
    overflow: hidden;
}

.class-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.class-icon-wrapper i {
    font-size: 1.8rem;
    color: white;
}

.class-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 6px;
}

.class-description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.85rem;
    line-height: 1.4;
}

.class-card-body {
    padding: 0 24px 24px;
}

.class-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.stat-item {
    background: #F9FAFB;
    padding: 14px;
    border-radius: 10px;
    text-align: center;
}

.stat-item-label {
    font-size: 0.75rem;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    font-weight: 600;
}

.stat-item-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a202c;
}

.next-class-box {
    background: #F9FAFB;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 16px;
}

.next-class-label {
    font-size: 0.7rem;
    color: #6B7280;
    margin-bottom: 6px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.next-class-time {
    font-weight: 600;
    color: #1a202c;
    font-size: 0.9rem;
}

.btn-view-schedule {
    width: 100%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}

.btn-view-schedule:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Content Card - Exact Match */
.content-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
}

.content-card h5 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Schedule Items - Exact Match from Dashboard */
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
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Empty State - Exact Match */
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
    margin: 0 0 24px 0;
}

.btn-enroll {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.btn-enroll:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

@media (max-width: 768px) {
    .classes-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .schedule-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="student-profile">
      <div class="student-avatar"><?php echo $student_initials; ?></div>
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
    <a href="student_myclass.php" class="active">
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
  <!-- Header Section - Matching Dashboard Style -->
  <div class="header-section">
    <h2>My Classes 📚</h2>
    <p>View your enrolled tutorials and upcoming schedules</p>
  </div>

  <?php if (count($all_enrolled) > 0): ?>
    <!-- Statistics Cards - Matching Dashboard -->
    <div class="quick-stats">
      <div class="stat-card primary">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-label">Enrolled Classes</div>
        <div class="stat-value"><?php echo count($all_enrolled); ?></div>
      </div>

      <div class="stat-card success">
        <div class="stat-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-label">Total Sessions</div>
        <div class="stat-value"><?php echo count($schedules); ?></div>
      </div>

      <div class="stat-card warning">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-label">Upcoming Classes</div>
        <div class="stat-value">
          <?php 
            $upcoming = array_filter($schedules, function($s) {
              return strtotime($s['schedule_date']) >= strtotime('today');
            });
            echo count($upcoming);
          ?>
        </div>
      </div>

      <div class="stat-card info">
        <div class="stat-icon">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="stat-label">Status</div>
        <div class="stat-value" style="font-size: 1.3rem; color: #10b981;">Active</div>
      </div>
    </div>

    <!-- Enrolled Classes Grid -->
    <div class="classes-grid">
      <?php foreach ($all_enrolled as $tutorial): ?>
      <?php 
        $info = isset($tutorial_info[$tutorial]) ? $tutorial_info[$tutorial] : ['icon' => 'fa-book', 'color' => '#667eea', 'description' => 'Tutorial Program'];
        $tutorial_schedules = array_filter($schedules, function($s) use ($tutorial) {
          return $s['subject'] === $tutorial;
        });
        $next_class = !empty($tutorial_schedules) ? reset($tutorial_schedules) : null;
      ?>
        <div class="class-card">
          <div class="class-card-header" style="background: linear-gradient(135deg, <?php echo $info['color']; ?>, <?php echo $info['color']; ?>dd);">
            <div class="class-icon-wrapper" style="background: rgba(0, 0, 0, 0.15);">
              <i class="fas <?php echo $info['icon']; ?>"></i>
            </div>
            <h4 class="class-title"><?php echo htmlspecialchars($tutorial); ?></h4>
            <p class="class-description"><?php echo $info['description']; ?></p>
          </div>

          <div class="class-card-body">
            <div class="class-stats">
              <div class="stat-item">
                <div class="stat-item-label">Sessions</div>
                <div class="stat-item-value"><?php echo count($tutorial_schedules); ?></div>
              </div>
              <div class="stat-item">
                <div class="stat-item-label">Status</div>
                <div class="stat-item-value" style="color: #10b981; font-size: 1rem;">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>

            <?php if ($next_class): ?>
              <div class="next-class-box">
                <div class="next-class-label">Next Class</div>
                <div class="next-class-time">
                  <i class="fas fa-calendar" style="color: #667eea;"></i>
                  <?php echo date('M d, Y', strtotime($next_class['schedule_date'])); ?> • 
                  <?php echo date('h:i A', strtotime($next_class['start_time'])); ?>
                </div>
              </div>
            <?php endif; ?>

            <button class="btn-view-schedule" onclick="scrollToSchedule('<?php echo htmlspecialchars($tutorial); ?>')">
              <i class="fas fa-calendar-alt"></i>
              View Schedule
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Schedule Section -->
    <div class="content-card" id="schedule-section">
      <h5>
        <i class="fas fa-calendar-week"></i>
        Class Schedule
      </h5>

      <?php if (count($schedules) > 0): ?>
        <?php foreach ($schedules as $schedule): ?>
          <div class="schedule-item" data-subject="<?php echo htmlspecialchars($schedule['subject']); ?>">
            <!-- Schedule Header with Date and Time -->
            <div class="schedule-header">
              <span class="schedule-date-badge">
                <i class="fas fa-calendar"></i>
                <?php echo date('l, F j, Y', strtotime($schedule['schedule_date'])); ?>
              </span>
              <div class="schedule-time">
                <i class="fas fa-clock"></i>
                <?php echo date("h:i A", strtotime($schedule['start_time'])) ?>
                -
                <?php echo date("h:i A", strtotime($schedule['end_time'])) ?>
              </div>
            </div>
            
            <!-- Schedule Body with Subject -->
            <div class="schedule-body">
              <div class="schedule-title">
                <?php 
                  $icon = isset($tutorial_info[$schedule['subject']]['icon']) ? $tutorial_info[$schedule['subject']]['icon'] : 'fa-book';
                  $color = isset($tutorial_info[$schedule['subject']]['color']) ? $tutorial_info[$schedule['subject']]['color'] : '#667eea';
                ?>
                <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                <?php echo htmlspecialchars($schedule['subject']); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state" style="padding: 40px;">
          <i class="fas fa-calendar-times"></i>
          <h5>No Scheduled Classes Yet</h5>
          <p>Your class schedule will appear here once the admin sets it up.</p>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- Empty State -->
    <div class="content-card">
      <div class="empty-state">
        <i class="fas fa-book-open"></i>
        <h5>No Classes Enrolled</h5>
        <p>You haven't enrolled in any tutorials yet. Start your learning journey today!</p>
        <a href="studentdashboard.php" class="btn-enroll">
          <i class="fas fa-plus-circle"></i>
          Enroll in Tutorials
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function scrollToSchedule(subject) {
  const scheduleSection = document.getElementById('schedule-section');
  scheduleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  
  // Highlight the specific subject schedules
  setTimeout(() => {
    const items = document.querySelectorAll(`[data-subject="${subject}"]`);
    items.forEach(item => {
      item.style.background = 'linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1))';
      item.style.transform = 'scale(1.02)';
      setTimeout(() => {
        item.style.background = '';
        item.style.transform = '';
      }, 2000);
    });
  }, 500);
}
</script>

</body>
</html>