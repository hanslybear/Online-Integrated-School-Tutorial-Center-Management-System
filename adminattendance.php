<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Get today's date
$today = date('Y-m-d');

// Fetch attendance records
$sql = "SELECT 
          a.id,
          a.student_id,
          a.date,
          a.status,
          a.time_in,
          a.remarks,
          u.student_name,
          u.email,
          u.qr_code
        FROM attendance a
        LEFT JOIN users u ON a.student_id = u.id
        WHERE u.status = 'approved'
        ORDER BY a.date DESC, u.student_name ASC";

$result = $conn->query($sql);

$attendance_records = [];
while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}

// Calculate statistics
$total_present = 0;
$total_absent = 0;
$total_late = 0;
$total_excused = 0;

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

// Get all approved students for marking attendance
$students_sql = "SELECT id, student_name, email, qr_code FROM users WHERE status = 'approved' ORDER BY student_name ASC";
$students_result = $conn->query($students_sql);
$students = [];
while ($student = $students_result->fetch_assoc()) {
    $students[] = $student;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Management | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
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

.page-header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
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

.current-date {
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
  padding: 12px 20px;
  border-radius: 12px;
  color: var(--primary);
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.header-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-qr-scan {
  background: linear-gradient(135deg, #8b5cf6, #6366f1);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-qr-scan:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(139, 92, 246, 0.4);
  color: white;
}

.btn-mark-attendance {
  background: linear-gradient(135deg, var(--success), #059669);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-mark-attendance:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
  color: white;
}

.btn-export {
  background: linear-gradient(135deg, var(--info), #2563EB);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-export:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
  color: white;
}

/* QR Scanner Styles */
.qr-scanner-container {
  position: relative;
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}

#qr-video {
  width: 100%;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.scanner-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 250px;
  height: 250px;
  border: 3px solid #8b5cf6;
  border-radius: 16px;
  pointer-events: none;
}

.scanner-corner {
  position: absolute;
  width: 40px;
  height: 40px;
  border: 4px solid #8b5cf6;
}

.scanner-corner.top-left {
  top: -4px;
  left: -4px;
  border-right: none;
  border-bottom: none;
  border-radius: 16px 0 0 0;
}

.scanner-corner.top-right {
  top: -4px;
  right: -4px;
  border-left: none;
  border-bottom: none;
  border-radius: 0 16px 0 0;
}

.scanner-corner.bottom-left {
  bottom: -4px;
  left: -4px;
  border-right: none;
  border-top: none;
  border-radius: 0 0 0 16px;
}

.scanner-corner.bottom-right {
  bottom: -4px;
  right: -4px;
  border-left: none;
  border-top: none;
  border-radius: 0 0 16px 0;
}

.scanner-line {
  position: absolute;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, transparent, #8b5cf6, transparent);
  animation: scan 2s ease-in-out infinite;
}

@keyframes scan {
  0%, 100% {
    top: 0;
  }
  50% {
    top: calc(100% - 3px);
  }
}

.scan-result {
  margin-top: 20px;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  font-weight: 600;
}

.scan-result.success {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
  border: 2px solid var(--success);
  color: var(--success);
}

.scan-result.error {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
  border: 2px solid var(--danger);
  color: var(--danger);
}

.scan-instructions {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(99, 102, 241, 0.1));
  border: 2px dashed #8b5cf6;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  text-align: center;
}

.scan-instructions h5 {
  color: #8b5cf6;
  font-weight: 700;
  margin-bottom: 12px;
}

.scan-instructions p {
  color: #6B7280;
  margin: 0;
}

.attendance-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.attendance-stat-card.present::before {
  background: linear-gradient(90deg, var(--success), #059669);
}

.attendance-stat-card.absent::before {
  background: linear-gradient(90deg, var(--danger), #DC2626);
}

.attendance-stat-card.late::before {
  background: linear-gradient(90deg, var(--warning), #D97706);
}

.attendance-stat-card.excused::before {
  background: linear-gradient(90deg, var(--info), #2563EB);
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

.attendance-stat-card.present .stat-icon-lg {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: var(--success);
}

.attendance-stat-card.absent .stat-icon-lg {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
  color: var(--danger);
}

.attendance-stat-card.late .stat-icon-lg {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: var(--warning);
}

.attendance-stat-card.excused .stat-icon-lg {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
  color: var(--info);
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
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
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

.student-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar-sm {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1rem;
}

.student-details h6 {
  margin: 0;
  font-weight: 600;
  color: var(--dark);
  font-size: 0.95rem;
}

.student-details p {
  margin: 0;
  font-size: 0.85rem;
  color: #6B7280;
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
  color: var(--success);
  border: 2px solid var(--success);
}

.status-badge.absent {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
  color: var(--danger);
  border: 2px solid var(--danger);
}

.status-badge.late {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: var(--warning);
  border: 2px solid var(--warning);
}

.status-badge.excused {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
  color: var(--info);
  border: 2px solid var(--info);
}

.time-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  background: #F3F4F6;
  color: #4B5563;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.action-btns {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  color: white;
  cursor: pointer;
}

.btn-action.edit {
  background: linear-gradient(135deg, var(--warning), #D97706);
}

.btn-action.delete {
  background: linear-gradient(135deg, var(--danger), #DC2626);
}

.btn-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.student-select-list {
  max-height: 400px;
  overflow-y: auto;
}

.student-attendance-item {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s ease;
}

.student-attendance-item:hover {
  background: #F3F4F6;
  transform: translateX(4px);
}

.student-attendance-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-selector {
  display: flex;
  gap: 8px;
}

.status-btn {
  padding: 8px 16px;
  border: 2px solid #E5E7EB;
  background: white;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
}

.status-btn:hover {
  transform: translateY(-2px);
}

.status-btn.present.selected {
  border-color: var(--success);
  background: var(--success);
  color: white;
}

.status-btn.absent.selected {
  border-color: var(--danger);
  background: var(--danger);
  color: white;
}

.status-btn.late.selected {
  border-color: var(--warning);
  background: var(--warning);
  color: white;
}

.status-btn.excused.selected {
  border-color: var(--info);
  background: var(--info);
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

.attendance-table tbody tr {
  animation: fadeInUp 0.4s ease forwards;
}

@media (max-width: 768px) {
  .attendance-stats {
    grid-template-columns: 1fr;
  }
  
  .header-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-mark-attendance, .btn-export, .btn-qr-scan {
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
    <a href="adminstudents.php">
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
    <a href="adminattendance.php" class="active">
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
    <div class="page-header-top">
      <div>
        <h2>
          <i class="fas fa-clipboard-check"></i>
          Attendance Management
        </h2>
        <div class="current-date">
          <i class="fas fa-calendar-day"></i>
          <?php echo date('l, F d, Y'); ?>
        </div>
      </div>
      <div class="header-actions">
        <button class="btn btn-qr-scan" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
          <i class="fas fa-qrcode"></i>
          QR Scanner
        </button>
        <button class="btn btn-export">
          <i class="fas fa-file-excel"></i>
          Export
        </button>
        <button class="btn btn-mark-attendance" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
          <i class="fas fa-check-double"></i>
          Mark Manually
        </button>
      </div>
    </div>
  </div>

  <div class="attendance-stats">
    <div class="attendance-stat-card present">
      <div class="stat-icon-lg">
        <i class="fas fa-user-check"></i>
      </div>
      <div class="stat-label-lg">Present</div>
      <div class="stat-value-lg"><?php echo $total_present; ?></div>
    </div>

    <div class="attendance-stat-card absent">
      <div class="stat-icon-lg">
        <i class="fas fa-user-times"></i>
      </div>
      <div class="stat-label-lg">Absent</div>
      <div class="stat-value-lg"><?php echo $total_absent; ?></div>
    </div>

    <div class="attendance-stat-card late">
      <div class="stat-icon-lg">
        <i class="fas fa-user-clock"></i>
      </div>
      <div class="stat-label-lg">Late</div>
      <div class="stat-value-lg"><?php echo $total_late; ?></div>
    </div>

    <div class="attendance-stat-card excused">
      <div class="stat-icon-lg">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="stat-label-lg">Excused</div>
      <div class="stat-value-lg"><?php echo $total_excused; ?></div>
    </div>
  </div>

  <div class="filter-section">
    <form method="GET" action="">
      <div class="row g-3">
        <div class="col-md-3">
          <label>Search Student</label>
          <input type="text" class="form-control" name="search" placeholder="Name or email...">
        </div>
        <div class="col-md-2">
          <label>Status</label>
          <select class="form-control" name="status">
            <option value="">All Status</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="excused">Excused</option>
          </select>
        </div>
        <div class="col-md-2">
          <label>Date From</label>
          <input type="date" class="form-control" name="date_from">
        </div>
        <div class="col-md-2">
          <label>Date To</label>
          <input type="date" class="form-control" name="date_to">
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600;">
            <i class="fas fa-filter"></i> Apply Filters
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="attendance-table-container">
    <table class="attendance-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Status</th>
          <th>Remarks</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($attendance_records) > 0): ?>
          <?php foreach ($attendance_records as $record): ?>
            <tr>
              <td>
                <div class="student-info">
                  <div class="student-avatar-sm">
                    <?php echo strtoupper(substr($record['student_name'], 0, 1)); ?>
                  </div>
                  <div class="student-details">
                    <h6><?php echo htmlspecialchars($record['student_name']); ?></h6>
                    <p><?php echo htmlspecialchars($record['email']); ?></p>
                  </div>
                </div>
              </td>
              <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
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
              <td><?php echo $record['remarks'] ? htmlspecialchars($record['remarks']) : '—'; ?></td>
              <td>
                <div class="action-btns">
                  <button class="btn-action edit" onclick="editAttendance(<?php echo $record['id']; ?>)" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-action delete" onclick="deleteAttendance(<?php echo $record['id']; ?>)" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center" style="padding: 60px;">
              <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #D1D5DB; margin-bottom: 16px;"></i>
              <h5 style="color: #6B7280;">No attendance records found</h5>
              <p style="color: #9CA3AF;">Start by scanning QR codes or marking attendance manually</p>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrScannerModalLabel">
          <i class="fas fa-qrcode"></i> QR Code Attendance Scanner
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanner()"></button>
      </div>
      <div class="modal-body">
        <div class="scan-instructions">
          <h5><i class="fas fa-info-circle"></i> How to Use</h5>
          <p>Position the student's QR code in front of your webcam. The system will automatically scan and record time-in.</p>
        </div>

        <div class="qr-scanner-container">
          <video id="qr-video" autoplay></video>
          <div class="scanner-overlay">
            <div class="scanner-corner top-left"></div>
            <div class="scanner-corner top-right"></div>
            <div class="scanner-corner bottom-left"></div>
            <div class="scanner-corner bottom-right"></div>
            <div class="scanner-line"></div>
          </div>
        </div>

        <div id="scan-result"></div>
      </div>
    </div>
  </div>
</div>

<!-- Manual Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-labelledby="markAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="markAttendanceModalLabel">
          <i class="fas fa-check-double"></i> Mark Attendance for <?php echo date('F d, Y'); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="markAttendanceForm" action="save_attendance_batch.php" method="POST">
          <input type="hidden" name="date" value="<?php echo $today; ?>">
          
          <div class="student-select-list">
            <?php foreach ($students as $student): ?>
              <div class="student-attendance-item">
                <div class="student-attendance-info">
                  <div class="student-avatar-sm">
                    <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
                  </div>
                  <div>
                    <h6 style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($student['student_name']); ?></h6>
                    <p style="margin: 0; font-size: 0.85rem; color: #6B7280;"><?php echo htmlspecialchars($student['email']); ?></p>
                  </div>
                </div>
                
                <div class="status-selector">
                  <input type="hidden" name="students[]" value="<?php echo $student['id']; ?>">
                  <button type="button" class="status-btn present" onclick="selectStatus(this, <?php echo $student['id']; ?>, 'present')">
                    <i class="fas fa-check"></i> Present
                  </button>
                  <button type="button" class="status-btn absent" onclick="selectStatus(this, <?php echo $student['id']; ?>, 'absent')">
                    <i class="fas fa-times"></i> Absent
                  </button>
                  <button type="button" class="status-btn late" onclick="selectStatus(this, <?php echo $student['id']; ?>, 'late')">
                    <i class="fas fa-clock"></i> Late
                  </button>
                  <button type="button" class="status-btn excused" onclick="selectStatus(this, <?php echo $student['id']; ?>, 'excused')">
                    <i class="fas fa-shield-alt"></i> Excused
                  </button>
                  <input type="hidden" name="status_<?php echo $student['id']; ?>" id="status_<?php echo $student['id']; ?>" value="">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" form="markAttendanceForm" class="btn btn-success">
          <i class="fas fa-save"></i> Save Attendance
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let video = null;
let canvas = null;
let canvasContext = null;
let scanning = false;

// Initialize QR Scanner when modal is shown
document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function () {
  startScanner();
});

// Stop scanner when modal is closed
document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function () {
  stopScanner();
});

function startScanner() {
  video = document.getElementById('qr-video');
  canvas = document.createElement('canvas');
  canvasContext = canvas.getContext('2d');
  scanning = true;

  navigator.mediaDevices.getUserMedia({ 
    video: { 
      facingMode: 'environment',
      width: { ideal: 1280 },
      height: { ideal: 720 }
    } 
  })
  .then(function(stream) {
    video.srcObject = stream;
    video.setAttribute('playsinline', true);
    video.play();
    requestAnimationFrame(tick);
  })
  .catch(function(err) {
    console.error('Error accessing webcam:', err);
    showScanResult('error', 'Unable to access webcam. Please check permissions.');
  });
}

function stopScanner() {
  scanning = false;
  if (video && video.srcObject) {
    video.srcObject.getTracks().forEach(track => track.stop());
    video.srcObject = null;
  }
  document.getElementById('scan-result').innerHTML = '';
}

function tick() {
  if (!scanning || !video.readyState === video.HAVE_ENOUGH_DATA) {
    if (scanning) requestAnimationFrame(tick);
    return;
  }

  canvas.height = video.videoHeight;
  canvas.width = video.videoWidth;
  canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
  
  const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
  const code = jsQR(imageData.data, imageData.width, imageData.height, {
    inversionAttempts: 'dontInvert',
  });

  if (code) {
    handleQRCode(code.data);
  }

  if (scanning) {
    requestAnimationFrame(tick);
  }
}

function handleQRCode(qrData) {
  // Stop scanning temporarily to prevent multiple scans
  scanning = false;

  // Send AJAX request to mark attendance
  fetch('process_qr_attendance.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      qr_code: qrData,
      date: '<?php echo $today; ?>'
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showScanResult('success', `✓ ${data.student_name} marked as PRESENT at ${data.time_in}`);
      // Reload page after 2 seconds to update the table
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      showScanResult('error', data.message || 'Failed to mark attendance');
      // Resume scanning after 3 seconds
      setTimeout(() => {
        scanning = true;
        document.getElementById('scan-result').innerHTML = '';
        requestAnimationFrame(tick);
      }, 3000);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showScanResult('error', 'Error processing attendance');
    setTimeout(() => {
      scanning = true;
      document.getElementById('scan-result').innerHTML = '';
      requestAnimationFrame(tick);
    }, 3000);
  });
}

function showScanResult(type, message) {
  const resultDiv = document.getElementById('scan-result');
  resultDiv.className = `scan-result ${type}`;
  resultDiv.innerHTML = `
    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="font-size: 2rem; margin-bottom: 10px;"></i>
    <div style="font-size: 1.1rem;">${message}</div>
  `;
}

function selectStatus(btn, studentId, status) {
  const parent = btn.parentElement;
  parent.querySelectorAll('.status-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('status_' + studentId).value = status;
}

function editAttendance(id) {
  // Implement edit functionality
  console.log('Edit attendance:', id);
}

function deleteAttendance(id) {
  if (confirm('Are you sure you want to delete this attendance record?')) {
    window.location.href = 'delete_attendance_process.php?id=' + id;
  }
}
</script>

</body>
</html>