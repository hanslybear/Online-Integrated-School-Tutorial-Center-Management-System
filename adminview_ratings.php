<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

$feedback_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($feedback_id === 0) {
    header("Location: adminfeedback.php");
    exit();
}

// Fetch feedback details
$sql = "SELECT 
          f.*,
          u.student_name,
          u.email,
          u.contact,
          u.subject_tutorials,
          u.computer_tutorials
        FROM studentfeedback f
        LEFT JOIN users u ON f.student_id = u.id
        WHERE f.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $feedback_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: adminfeedback.php?error=Feedback not found");
    exit();
}

$feedback = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Feedback | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
.feedback-detail-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px;
  border-radius: 20px;
  margin-bottom: 32px;
  color: white;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
}

.feedback-detail-header h2 {
  color: white;
  font-weight: 700;
  margin-bottom: 12px;
}

.detail-card {
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  margin-bottom: 24px;
}

.section-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-title i {
  color: #667eea;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.info-item label {
  font-size: 0.8rem;
  color: #6B7280;
  font-weight: 700;
  text-transform: uppercase;
  display: block;
  margin-bottom: 6px;
}

.info-item value {
  font-size: 1.1rem;
  color: #1a202c;
  font-weight: 600;
  display: block;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
  border-radius: 12px;
  margin-bottom: 24px;
}

.rating-stars-large {
  display: flex;
  gap: 6px;
}

.rating-stars-large i {
  font-size: 2rem;
  color: #F59E0B;
}

.rating-value-large {
  font-size: 2.5rem;
  font-weight: 700;
  color: #F59E0B;
}

.message-box {
  background: #F9FAFB;
  border-left: 4px solid #667eea;
  padding: 24px;
  border-radius: 12px;
  line-height: 1.8;
  font-size: 1.05rem;
  color: #1a202c;
}

.status-badge-large {
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  display: inline-block;
}

.status-badge-large.pending {
  background: rgba(245, 158, 11, 0.15);
  color: #D97706;
  border: 2px solid #F59E0B;
}

.status-badge-large.reviewed {
  background: rgba(16, 185, 129, 0.15);
  color: #059669;
  border: 2px solid #10b981;
}

.action-buttons-large {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.btn-back {
  background: #F3F4F6;
  color: #1a202c;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
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

.btn-action-large {
  padding: 14px 28px;
  border-radius: 12px;
  border: none;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.btn-mark-reviewed {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-mark-reviewed:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
}

.btn-delete-large {
  background: linear-gradient(135deg, #EF4444, #DC2626);
  color: white;
}

.btn-delete-large:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
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
    <a href="pending_students.php">
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
    <a href="adminfeedback.php" class="active">
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
  <a href="adminfeedback.php" class="btn-back">
    <i class="fas fa-arrow-left"></i>
    Back to Feedback
  </a>

  <div class="feedback-detail-header">
    <h2><i class="fas fa-comment-dots"></i> Feedback Details</h2>
    <p>Submitted on <?php echo date('F d, Y \a\t g:i A', strtotime($feedback['created_at'])); ?></p>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <!-- Student Information -->
      <div class="detail-card">
        <h3 class="section-title">
          <i class="fas fa-user"></i>
          Student Information
        </h3>
        <div class="info-grid">
          <div class="info-item">
            <label>Student Name</label>
            <value><?php echo htmlspecialchars($feedback['student_name']); ?></value>
          </div>
          <div class="info-item">
            <label>Email</label>
            <value><?php echo htmlspecialchars($feedback['email']); ?></value>
          </div>
          <div class="info-item">
            <label>Contact</label>
            <value><?php echo htmlspecialchars(isset($feedback['contact']) ? $feedback['contact'] : 'N/A'); ?></value>
          </div>
          <div class="info-item">
            <label>Subject Tutorials</label>
            <value><?php echo htmlspecialchars(isset($feedback['subject_tutorials']) ? $feedback['subject_tutorials'] : 'None'); ?></value>
          </div>
          <div class="info-item">
            <label>Computer Tutorials</label>
            <value><?php echo htmlspecialchars(isset($feedback['computer_tutorials']) ? $feedback['computer_tutorials'] : 'None'); ?></value>
          </div>
        </div>
      </div>

      <!-- Feedback Content -->
      <div class="detail-card">
        <h3 class="section-title">
          <i class="fas fa-message"></i>
          Feedback Content
        </h3>

        <div class="info-item" style="margin-bottom: 20px;">
          <label>Subject</label>
          <value style="font-size: 1.3rem;"><?php echo htmlspecialchars($feedback['subject']); ?></value>
        </div>

        <div class="info-item" style="margin-bottom: 20px;">
          <label>Category</label>
          <value>
            <span class="category-badge" style="padding: 8px 16px; font-size: 0.9rem;">
              <i class="fas fa-tag"></i> <?php echo ucfirst($feedback['category']); ?>
            </span>
          </value>
        </div>

        <?php if ($feedback['rating'] > 0): ?>
          <div class="rating-display">
            <div class="rating-stars-large">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : ' far'; ?>"></i>
              <?php endfor; ?>
            </div>
            <div class="rating-value-large"><?php echo $feedback['rating']; ?>/5</div>
          </div>
        <?php endif; ?>

        <div class="info-item">
          <label>Message</label>
          <div class="message-box">
            <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <!-- Status & Actions -->
      <div class="detail-card">
        <h3 class="section-title">
          <i class="fas fa-tasks"></i>
          Status & Actions
        </h3>

        <div style="margin-bottom: 24px;">
          <label style="font-size: 0.8rem; color: #6B7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 12px;">
            Current Status
          </label>
          <span class="status-badge-large <?php echo $feedback['status']; ?>">
            <i class="fas fa-<?php echo $feedback['status'] === 'pending' ? 'clock' : 'check-circle'; ?>"></i>
            <?php echo ucfirst($feedback['status']); ?>
          </span>
        </div>

        <div class="action-buttons-large">
          <?php if ($feedback['status'] === 'pending'): ?>
            <form method="POST" action="update_feedback_status.php" style="flex: 1;">
              <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
              <input type="hidden" name="status" value="reviewed">
              <button type="submit" class="btn-action-large btn-mark-reviewed" style="width: 100%;">
                <i class="fas fa-check"></i>
                Mark as Reviewed
              </button>
            </form>
          <?php else: ?>
            <form method="POST" action="update_feedback_status.php" style="flex: 1;">
              <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
              <input type="hidden" name="status" value="pending">
              <button type="submit" class="btn-action-large" style="background: linear-gradient(135deg, #F59E0B, #D97706); color: white; width: 100%;">
                <i class="fas fa-undo"></i>
                Mark as Pending
              </button>
            </form>
          <?php endif; ?>
        </div>

        <form method="POST" action="delete_feedback.php" onsubmit="return confirm('Are you sure you want to delete this feedback? This action cannot be undone.');" style="margin-top: 12px;">
          <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
          <button type="submit" class="btn-action-large btn-delete-large" style="width: 100%;">
            <i class="fas fa-trash"></i>
            Delete Feedback
          </button>
        </form>
      </div>

      <!-- Timeline -->
      <div class="detail-card">
        <h3 class="section-title">
          <i class="fas fa-history"></i>
          Timeline
        </h3>

        <div style="position: relative; padding-left: 28px;">
          <div style="position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: linear-gradient(180deg, #667eea, #764ba2);"></div>
          
          <div style="position: relative; margin-bottom: 20px;">
            <div style="position: absolute; left: -26px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #667eea; border: 3px solid white; box-shadow: 0 0 0 2px #667eea;"></div>
            <div style="font-size: 0.8rem; color: #6B7280; margin-bottom: 4px;">
              <?php echo date('M d, Y - g:i A', strtotime($feedback['created_at'])); ?>
            </div>
            <div style="background: #F9FAFB; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem;">
              Feedback submitted
            </div>
          </div>

          <?php if ($feedback['status'] === 'reviewed'): ?>
          <div style="position: relative;">
            <div style="position: absolute; left: -26px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #10b981; border: 3px solid white; box-shadow: 0 0 0 2px #10b981;"></div>
            <div style="font-size: 0.8rem; color: #6B7280; margin-bottom: 4px;">
              <?php echo date('M d, Y - g:i A', strtotime(isset($feedback['updated_at']) ? $feedback['updated_at'] : $feedback['created_at'])); ?>
            </div>
            <div style="background: #F9FAFB; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem;">
              Marked as reviewed
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>