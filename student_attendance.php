<!--Student Attendance Page-->
<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: homepage.php?error=Access denied");
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

// Get filter parameters
$search_date = isset($_GET['date']) ? $_GET['date'] : '';
$search_status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build the query - REMOVED time_out from SELECT
$sql = "SELECT 
          a.id,
          a.date,
          a.status,
          a.time_in,
          a.remarks,
          a.created_at
        FROM attendance a
        WHERE a.student_id = ?";

$params = [$student_id];
$types = "i";

// Add filters
if ($search_date) {
    $sql .= " AND a.date = ?";
    $params[] = $search_date;
    $types .= "s";
}

if ($search_status) {
    $sql .= " AND a.status = ?";
    $params[] = $search_status;
    $types .= "s";
}

if ($date_from) {
    $sql .= " AND a.date >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $sql .= " AND a.date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY a.date DESC, a.time_in DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$attendance_records = [];
while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}

// Calculate statistics
$total_present = 0;
$total_absent = 0;
$total_late = 0;
$total_excused = 0;
$total_records = count($attendance_records);

foreach ($attendance_records as $record) {
    switch ($record['status']) {
        case 'present':
            $total_present++;
            break;
        case 'absent':
            $total_absent++;
            break;
        case 'late':
            $total_late++;
            break;
        case 'excused':
            $total_excused++;
            break;
    }
}

// Calculate attendance rate
$attendance_rate = $total_records > 0 ? round(($total_present + $total_late) / $total_records * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Attendance | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">
<style>
.page-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  padding: 28px 32px;
  border-radius: 20px;
  margin-bottom: 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.page-header h2 {
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 8px 0;
  font-size: 1.75rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-header h2 i {
  color: #667eea;
}

.page-header p {
  color: #6B7280;
  margin: 0;
  font-size: 1rem;
}

.current-date {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  padding: 10px 18px;
  border-radius: 12px;
  color: #667eea;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  font-size: 0.95rem;
}

.attendance-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.attendance-stat-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.attendance-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.attendance-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.attendance-stat-card.rate::before {
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.attendance-stat-card.present::before {
  background: linear-gradient(90deg, #10b981, #059669);
}

.attendance-stat-card.absent::before {
  background: linear-gradient(90deg, #ef4444, #dc2626);
}

.attendance-stat-card.late::before {
  background: linear-gradient(90deg, #f59e0b, #d97706);
}

.attendance-stat-card.excused::before {
  background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.stat-icon-lg {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 16px;
}

.attendance-stat-card.rate .stat-icon-lg {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
  color: #667eea;
}

.attendance-stat-card.present .stat-icon-lg {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: #10b981;
}

.attendance-stat-card.absent .stat-icon-lg {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
  color: #ef4444;
}

.attendance-stat-card.late .stat-icon-lg {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: #f59e0b;
}

.attendance-stat-card.excused .stat-icon-lg {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
  color: #3b82f6;
}

.stat-label-lg {
  font-size: 0.9rem;
  color: #6B7280;
  font-weight: 600;
  margin-bottom: 8px;
}

.stat-value-lg {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--dark);
}

.filter-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.filter-section label {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.filter-section input,
.filter-section select {
  border-radius: 10px;
  border: 2px solid #E5E7EB;
  padding: 10px 16px;
  transition: all 0.3s ease;
}

.filter-section input:focus,
.filter-section select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-filter {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-filter:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  color: white;
}

.btn-reset {
  background: #6B7280;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-reset:hover {
  background: #4B5563;
  transform: translateY(-2px);
  color: white;
}

.attendance-table-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  overflow-x: auto;
}

.attendance-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
}

.attendance-table thead th {
  background: transparent;
  color: #6B7280;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  padding: 12px 16px;
  border: none;
}

.attendance-table tbody tr {
  background: #F9FAFB;
  transition: all 0.3s ease;
  animation: fadeInUp 0.4s ease forwards;
}

.attendance-table tbody tr:hover {
  background: #F3F4F6;
  transform: scale(1.01);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.attendance-table tbody td {
  padding: 18px 16px;
  border: none;
  vertical-align: middle;
}

.attendance-table tbody tr td:first-child {
  border-radius: 10px 0 0 10px;
}

.attendance-table tbody tr td:last-child {
  border-radius: 0 10px 10px 0;
}

.date-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 700;
  color: #667eea;
  font-size: 0.9rem;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status-badge.present {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: #10b981;
  border: 2px solid #10b981;
}

.status-badge.absent {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
  color: #ef4444;
  border: 2px solid #ef4444;
}

.status-badge.late {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: #f59e0b;
  border: 2px solid #f59e0b;
}

.status-badge.excused {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.time-badge {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  background: #F3F4F6;
  color: #4B5563;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
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

@media (max-width: 768px) {
  .attendance-stats {
    grid-template-columns: 1fr;
  }
  
  .qr-quick-access-content {
    flex-direction: column;
    text-align: center;
  }
  
  .btn-view-qr {
    width: 100%;
    justify-content: center;
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
    <a href="student_tutorprofile.php">
      <i class="fas fa-chalkboard-teacher"></i>
      <span>List of Tutor</span>
    </a>
    <a href="student_attendance.php" class="active">
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
  <div class="page-header">
    <h2>
      <i class="fas fa-clipboard-check"></i>
      My Attendance Records
    </h2>
    <p>Track your attendance and punctuality</p>
    <div class="current-date">
      <i class="fas fa-calendar-day"></i>
      <?php echo date('l, F d, Y'); ?>
    </div>
  </div>

  <!-- QR CODE QUICK ACCESS -->
  <div class="qr-quick-access">
    <div class="qr-quick-access-content">
      <div class="qr-quick-access-text">
        <h4><i class="fas fa-qrcode"></i> Quick Check-In</h4>
        <p>Show your QR code for instant attendance marking</p>
      </div>
      <a href="student_qr_display.php" class="btn-view-qr">
        <i class="fas fa-eye"></i>
        View My QR Code
      </a>
    </div>
  </div>

  <!-- ATTENDANCE STATISTICS -->
  <div class="attendance-stats">
    <div class="attendance-stat-card present">
      <div class="stat-icon-lg">
        <i class="fas fa-user-check"></i>
      </div>
      <div class="stat-label-lg">Present</div>
      <div class="stat-value-lg"><?php echo $total_present; ?></div>
    </div>

    <div class="attendance-stat-card late">
      <div class="stat-icon-lg">
        <i class="fas fa-user-clock"></i>
      </div>
      <div class="stat-label-lg">Late</div>
      <div class="stat-value-lg"><?php echo $total_late; ?></div>
    </div>

    <div class="attendance-stat-card absent">
      <div class="stat-icon-lg">
        <i class="fas fa-user-times"></i>
      </div>
      <div class="stat-label-lg">Absent</div>
      <div class="stat-value-lg"><?php echo $total_absent; ?></div>
    </div>

    <div class="attendance-stat-card excused">
      <div class="stat-icon-lg">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="stat-label-lg">Excused</div>
      <div class="stat-value-lg"><?php echo $total_excused; ?></div>
    </div>
  </div>

  <!-- ATTENDANCE TABLE (TIME-IN ONLY - NO TIME-OUT) -->
  <div class="attendance-table-container">
    <table class="attendance-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Day</th>
          <th>Time In</th>
          <th>Status</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($attendance_records) > 0): ?>
          <?php foreach ($attendance_records as $record): ?>
            <tr>
              <td>
                <span class="date-badge">
                  <i class="fas fa-calendar"></i>
                  <?php echo date('M d, Y', strtotime($record['date'])); ?>
                </span>
              </td>
              <td>
                <strong><?php echo date('l', strtotime($record['date'])); ?></strong>
              </td>
              <td>
                <?php if ($record['time_in']): ?>
                  <span class="time-badge">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo date('h:i A', strtotime($record['time_in'])); ?>
                  </span>
                <?php else: ?>
                  <span style="color: #9CA3AF;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="status-badge <?php echo $record['status']; ?>">
                  <?php 
                    $icons = [
                      'present' => 'fa-check-circle',
                      'absent' => 'fa-times-circle',
                      'late' => 'fa-clock',
                      'excused' => 'fa-shield-alt'
                    ];
                  ?>
                  <i class="fas <?php echo isset($icons[$record['status']]) ? $icons[$record['status']] : 'fa-circle'; ?>"></i>
                  <?php echo ucfirst($record['status']); ?>
                </span>
              </td>
              <td>
                <?php echo $record['remarks'] ? htmlspecialchars($record['remarks']) : '—'; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5>No Attendance Records Found</h5>
                <p>
                  <?php 
                    if ($search_date || $search_status || $date_from || $date_to) {
                      echo "Try adjusting your filters to see more results";
                    } else {
                      echo "Your attendance records will appear here once they're marked";
                    }
                  ?>
                </p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>