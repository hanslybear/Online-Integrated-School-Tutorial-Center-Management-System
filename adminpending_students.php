<!--admin approve pending_students.php-->
<?php
  session_start();
  include "db_connect.php";
  
  if (!isset($_SESSION['admin_id'])) {
      header("Location: homepage.php");
      exit();
  }
  
  $sql = "SELECT 
            id,
            student_name,
            email,
            gender,
            contact,
            parents_contact,
            created_at,
            status
          FROM users
          WHERE status = 'pending'
          ORDER BY created_at DESC";

  $result = $conn->query($sql);

  $pending_students = [];

  while ($row = $result->fetch_assoc()) {
      $pending_students[] = [
          'id' => $row['id'],
          'name' => $row['student_name'],
          'email' => $row['email'],
          'gender' => $row['gender'],
          'phone' => $row['contact'],
          'parents_contact' => $row['parents_contact'],
          'program' => 'N/A', // optional for now
          'date_applied' => $row['created_at'],
          'status' => 'new'
      ];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pending Students | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
* {
    font-family: 'DM Sans', sans-serif;
}

/* Page Header - Matching Dashboard Style */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}

.page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 800;
    color: #1a202c;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h2 i {
    color: #ec4899;
    font-size: 1.5rem;
}

.page-header .badge {
    font-family: 'DM Sans', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
    color: #f59e0b;
    border: 2px solid rgba(245, 158, 11, 0.3);
}

/* Alert Messages - Matching Dashboard Style */
.alert {
    margin-bottom: 24px;
    animation: slideDown 0.4s ease;
    border-radius: 12px;
    padding: 16px 20px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.alert-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border-left: 4px solid #10b981;
    color: #065f46;
}

.alert-danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.alert i {
    margin-right: 8px;
    font-size: 1.1rem;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Student Cards - Enhanced Design */
.students-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
}

.student-card {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.student-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
}

/* Student Header */
.student-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
}

.student-avatar {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.student-info {
    flex: 1;
    min-width: 0;
}

.student-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.student-program {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.student-program i {
    color: #ec4899;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.status-badge.new {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
    color: #3b82f6;
    border: 2px solid rgba(59, 130, 246, 0.3);
}

/* Student Details Grid */
.student-details {
    display: grid;
    gap: 12px;
    margin-bottom: 24px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.detail-item:hover {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    transform: translateX(4px);
}

.detail-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    font-size: 1rem;
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
    min-width: 0;
}

.detail-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.detail-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1a202c;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Action Buttons */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.btn-action {
    padding: 10px 12px;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-view {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
    color: #3b82f6;
    border: 2px solid rgba(59, 130, 246, 0.2);
}

.btn-view:hover {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-approve {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.2);
}

.btn-approve:hover {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-reject {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
    color: #ef4444;
    border: 2px solid rgba(239, 68, 68, 0.2);
}

.btn-reject:hover {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Empty State - Matching Dashboard Style */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.empty-state-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.empty-state-icon i {
    font-size: 3rem;
    color: #667eea;
}

.empty-state h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 1rem;
    color: #64748b;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .students-list {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .action-buttons {
        grid-template-columns: 1fr;
    }

    .student-name {
        font-size: 1.1rem;
    }

    .btn-action {
        padding: 12px;
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
    <a href="adminpending_students.php" class="active">
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
      <i class="fas fa-user-clock"></i>
      Pending Student Applications
    </h2>
    <span class="badge">
      <?php echo count($pending_students); ?> Pending
    </span>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle"></i>
      <strong>Success!</strong> <?php echo $_SESSION['success_message']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle"></i>
      <strong>Error!</strong> <?php echo $_SESSION['error_message']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>


  <div class="students-list">
    <?php if (count($pending_students) > 0): ?>
      <?php foreach ($pending_students as $student): ?>
        <div class="student-card">
          <div class="student-header">
            <div class="student-avatar">
              <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
            </div>
            <div class="student-info">
              <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
              <div class="student-program">
                <i class="fas fa-book"></i>
                <?php echo htmlspecialchars($student['program']); ?>
              </div>
            </div>
            <span class="status-badge <?php echo $student['status']; ?>">
              <?php echo ucfirst($student['status']); ?>
            </span>
          </div>

          <div class="student-details">
            <div class="detail-item">
              <div class="detail-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="detail-content">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($student['email']); ?></div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-icon">
                <i class="fas fa-phone"></i>
              </div>
              <div class="detail-content">
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo htmlspecialchars($student['phone']); ?></div>
              </div>
            </div>

            <div class="detail-item">
              <div class="detail-icon">
                <i class="fas fa-calendar"></i>
              </div>
              <div class="detail-content">
                <div class="detail-label">Applied On</div>
                <div class="detail-value"><?php echo date('M d, Y', strtotime($student['date_applied'])); ?></div>
              </div>
            </div>
          </div>

          <div class="action-buttons">
            <button class="btn btn-action btn-view" onclick="viewStudent(<?php echo $student['id']; ?>)">
              <i class="fas fa-eye"></i>
              View Details
            </button>
            <button class="btn btn-action btn-approve" onclick="approveStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name'], ENT_QUOTES); ?>')">
              <i class="fas fa-check"></i>
              Approve
            </button>
            <button class="btn btn-action btn-reject" onclick="rejectStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name'], ENT_QUOTES); ?>')">
              <i class="fas fa-times"></i>
              Reject
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h3>No Pending Applications</h3>
        <p>All student applications have been processed. Great job!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function viewStudent(id) {
  window.location.href = 'adminview_student.php?id=' + id;
}

function approveStudent(id, name) {
  if (confirm('Are you sure you want to approve ' + name + '?\n\nAn email notification will be sent to the student informing them that their account has been approved and they can now access the student dashboard.')) {
    window.location.href = 'approve_student.php?id=' + id;
  }
}

function rejectStudent(id, name) {
  if (confirm('Are you sure you want to reject the application of ' + name + '?\n\nAn email notification will be sent to the student.')) {
    window.location.href = 'reject_student.php?id=' + id;
  }
}

// Auto-hide alerts after 10 seconds
setTimeout(function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 10000);
</script>

</body>
</html>