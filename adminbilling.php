<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Handle Delete Billing
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM billing WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        header("Location: adminbilling.php?success=Billing record deleted successfully");
        exit();
    }
}

// Fetch billing records with student information
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT 
          b.id,
          b.student_id,
          u.student_name,
          u.email,
          b.amount,
          b.due_date,
          b.status,
          b.payment_method,
          b.billing_type,
          b.description,
          b.created_at
        FROM billing b
        LEFT JOIN users u ON b.student_id = u.id
        WHERE 1=1";

if (!empty($filter_status)) {
    $sql .= " AND b.status = '$filter_status'";
}

if (!empty($filter_type)) {
    $sql .= " AND b.billing_type = '$filter_type'";
}

if (!empty($search)) {
    $sql .= " AND (u.student_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$sql .= " ORDER BY b.created_at DESC";

$result = $conn->query($sql);

$billing_records = [];
while ($row = $result->fetch_assoc()) {
    // Check if overdue
    if ($row['status'] !== 'paid' && strtotime($row['due_date']) < time()) {
        $row['display_status'] = 'overdue';
    } else {
        $row['display_status'] = $row['status'];
    }
    $billing_records[] = $row;
}

// Calculate statistics
$total_amount = 0;
$paid_amount = 0;
$pending_amount = 0;
$overdue_count = 0;

foreach ($billing_records as $record) {
    $total_amount += $record['amount'];
    if ($record['status'] === 'paid') {
        $paid_amount += $record['amount'];
    } else {
        $pending_amount += $record['amount'];
        if (strtotime($record['due_date']) < time()) {
            $overdue_count++;
        }
    }
}

// Fetch all enrolled students for the dropdown
$students_sql = "SELECT id, student_name, email FROM users WHERE status = 'approved' ORDER BY student_name ASC";
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
<title>Billing Management | ACTS Admin</title>
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

.main {
    padding: 32px;
}

/* Stats Cards - Matching Dashboard */
.row.g-4 {
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
    width: 4px;
    height: 100%;
    transition: all 0.3s ease;
}

.stat-card.total::before {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-card.paid::before {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-card.pending::before {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-card.overdue::before {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 12px;
}

.stat-card.total .stat-icon {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
}

.stat-card.paid .stat-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    color: #10b981;
}

.stat-card.pending .stat-icon {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
    color: #f59e0b;
}

.stat-card.overdue .stat-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
    color: #ef4444;
}

.stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: #1a202c;
}

.stat-trend {
    font-size: 0.85rem;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
}

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
}

.filter-section label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.filter-section input,
.filter-section select {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.filter-section input:focus,
.filter-section select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
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
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-reset {
    background: #e2e8f0;
    color: #64748b;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-left: 8px;
}

.btn-reset:hover {
    background: #cbd5e1;
}

/* Table Card - Matching Dashboard Chart Card */
.chart-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.chart-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
}

.chart-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chart-title i {
    color: #ec4899;
    font-size: 1.3rem;
}

.btn-add {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    color: white;
}

/* Table */
.billing-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.billing-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 14px 16px;
    border: none;
}

.billing-table tbody tr {
    background: white;
    transition: all 0.3s ease;
}

.billing-table tbody tr:hover {
    background: #f8fafc;
}

.billing-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #4a5568;
    font-size: 0.95rem;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.student-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 800;
    color: white;
    font-family: 'Playfair Display', serif;
}

.student-details h6 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
    font-size: 0.95rem;
}

.student-details p {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

.amount-cell {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 800;
    color: #10b981;
}

.billing-type-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(102, 126, 234, 0.12);
    color: #667eea;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: capitalize;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-badge.paid {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.status-badge.overdue {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
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
    font-size: 0.85rem;
}

.btn-action.edit {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.btn-action.delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

/* Modal - Matching Dashboard Style */
.modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 24px 28px;
    border: none;
}

.modal-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-body {
    padding: 28px;
}

.modal-footer {
    border: none;
    padding: 20px 28px 28px;
    background: #f8fafc;
}

.form-label {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 12px 14px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    color: white;
}

.btn-cancel {
    background: #e2e8f0;
    color: #475569;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #cbd5e1;
}

/* Alerts */
.alert-custom {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 2px solid #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 2px solid #ef4444;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.empty-state h4 {
    font-size: 1.2rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 8px;
}

.empty-state p {
    color: #94a3b8;
    font-size: 0.95rem;
}

@media (max-width: 768px) {
    .main {
        padding: 20px;
    }

    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
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
    <a href="adminbilling.php" class="active">
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
  <?php if (isset($_GET['success'])): ?>
    <div class="alert-custom alert-success">
      <i class="fas fa-check-circle"></i>
      <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert-custom alert-error">
      <i class="fas fa-exclamation-circle"></i>
      <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Stats Cards -->
  <div class="row g-4">
    <div class="col-md-6 col-lg-3">
      <div class="stat-card total">
        <div class="stat-icon">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">₱<?php echo number_format($total_amount, 2); ?></div>
        <div class="stat-trend">
          <i class="fas fa-chart-line"></i>
          <span>All time</span>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="stat-card paid">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-label">Paid Amount</div>
        <div class="stat-value">₱<?php echo number_format($paid_amount, 2); ?></div>
        <div class="stat-trend">
          <i class="fas fa-check"></i>
          <span>Collected</span>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="stat-card pending">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-label">Pending Amount</div>
        <div class="stat-value">₱<?php echo number_format($pending_amount, 2); ?></div>
        <div class="stat-trend">
          <i class="fas fa-hourglass-half"></i>
          <span>Awaiting payment</span>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="stat-card overdue">
        <div class="stat-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-label">Overdue Bills</div>
        <div class="stat-value"><?php echo $overdue_count; ?></div>
        <div class="stat-trend">
          <i class="fas fa-exclamation-circle"></i>
          <span>Past due date</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="filter-section">
    <form method="GET" action="">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label>Search Student</label>
          <input type="text" class="form-control" name="search" placeholder="Name or email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
          <label>Status</label>
          <select class="form-control" name="status">
            <option value="">All Status</option>
            <option value="paid" <?php echo $filter_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
          </select>
        </div>
        <div class="col-md-3">
          <label>Billing Type</label>
          <select class="form-control" name="type">
            <option value="">All Types</option>
            <option value="tuition" <?php echo $filter_type === 'tuition' ? 'selected' : ''; ?>>Tuition Fee</option>
            <option value="miscellaneous" <?php echo $filter_type === 'miscellaneous' ? 'selected' : ''; ?>>Miscellaneous</option>
            <option value="materials" <?php echo $filter_type === 'materials' ? 'selected' : ''; ?>>Materials</option>
            <option value="enrollment" <?php echo $filter_type === 'enrollment' ? 'selected' : ''; ?>>Enrollment</option>
          </select>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-filter">
            <i class="fas fa-search"></i> Filter
          </button>
          <a href="adminbilling.php" class="btn btn-reset">
            <i class="fas fa-redo"></i> Reset
          </a>
        </div>
      </div>
    </form>
  </div>

  <!-- Billing Table -->
  <div class="chart-card">
    <div class="chart-header">
      <h4 class="chart-title">
        <i class="fas fa-file-invoice-dollar"></i>
        Billing Records
      </h4>
      <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addBillingModal">
        <i class="fas fa-plus-circle"></i> Add Billing
      </button>
    </div>

    <?php if (count($billing_records) > 0): ?>
      <div class="table-responsive">
        <table class="billing-table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Type</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($billing_records as $record): ?>
              <tr>
                <td>
                  <div class="student-info">
                    <div class="student-avatar">
                      <?php echo strtoupper(substr($record['student_name'], 0, 1)); ?>
                    </div>
                    <div class="student-details">
                      <h6><?php echo htmlspecialchars($record['student_name']); ?></h6>
                      <p><?php echo htmlspecialchars($record['email']); ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="billing-type-badge">
                    <?php echo ucfirst(htmlspecialchars($record['billing_type'])); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars(isset($record['description']) ? $record['description'] : 'N/A'); ?></td>
                <td class="amount-cell">₱<?php echo number_format($record['amount'], 2); ?></td>
                <td><?php echo date('M d, Y', strtotime($record['due_date'])); ?></td>
                <td>
                  <span class="status-badge <?php echo $record['display_status']; ?>">
                    <?php if ($record['display_status'] === 'paid'): ?>
                      <i class="fas fa-check"></i>
                    <?php elseif ($record['display_status'] === 'overdue'): ?>
                      <i class="fas fa-exclamation"></i>
                    <?php else: ?>
                      <i class="fas fa-clock"></i>
                    <?php endif; ?>
                    <?php echo ucfirst($record['display_status']); ?>
                  </span>
                </td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action edit" data-bs-toggle="modal" data-bs-target="#editBillingModal<?php echo $record['id']; ?>" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-action delete" onclick="confirmDelete(<?php echo $record['id']; ?>)" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-file-invoice"></i>
        <h4>No Billing Records Found</h4>
        <p>Start by adding a new billing record for your students</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Billing Modal -->
<div class="modal fade" id="addBillingModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-plus-circle"></i>
          Add New Billing Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="add_billing.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Student *</label>
            <select name="student_id" class="form-select" required>
              <option value="">Choose a student...</option>
              <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['id']; ?>">
                  <?php echo htmlspecialchars($student['student_name']) . ' (' . htmlspecialchars($student['email']) . ')'; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Billing Type *</label>
              <select name="billing_type" class="form-select" required>
                <option value="">Select type...</option>
                <option value="tuition">Tuition Fee</option>
                <option value="miscellaneous">Miscellaneous Fee</option>
                <option value="materials">Materials Fee</option>
                <option value="enrollment">Enrollment Fee</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Amount (₱) *</label>
              <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Add billing details or notes..."></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Due Date *</label>
              <input type="date" name="due_date" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Payment Status *</label>
              <select name="status" class="form-select" required>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-submit">Add Billing</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Billing Modals -->
<?php foreach ($billing_records as $record): ?>
<div class="modal fade" id="editBillingModal<?php echo $record['id']; ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-edit"></i> Edit Billing Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="update_billing.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="billing_id" value="<?php echo $record['id']; ?>">

          <div class="mb-3">
            <label class="form-label">Student</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($record['student_name']); ?>" disabled>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Billing Type</label>
              <select name="billing_type" class="form-select">
                <option value="tuition" <?= $record['billing_type']=='tuition'?'selected':'' ?>>Tuition</option>
                <option value="miscellaneous" <?= $record['billing_type']=='miscellaneous'?'selected':'' ?>>Miscellaneous</option>
                <option value="materials" <?= $record['billing_type']=='materials'?'selected':'' ?>>Materials</option>
                <option value="enrollment" <?= $record['billing_type']=='enrollment'?'selected':'' ?>>Enrollment</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Amount</label>
              <input type="number" name="amount" class="form-control" value="<?php echo $record['amount']; ?>" step="0.01" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars(isset($record['description']) ? $record['description'] : ''); ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" value="<?php echo $record['due_date']; ?>" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="pending" <?= $record['status']=='pending'?'selected':'' ?>>Pending</option>
                <option value="paid" <?= $record['status']=='paid'?'selected':'' ?>>Paid</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-submit">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(billingId) {
  if (confirm('Are you sure you want to delete this billing record? This action cannot be undone.')) {
    window.location.href = 'adminbilling.php?delete_id=' + billingId;
  }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert-custom');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.animation = 'slideUp 0.3s ease';
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });
});
</script>

</body>
</html>