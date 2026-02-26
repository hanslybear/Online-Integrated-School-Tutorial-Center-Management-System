<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Fetch all feedback with student information
$sql = "SELECT 
          f.id,
          f.student_id,
          f.rating,
          f.category,
          f.subject,
          f.message,
          f.status,
          f.created_at,
          u.student_name,
          u.email,
          u.subject_tutorials,
          u.computer_tutorials
        FROM studentfeedback f
        LEFT JOIN users u ON f.student_id = u.id
        ORDER BY f.created_at DESC";

$result = $conn->query($sql);

$feedback_records = [];
while ($row = $result->fetch_assoc()) {
    $feedback_records[] = $row;
}

// Calculate statistics
$total_feedback = count($feedback_records);
$reviewed_count = count($feedback_records); // All are reviewed now
$total_rating = 0;
$rating_count = 0;

foreach ($feedback_records as $feedback) {
    if ($feedback['rating'] > 0) {
        $total_rating += $feedback['rating'];
        $rating_count++;
    }
}

$avg_rating = $rating_count > 0 ? round($total_rating / $rating_count, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback & Reports | ACTS Admin</title>
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

.header-actions {
  display: flex;
  gap: 12px;
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

.feedback-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.feedback-stat-card {
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

.feedback-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.feedback-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.feedback-stat-card.total::before {
  background: linear-gradient(90deg, var(--primary), var(--secondary));
}

.feedback-stat-card.resolved::before {
  background: linear-gradient(90deg, var(--success), #059669);
}

.feedback-stat-card.rating::before {
  background: linear-gradient(90deg, #F59E0B, #D97706);
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

.feedback-stat-card.total .stat-icon-lg {
  background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(6, 182, 212, 0.15));
  color: var(--primary);
}

.feedback-stat-card.resolved .stat-icon-lg {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: var(--success);
}

.feedback-stat-card.rating .stat-icon-lg {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
  color: #F59E0B;
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

.rating-stars {
  display: flex;
  gap: 4px;
  margin-top: 8px;
}

.rating-stars i {
  color: #F59E0B;
  font-size: 1.2rem;
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

.view-toggle {
  display: flex;
  gap: 8px;
  background: #F3F4F6;
  padding: 4px;
  border-radius: 12px;
  margin-bottom: 24px;
  width: fit-content;
}

.view-toggle button {
  padding: 10px 20px;
  border: none;
  background: transparent;
  border-radius: 10px;
  font-weight: 600;
  color: #6B7280;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.view-toggle button.active {
  background: white;
  color: var(--primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.feedback-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

.feedback-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.feedback-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 5px;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.feedback-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.feedback-card:hover::before {
  transform: scaleX(1);
}

.feedback-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 16px;
}

.student-info-fb {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar-fb {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
}

.student-details-fb h6 {
  margin: 0;
  font-weight: 600;
  color: var(--dark);
  font-size: 0.95rem;
}

.student-details-fb p {
  margin: 0;
  font-size: 0.8rem;
  color: #6B7280;
}

.status-badge-fb {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-badge-fb.reviewed {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
  color: var(--success);
  border: 2px solid var(--success);
}

.category-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  background: #F3F4F6;
  color: #4B5563;
  margin-bottom: 12px;
}

.feedback-subject {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 12px;
}

.feedback-message {
  color: #4B5563;
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.feedback-rating {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.feedback-rating i {
  color: #F59E0B;
  font-size: 1rem;
}

.feedback-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 2px solid #f1f5f9;
}

.feedback-date {
  font-size: 0.8rem;
  color: #9CA3AF;
  display: flex;
  align-items: center;
  gap: 6px;
}

.feedback-actions {
  display: flex;
  gap: 8px;
}

.btn-fb-action {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  color: white;
  cursor: pointer;
}

.btn-fb-action.view {
  background: linear-gradient(135deg, var(--info), #2563EB);
}

.btn-fb-action.delete {
  background: linear-gradient(135deg, var(--danger), #DC2626);
}

.btn-fb-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.feedback-table-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.3);
  overflow-x: auto;
  display: none;
}

.feedback-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
}

.feedback-table thead th {
  background: transparent;
  color: #6B7280;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  padding: 12px 16px;
  border: none;
}

.feedback-table tbody tr {
  background: #F9FAFB;
  transition: all 0.3s ease;
}

.feedback-table tbody tr:hover {
  background: #F3F4F6;
  transform: scale(1.01);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.feedback-table tbody td {
  padding: 18px 16px;
  border: none;
  vertical-align: middle;
}

.feedback-table tbody tr td:first-child {
  border-radius: 10px 0 0 10px;
}

.feedback-table tbody tr td:last-child {
  border-radius: 0 10px 10px 0;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  grid-column: 1 / -1;
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

.feedback-card {
  animation: fadeInUp 0.4s ease forwards;
}

.feedback-card:nth-child(1) { animation-delay: 0.05s; }
.feedback-card:nth-child(2) { animation-delay: 0.1s; }
.feedback-card:nth-child(3) { animation-delay: 0.15s; }
.feedback-card:nth-child(4) { animation-delay: 0.2s; }
.feedback-card:nth-child(5) { animation-delay: 0.25s; }
.feedback-card:nth-child(6) { animation-delay: 0.3s; }

@media (max-width: 768px) {
  .feedback-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    padding: 28px 24px;
  }
  
  .page-header h2 {
    font-size: 2rem;
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
  <div class="page-header">
    <div class="page-header-top">
      <h2>
        <i class="fas fa-comments"></i>
        Feedback & Reports
      </h2>
      <div class="header-actions">
        <button class="btn btn-export">
          <i class="fas fa-file-pdf"></i>
          Export Report
        </button>
      </div>
    </div>
  </div>

  <div class="feedback-stats">
    <div class="feedback-stat-card total">
      <div class="stat-icon-lg">
        <i class="fas fa-comments"></i>
      </div>
      <div class="stat-label-lg">Total Feedback</div>
      <div class="stat-value-lg"><?php echo $total_feedback; ?></div>
    </div>

    <div class="feedback-stat-card resolved">
      <div class="stat-icon-lg">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-label-lg">Reviewed</div>
      <div class="stat-value-lg"><?php echo $reviewed_count; ?></div>
    </div>

    <div class="feedback-stat-card rating">
      <div class="stat-icon-lg">
        <i class="fas fa-star"></i>
      </div>
      <div class="stat-label-lg">Average Rating</div>
      <div class="stat-value-lg"><?php echo $avg_rating; ?>/5</div>
      <div class="rating-stars">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fas fa-star<?php echo $i <= floor($avg_rating) ? '' : ($i - 0.5 <= $avg_rating ? '-half-alt' : ' far'); ?>"></i>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <div class="filter-section">
    <form method="GET" action="">
      <div class="row g-3">
        <div class="col-md-4">
          <label>Search</label>
          <input type="text" class="form-control" name="search" placeholder="Search by subject or student name...">
        </div>
        <div class="col-md-3">
          <label>Category</label>
          <select class="form-control" name="category">
            <option value="">All Categories</option>
            <option value="tutor">Tutor Quality</option>
            <option value="content">Content</option>
            <option value="facility">Facility</option>
            <option value="general">General</option>
          </select>
        </div>
        <div class="col-md-3">
          <label>Rating</label>
          <select class="form-control" name="rating">
            <option value="">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
          </select>
        </div>
        <div class="col-md-2">
          <label>&nbsp;</label>
          <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600;">
            <i class="fas fa-filter"></i> Apply Filters
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="view-toggle">
    <button class="active" data-view="cards" onclick="toggleView('cards')">
      <i class="fas fa-th-large"></i>
      Card View
    </button>
    <button data-view="list" onclick="toggleView('list')">
      <i class="fas fa-list"></i>
      List View
    </button>
  </div>

  <div class="feedback-grid" id="feedbackGrid">
    <?php if (count($feedback_records) > 0): ?>
      <?php foreach ($feedback_records as $feedback): 
        $tutorials = [];
        if (!empty($feedback['subject_tutorials'])) {
          $tutorials[] = $feedback['subject_tutorials'];
        }
        if (!empty($feedback['computer_tutorials'])) {
          $tutorials[] = $feedback['computer_tutorials'];
        }
        $tutorial_display = !empty($tutorials) ? implode(', ', $tutorials) : 'N/A';
      ?>
        <div class="feedback-card">
          <div class="feedback-header">
            <div class="student-info-fb">
              <div class="student-avatar-fb">
                <?php echo strtoupper(substr($feedback['student_name'], 0, 1)); ?>
              </div>
              <div class="student-details-fb">
                <h6><?php echo htmlspecialchars($feedback['student_name']); ?></h6>
                <p><?php echo htmlspecialchars($tutorial_display); ?></p>
              </div>
            </div>
            <span class="status-badge-fb reviewed">
              Reviewed
            </span>
          </div>

          <span class="category-badge">
            <i class="fas fa-tag"></i> <?php echo ucfirst($feedback['category']); ?>
          </span>

          <div class="feedback-subject"><?php echo htmlspecialchars($feedback['subject']); ?></div>
          
          <div class="feedback-message"><?php echo htmlspecialchars($feedback['message']); ?></div>

          <?php if ($feedback['rating'] > 0): ?>
            <div class="feedback-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : ' far'; ?>"></i>
              <?php endfor; ?>
              <span style="color: #64748b; font-weight: 600; margin-left: 8px;">
                <?php echo $feedback['rating']; ?>/5
              </span>
            </div>
          <?php endif; ?>

          <div class="feedback-footer">
            <div class="feedback-date">
              <i class="fas fa-calendar"></i>
              <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
            </div>
            <div class="feedback-actions">
              <button class="btn-fb-action view" onclick="viewFeedback(<?php echo $feedback['id']; ?>)" title="View Details">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn-fb-action delete" onclick="deleteFeedback(<?php echo $feedback['id']; ?>)" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-comment-slash"></i>
        </div>
        <h4>No Feedback Yet</h4>
        <p>No student feedback has been submitted yet</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="feedback-table-container" id="feedbackTable">
    <table class="feedback-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Subject</th>
          <th>Category</th>
          <th>Rating</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($feedback_records as $feedback): 
          $tutorials = [];
          if (!empty($feedback['subject_tutorials'])) {
            $tutorials[] = $feedback['subject_tutorials'];
          }
          if (!empty($feedback['computer_tutorials'])) {
            $tutorials[] = $feedback['computer_tutorials'];
          }
          $tutorial_display = !empty($tutorials) ? implode(', ', $tutorials) : 'N/A';
        ?>
          <tr>
            <td>
              <div class="student-info-fb">
                <div class="student-avatar-fb">
                  <?php echo strtoupper(substr($feedback['student_name'], 0, 1)); ?>
                </div>
                <div class="student-details-fb">
                  <h6><?php echo htmlspecialchars($feedback['student_name']); ?></h6>
                  <p><?php echo htmlspecialchars($feedback['email']); ?></p>
                </div>
              </div>
            </td>
            <td><strong><?php echo htmlspecialchars($feedback['subject']); ?></strong></td>
            <td><span class="category-badge"><?php echo ucfirst($feedback['category']); ?></span></td>
            <td>
              <?php if ($feedback['rating'] > 0): ?>
                <div class="feedback-rating">
                  <?php for ($i = 1; $i <= $feedback['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                  <?php endfor; ?>
                  <span style="margin-left: 6px; font-weight: 600;">
                    <?php echo $feedback['rating']; ?>/5
                  </span>
                </div>
              <?php else: ?>
                <span style="color: #9CA3AF;">No rating</span>
              <?php endif; ?>
            </td>
            <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
            <td>
              <div class="feedback-actions">
                <button class="btn-fb-action view" onclick="viewFeedback(<?php echo $feedback['id']; ?>)">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn-fb-action delete" onclick="deleteFeedback(<?php echo $feedback['id']; ?>)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function toggleView(view) {
  const gridView = document.getElementById('feedbackGrid');
  const tableView = document.getElementById('feedbackTable');
  const buttons = document.querySelectorAll('.view-toggle button');
  
  buttons.forEach(btn => btn.classList.remove('active'));
  document.querySelector(`button[data-view="${view}"]`).classList.add('active');
  
  if (view === 'cards') {
    gridView.style.display = 'grid';
    tableView.style.display = 'none';
  } else {
    gridView.style.display = 'none';
    tableView.style.display = 'block';
  }
}

function viewFeedback(id) {
  window.location.href = 'adminview_feedback.php?id=' + id;
}

function deleteFeedback(id) {
  if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'admindelete_feedback.php';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'feedback_id';
    idInput.value = id;
    
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
  }
}
</script>

</body>
</html>