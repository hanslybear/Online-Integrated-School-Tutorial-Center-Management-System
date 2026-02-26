<!--Student Billing Page-->
<?php
session_start();
include "db_connect.php";

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

// Fetch billing records for the student
$billing_sql = "SELECT * FROM billing WHERE student_id = ? ORDER BY created_at DESC";
$billing_stmt = $conn->prepare($billing_sql);
$billing_stmt->bind_param("i", $student_id);
$billing_stmt->execute();
$billing_result = $billing_stmt->get_result();

$billing_records = [];
$total_amount = 0;
$paid_amount = 0;
$pending_amount = 0;

while ($row = $billing_result->fetch_assoc()) {
    $billing_records[] = $row;
    $total_amount += $row['amount'];
    if ($row['status'] === 'paid') {
        $paid_amount += $row['amount'];
    } else {
        $pending_amount += $row['amount'];
    }
}

$student_initials = strtoupper(substr($student['student_name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Billing | ACTS Learning Center</title>
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

.stat-detail {
    font-size: 0.85rem;
    color: #9CA3AF;
    margin-top: 4px;
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

/* Table Styles */
.table-responsive {
    margin-top: 20px;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: #F9FAFB;
    border: none;
    color: #6B7280;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 14px 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody td {
    border: none;
    padding: 16px 12px;
    border-bottom: 1px solid #E5E7EB;
    color: #4a5568;
    font-size: 0.95rem;
    vertical-align: middle;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
}

.amount-cell {
    font-weight: 700;
    color: #1a202c;
    font-size: 1rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-paid {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.3);
}

.status-pending {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
    color: #f59e0b;
    border: 2px solid rgba(245, 158, 11, 0.3);
}

.status-overdue {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
    color: #ef4444;
    border: 2px solid rgba(239, 68, 68, 0.3);
}

.due-date {
    color: #6B7280;
    font-size: 0.9rem;
}

.due-date.overdue {
    color: #ef4444;
    font-weight: 600;
}

/* Empty State - Exact Match */
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

/* Print Button */
.btn-print {
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
    margin-top: 20px;
    cursor: pointer;
}

.btn-print:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

@media (max-width: 768px) {
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 10px 8px;
    }
}

@media print {
    body {
        background: white;
    }
    
    .sidebar,
    .btn-print,
    .qr-quick-access,
    .quick-stats {
        display: none;
    }
    
    .main {
        margin-left: 0;
        padding: 20px;
    }
    
    .content-card {
        box-shadow: none;
        border: 1px solid #E5E7EB;
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
        <h4><?php echo htmlspecialchars($student['student_name']); ?></h4>
        <p><?php echo htmlspecialchars($student['email']); ?></p>
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
    <a href="student_attendance.php">
      <i class="fas fa-clipboard-check"></i>
      <span>Attendance</span>
    </a>
    <a href="student_billing.php" class="active">
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
    <h2>Billing & Payments 💰</h2>
    <p>Track your payments and outstanding balances</p>
  </div>

  <!-- Statistics Cards - Matching Dashboard -->
  <div class="quick-stats">
    <div class="stat-card primary">
      <div class="stat-icon">
        <i class="fas fa-money-bill-wave"></i>
      </div>
      <div class="stat-label">Total Amount</div>
      <div class="stat-value">₱<?php echo number_format($total_amount, 2); ?></div>
      <div class="stat-detail">All charges</div>
    </div>

    <div class="stat-card success">
      <div class="stat-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-label">Paid Amount</div>
      <div class="stat-value">₱<?php echo number_format($paid_amount, 2); ?></div>
      <div class="stat-detail">
        <?php echo count(array_filter($billing_records, function($b) { return $b['status'] === 'paid'; })); ?> 
        transaction<?php echo count(array_filter($billing_records, function($b) { return $b['status'] === 'paid'; })) != 1 ? 's' : ''; ?>
      </div>
    </div>

    <div class="stat-card warning">
      <div class="stat-icon">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <div class="stat-label">Pending Amount</div>
      <div class="stat-value">₱<?php echo number_format($pending_amount, 2); ?></div>
      <div class="stat-detail">
        <?php echo count(array_filter($billing_records, function($b) { return $b['status'] !== 'paid'; })); ?> 
        pending
      </div>
    </div>
  </div>

  <!-- Billing Records Table -->
  <div class="content-card">
    <h5>
      <i class="fas fa-history"></i>
      Billing History
    </h5>

    <?php if (count($billing_records) > 0): ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Due Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($billing_records as $bill): ?>
              <?php 
                $is_overdue = ($bill['status'] !== 'paid' && strtotime($bill['due_date']) < time());
                $status_class = $bill['status'];
                if ($is_overdue) {
                  $status_class = 'overdue';
                }
              ?>
              <tr>
                <td><?php echo date('M d, Y', strtotime($bill['created_at'])); ?></td>
                <td>
                  <strong><?php echo htmlspecialchars(!empty($bill['description']) ? $bill['description'] : $bill['billing_type']); ?></strong>
                </td>
                <td class="amount-cell">₱<?php echo number_format($bill['amount'], 2); ?></td>
                <td class="due-date <?php echo $is_overdue ? 'overdue' : ''; ?>">
                  <?php echo date('M d, Y', strtotime($bill['due_date'])); ?>
                  <?php if ($is_overdue): ?>
                    <br><small style="font-weight: 600; color: #ef4444;">(Overdue)</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge status-<?php echo $status_class; ?>">
                    <?php if ($bill['status'] === 'paid'): ?>
                      <i class="fas fa-check"></i>
                    <?php elseif ($is_overdue): ?>
                      <i class="fas fa-exclamation"></i>
                    <?php else: ?>
                      <i class="fas fa-clock"></i>
                    <?php endif; ?>
                    <?php echo $is_overdue ? 'Overdue' : ucfirst($bill['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i>
        Print Billing History
      </button>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h5>No Billing Records Yet</h5>
        <p>Your billing history will appear here</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>