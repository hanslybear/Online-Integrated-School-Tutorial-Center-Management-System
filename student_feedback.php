<!--Student Feedback Page-->
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
$student_name = $student['student_name'];
$student_email = $student['email'];

// Fetch student's feedback history
$feedback_sql = "SELECT * FROM studentfeedback WHERE student_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($feedback_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback & Ratings | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">
<style>
.page-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 32px;
  color: white;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  position: relative;
  overflow: hidden;
}

.page-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 300px;
  height: 300px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.banner-content {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.banner-text h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.banner-text p {
  font-size: 1.1rem;
  opacity: 0.95;
}

.btn-submit-feedback {
  background: white;
  color: #667eea;
  border: none;
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.btn-submit-feedback:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  background: #f8f9ff;
  color: #5a67d8;
}

.feedback-history {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
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
  font-size: 1.4rem;
  font-weight: 700;
  color: #1a202c;
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-title i {
  color: #667eea;
}

.feedback-item {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  border-left: 4px solid #667eea;
  transition: all 0.3s ease;
}

.feedback-item:hover {
  transform: translateX(8px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 12px;
}

.feedback-subject {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 4px;
}

.feedback-date {
  font-size: 0.85rem;
  color: #6B7280;
}

.feedback-rating {
  display: flex;
  gap: 4px;
  margin-bottom: 12px;
}

.feedback-rating i {
  color: #F59E0B;
  font-size: 1.2rem;
}

.feedback-message {
  color: #4B5563;
  line-height: 1.6;
  margin-bottom: 12px;
}

.feedback-status {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.feedback-status.reviewed {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: #10b981;
  border: 2px solid #10b981;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 4rem;
  color: #D1D5DB;
  margin-bottom: 16px;
}

.empty-state h4 {
  color: #4B5563;
  font-weight: 700;
  margin-bottom: 8px;
}

.empty-state p {
  color: #9CA3AF;
  font-size: 1.1rem;
}

.form-section {
  margin-bottom: 24px;
}

.form-label-custom {
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 12px;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-label-custom i {
  color: #667eea;
}

.star-rating {
  display: flex;
  gap: 8px;
  flex-direction: row-reverse;
  justify-content: flex-end;
  margin-bottom: 24px;
}

.star-rating input {
  display: none;
}

.star-rating label {
  cursor: pointer;
  font-size: 2.5rem;
  color: #D1D5DB;
  transition: all 0.3s ease;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
  color: #F59E0B;
  transform: scale(1.1);
}

.category-select {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.category-option {
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 10px;
  padding: 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.category-option:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.category-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
}

.category-option i {
  font-size: 1.5rem;
  color: #667eea;
  margin-bottom: 8px;
}

.category-option .category-name {
  font-weight: 600;
  color: #1a202c;
  font-size: 0.9rem;
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

.feedback-item {
  animation: fadeInUp 0.4s ease forwards;
}

.feedback-item:nth-child(1) { animation-delay: 0.1s; }
.feedback-item:nth-child(2) { animation-delay: 0.2s; }
.feedback-item:nth-child(3) { animation-delay: 0.3s; }
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
    <a href="student_feedback.php" class="active">
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
  <!-- Success/Error Messages -->
  <?php if (isset($_SESSION['feedback_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border-left: 4px solid #10b981; margin-bottom: 24px;">
      <i class="fas fa-check-circle"></i>
      <strong>Success!</strong> <?php echo $_SESSION['feedback_success']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['feedback_success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['feedback_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border-left: 4px solid #ef4444; margin-bottom: 24px;">
      <i class="fas fa-exclamation-circle"></i>
      <strong>Error!</strong> <?php echo $_SESSION['feedback_error']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['feedback_error']); ?>
  <?php endif; ?>

  <!-- Page Banner -->
  <div class="page-banner">
    <div class="banner-content">
      <div class="banner-text">
        <h2><i class="fas fa-star"></i> Feedback & Ratings</h2>
        <p>Share your experience and help us improve our services</p>
      </div>
      <button class="btn-submit-feedback" data-bs-toggle="modal" data-bs-target="#feedbackModal">
        <i class="fas fa-plus-circle"></i>
        Submit Feedback
      </button>
    </div>
  </div>

  <!-- Feedback History -->
  <div class="feedback-history">
    <div class="section-header">
      <h3 class="section-title">
        <i class="fas fa-history"></i>
        Your Feedback History
      </h3>
    </div>

    <?php if ($feedbacks->num_rows > 0): ?>
      <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
        <div class="feedback-item">
          <div class="feedback-header">
            <div>
              <div class="feedback-subject"><?php echo htmlspecialchars($feedback['subject']); ?></div>
              <div class="feedback-date">
                <i class="fas fa-calendar"></i>
                <?php echo date('F d, Y', strtotime($feedback['created_at'])); ?>
              </div>
            </div>
            <span class="feedback-status <?php echo $feedback['status']; ?>">
              <?php echo ucfirst($feedback['status']); ?>
            </span>
          </div>

          <?php if ($feedback['rating'] > 0): ?>
            <div class="feedback-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : ' far'; ?>"></i>
              <?php endfor; ?>
            </div>
          <?php endif; ?>

          <div class="feedback-message">
            <?php echo htmlspecialchars($feedback['message']); ?>
          </div>

          <div style="font-size: 0.85rem; color: #6B7280;">
            <i class="fas fa-tag"></i> <?php echo ucfirst($feedback['category']); ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-comment-slash"></i>
        </div>
        <h4>No Feedback Yet</h4>
        <p>You haven't submitted any feedback. Share your thoughts with us!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 20px; border: none;">
      <div class="modal-header" style="border: none; padding: 32px 32px 20px;">
        <h4 class="modal-title" id="feedbackModalLabel">
          <i class="fas fa-star" style="color: #667eea;"></i>
          Submit Feedback & Rating
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 20px 32px 32px;">
        <form id="feedbackForm" action="save_feedback.php" method="POST">
          
          <!-- Rating -->
          <div class="form-section">
            <label class="form-label-custom">
              <i class="fas fa-star"></i>
              Rate Your Experience
            </label>
            <div class="star-rating">
              <input type="radio" name="rating" id="star5" value="5">
              <label for="star5"><i class="fas fa-star"></i></label>
              
              <input type="radio" name="rating" id="star4" value="4">
              <label for="star4"><i class="fas fa-star"></i></label>
              
              <input type="radio" name="rating" id="star3" value="3">
              <label for="star3"><i class="fas fa-star"></i></label>
              
              <input type="radio" name="rating" id="star2" value="2">
              <label for="star2"><i class="fas fa-star"></i></label>
              
              <input type="radio" name="rating" id="star1" value="1">
              <label for="star1"><i class="fas fa-star"></i></label>
            </div>
          </div>

          <!-- Category -->
          <div class="form-section">
            <label class="form-label-custom">
              <i class="fas fa-tag"></i>
              Feedback Category
            </label>
            <div class="category-select">
              <div class="category-option" onclick="selectCategory(this, 'tutor')">
                <i class="fas fa-user-tie"></i>
                <div class="category-name">Tutor Quality</div>
              </div>
              <div class="category-option" onclick="selectCategory(this, 'content')">
                <i class="fas fa-book"></i>
                <div class="category-name">Content</div>
              </div>
              <div class="category-option" onclick="selectCategory(this, 'facility')">
                <i class="fas fa-building"></i>
                <div class="category-name">Facility</div>
              </div>
              <div class="category-option" onclick="selectCategory(this, 'general')">
                <i class="fas fa-comment-dots"></i>
                <div class="category-name">General</div>
              </div>
            </div>
            <input type="hidden" name="category" id="selectedCategory" required>
          </div>

          <!-- Subject -->
          <div class="form-section">
            <label class="form-label-custom">
              <i class="fas fa-heading"></i>
              Subject
            </label>
            <input type="text" name="subject" class="form-control" placeholder="Brief title for your feedback" required style="border-radius: 10px; border: 2px solid #E5E7EB; padding: 12px;">
          </div>

          <!-- Message -->
          <div class="form-section">
            <label class="form-label-custom">
              <i class="fas fa-message"></i>
              Your Feedback
            </label>
            <textarea name="message" class="form-control" rows="5" placeholder="Tell us about your experience..." required style="border-radius: 10px; border: 2px solid #E5E7EB; padding: 12px;"></textarea>
          </div>

          <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">

        </form>
      </div>
      <div class="modal-footer" style="border: none; padding: 20px 32px 32px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 12px 24px;">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="submit" form="feedbackForm" class="btn btn-primary" style="border-radius: 10px; padding: 12px 32px; background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
          <i class="fas fa-paper-plane"></i> Submit Feedback
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function selectCategory(element, category) {
  // Remove selected class from all categories
  document.querySelectorAll('.category-option').forEach(opt => opt.classList.remove('selected'));
  
  // Add selected class to clicked category
  element.classList.add('selected');
  
  // Set hidden input value
  document.getElementById('selectedCategory').value = category;
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

</body>
</html>