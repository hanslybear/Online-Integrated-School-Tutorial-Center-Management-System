<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php");
    exit();
}

// Count pending students
$pendingCount = 0;
$sql = "SELECT COUNT(*) AS total FROM users WHERE status = 'pending'";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $pendingCount = $row['total'];
}

// Count total students (approved)
$totalStudents = 0;
$sqlTotal = "SELECT COUNT(*) AS total FROM users WHERE status = 'approved'";
$resultTotal = mysqli_query($conn, $sqlTotal);
if ($rowTotal = mysqli_fetch_assoc($resultTotal)) {
    $totalStudents = $rowTotal['total'];
}



// Count total tutors
$totalTutors = 0;
$sqlTutors = "SELECT COUNT(*) AS total FROM tutors";
$resultTutors = mysqli_query($conn, $sqlTutors);

if ($resultTutors) {
    if ($rowTutors = mysqli_fetch_assoc($resultTutors)) {
        $totalTutors = $rowTutors['total'];
    }
} else {
    // Optional: show error during development
    // echo "Error counting tutors: " . mysqli_error($conn);
}

// Get billing revenue data - Monthly for current year
$monthlyRevenue = array_fill(0, 12, 0);
$currentYear = date('Y');
$revenueQuery = "SELECT 
                    MONTH(due_date) as month,
                    SUM(amount) as total
                FROM billing 
                WHERE YEAR(due_date) = '$currentYear' 
                AND status = 'paid'
                GROUP BY MONTH(due_date)";
$revenueResult = mysqli_query($conn, $revenueQuery);
if ($revenueResult) {
    while ($row = mysqli_fetch_assoc($revenueResult)) {
        $monthlyRevenue[$row['month'] - 1] = floatval($row['total']);
    }
}

// Get total revenue
$totalRevenue = 0;
$totalRevenueQuery = "SELECT SUM(amount) as total FROM billing WHERE status = 'paid'";
$totalRevenueResult = mysqli_query($conn, $totalRevenueQuery);
if ($row = mysqli_fetch_assoc($totalRevenueResult)) {
    $totalRevenue = isset($row['total']) ? $row['total'] : 0;
}

// Get pending payments
$pendingPayments = 0;
$pendingPaymentsQuery = "SELECT SUM(amount) as total FROM billing WHERE status = 'pending'";
$pendingPaymentsResult = mysqli_query($conn, $pendingPaymentsQuery);
if ($row = mysqli_fetch_assoc($pendingPaymentsResult)) {
    $pendingPayments = isset($row['total']) ? $row['total'] : 0;
}

// Get unpaid bills count
$unpaidBills = 0;
$unpaidQuery = "SELECT COUNT(*) as total FROM billing WHERE status = 'pending'";
$unpaidResult = mysqli_query($conn, $unpaidQuery);
if ($row = mysqli_fetch_assoc($unpaidResult)) {
    $unpaidBills = $row['total'];
}

// Get revenue by billing type
$revenueByType = [];
$typeQuery = "SELECT 
                billing_type,
                SUM(amount) as total
            FROM billing 
            WHERE status = 'paid'
            GROUP BY billing_type";
$typeResult = mysqli_query($conn, $typeQuery);
if ($typeResult) {
    while ($row = mysqli_fetch_assoc($typeResult)) {
        $revenueByType[$row['billing_type']] = floatval($row['total']);
    }
}

// Get recent payments
$recentPayments = [];
$recentQuery = "SELECT b.*, u.student_name 
                FROM billing b 
                JOIN users u ON b.student_id = u.id 
                WHERE b.status = 'paid'
                ORDER BY b.due_date DESC 
                LIMIT 5";
$recentResult = mysqli_query($conn, $recentQuery);
if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentPayments[] = $row;
    }
}

// Get enrollment data - Weekly (last 12 weeks)
$weeklyEnrollments = array_fill(0, 12, 0);
for ($i = 11; $i >= 0; $i--) {
    $weekStart = date('Y-m-d', strtotime("-$i weeks monday"));
    $weekEnd = date('Y-m-d', strtotime("-$i weeks sunday"));
    
    $weekQuery = "SELECT COUNT(*) as count 
                  FROM users 
                  WHERE status = 'approved' 
                  AND DATE(created_at) BETWEEN '$weekStart' AND '$weekEnd'";
    $weekResult = mysqli_query($conn, $weekQuery);
    if ($row = mysqli_fetch_assoc($weekResult)) {
        $weeklyEnrollments[11 - $i] = intval($row['count']);
    }
}

// Get enrollment data - Monthly (last 12 months)
$monthlyEnrollments = array_fill(0, 12, 0);
for ($i = 11; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    
    $monthQuery = "SELECT COUNT(*) as count 
                   FROM users 
                   WHERE status = 'approved' 
                   AND DATE(created_at) BETWEEN '$monthStart' AND '$monthEnd'";
    $monthResult = mysqli_query($conn, $monthQuery);
    if ($row = mysqli_fetch_assoc($monthResult)) {
        $monthlyEnrollments[11 - $i] = intval($row['count']);
    }
}

// Get enrollment data - Yearly (last 5 years)
$yearlyEnrollments = array_fill(0, 5, 0);
$yearLabels = [];
for ($i = 4; $i >= 0; $i--) {
    $year = date('Y') - $i;
    $yearLabels[] = $year;
    
    $yearQuery = "SELECT COUNT(*) as count 
                  FROM users 
                  WHERE status = 'approved' 
                  AND YEAR(created_at) = $year";
    $yearResult = mysqli_query($conn, $yearQuery);
    if ($row = mysqli_fetch_assoc($yearResult)) {
        $yearlyEnrollments[4 - $i] = intval($row['count']);
    }
}

// Get enrollment growth rate
$thisMonthEnrollments = 0;
$lastMonthEnrollments = 0;

$thisMonthQuery = "SELECT COUNT(*) as count FROM users WHERE status = 'approved' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$thisMonthResult = mysqli_query($conn, $thisMonthQuery);
if ($row = mysqli_fetch_assoc($thisMonthResult)) {
    $thisMonthEnrollments = intval($row['count']);
}

$lastMonthQuery = "SELECT COUNT(*) as count FROM users WHERE status = 'approved' AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
$lastMonthResult = mysqli_query($conn, $lastMonthQuery);
if ($row = mysqli_fetch_assoc($lastMonthResult)) {
    $lastMonthEnrollments = intval($row['count']);
}

$enrollmentGrowth = 0;
if ($lastMonthEnrollments > 0) {
    $enrollmentGrowth = round((($thisMonthEnrollments - $lastMonthEnrollments) / $lastMonthEnrollments) * 100, 1);
}

// ====== ENROLLMENT TRACKING DATA ======

// Get enrollment data - Monthly for current year
$monthlyEnrollment = array_fill(0, 12, 0);
$enrollmentQuery = "SELECT 
                        MONTH(created_at) as month,
                        COUNT(*) as total
                    FROM users 
                    WHERE YEAR(created_at) = '$currentYear' 
                    AND status = 'approved'
                    GROUP BY MONTH(created_at)";
$enrollmentResult = mysqli_query($conn, $enrollmentQuery);
if ($enrollmentResult) {
    while ($row = mysqli_fetch_assoc($enrollmentResult)) {
        $monthlyEnrollment[$row['month'] - 1] = intval($row['total']);
    }
}

// Get weekly enrollment for last 8 weeks
$weeklyEnrollment = [];
$weekLabels = [];
for ($i = 7; $i >= 0; $i--) {
    $weekStart = date('Y-m-d', strtotime("-$i weeks"));
    $weekEnd = date('Y-m-d', strtotime("-$i weeks +6 days"));
    
    $weekQuery = "SELECT COUNT(*) as total 
                  FROM users 
                  WHERE created_at BETWEEN '$weekStart 00:00:00' AND '$weekEnd 23:59:59'
                  AND status = 'approved'";
    $weekResult = mysqli_query($conn, $weekQuery);
    $row = mysqli_fetch_assoc($weekResult);
    
    $weeklyEnrollment[] = intval($row['total']);
    $weekLabels[] = date('M d', strtotime($weekStart));
}

// Get yearly enrollment for last 3 years
$yearlyEnrollment = [];
$yearLabels = [];
for ($i = 2; $i >= 0; $i--) {
    $year = date('Y') - $i;
    $yearQuery = "SELECT COUNT(*) as total 
                  FROM users 
                  WHERE YEAR(created_at) = '$year'
                  AND status = 'approved'";
    $yearResult = mysqli_query($conn, $yearQuery);
    $row = mysqli_fetch_assoc($yearResult);
    
    $yearlyEnrollment[] = intval($row['total']);
    $yearLabels[] = $year;
}

// Get enrollment by tutorial type
$enrollmentByTutorial = [
    'subject_only' => 0,
    'computer_only' => 0,
    'both' => 0
];

$tutorialQuery = "SELECT 
                    CASE 
                        WHEN subject_tutorials IS NOT NULL AND subject_tutorials != '' 
                             AND (computer_tutorials IS NULL OR computer_tutorials = '') THEN 'subject_only'
                        WHEN computer_tutorials IS NOT NULL AND computer_tutorials != '' 
                             AND (subject_tutorials IS NULL OR subject_tutorials = '') THEN 'computer_only'
                        WHEN subject_tutorials IS NOT NULL AND subject_tutorials != '' 
                             AND computer_tutorials IS NOT NULL AND computer_tutorials != '' THEN 'both'
                    END as tutorial_type,
                    COUNT(*) as total
                  FROM users 
                  WHERE status = 'approved'
                  GROUP BY tutorial_type";
$tutorialResult = mysqli_query($conn, $tutorialQuery);
if ($tutorialResult) {
    while ($row = mysqli_fetch_assoc($tutorialResult)) {
        if ($row['tutorial_type']) {
            $enrollmentByTutorial[$row['tutorial_type']] = intval($row['total']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | ACTS Learning Center</title>
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

.revenue-section {
    margin-top: 32px;
}

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

.chart-filter {
    display: flex;
    gap: 8px;
}

.filter-btn {
    padding: 8px 16px;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-btn:hover {
    border-color: #ec4899;
    color: #ec4899;
}

.filter-btn.active {
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
    border-color: #ec4899;
    color: white;
}

.chart-container {
    position: relative;
    height: 350px;
}

.revenue-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.revenue-stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.revenue-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #ec4899, #8b5cf6);
}

.revenue-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.revenue-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 12px;
}

.revenue-stat-icon.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    color: #10b981;
}

.revenue-stat-icon.warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
    color: #f59e0b;
}

.revenue-stat-icon.primary {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
    color: #3b82f6;
}

.revenue-stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.revenue-stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 800;
    color: #1a202c;
}

.revenue-stat-trend {
    font-size: 0.85rem;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.revenue-stat-trend.up {
    color: #10b981;
}

.revenue-stat-trend.down {
    color: #ef4444;
}

.recent-payments-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.payment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
}

.payment-item:hover {
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.payment-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.payment-avatar {
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

.payment-details h6 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 4px;
    font-size: 0.95rem;
}

.payment-details p {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
}

.payment-amount {
    text-align: right;
}

.payment-amount .amount {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 800;
    color: #10b981;
}

.payment-amount .date {
    font-size: 0.8rem;
    color: #64748b;
}

.section-header {
    margin-bottom: 24px;
}

.section-header h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 8px;
}

.section-header p {
    color: #64748b;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .chart-container {
        height: 250px;
    }

    .revenue-stats {
        grid-template-columns: 1fr;
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
    <a href="admindashboard.php" class="active">
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
    <a href="adminfeedback.php">
      <i class="fas fa-comments"></i>
      <span>Feedback</span>
    </a>
    <a href="logoutadmin.php" class="logout">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<div class="main">
  



  <div class="row g-4">

      <div class="col-md-6 col-lg-3">
      <div class="stat-card tutor">
        <div class="stat-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-label">Total Tutors</div>
        <div class="stat-value"><?php echo $totalTutors; ?></div>
        <div class="stat-trend">
          <i class="fas fa-user-tie"></i>
          <span>Active instructors</span>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-3">
      <div class="stat-card primary">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-label">Total Students</div>
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-trend up">
          <i class="fas fa-arrow-up"></i>
          <span><?php echo array_sum($monthlyEnrollment); ?> this year</span>
        </div>
      </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
      <div class="stat-card warning">
        <div class="stat-icon">
          <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-label">Pending Requests</div>
        <div class="stat-value"><?php echo $pendingCount; ?></div>
        <div class="stat-trend up">
          <i class="fas fa-exclamation-circle"></i>
          <span>Needs review</span>
        </div>
      </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
      <div class="stat-card info">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-label">This Month</div>
        <div class="stat-value"><?php echo $monthlyEnrollment[date('n') - 1]; ?></div>
        <div class="stat-trend up">
          <i class="fas fa-user-plus"></i>
          <span>New enrollments</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Enrollment Tracking Section -->
  <div class="revenue-section">
    <div class="section-header">
    
    </div>

    <!-- Enrollment Charts -->
    <div class="row g-4">
      <!-- Enrollment Trend Chart -->
      <div class="col-lg-8">
        <div class="chart-card">
          <div class="chart-header">
            <h4 class="chart-title">
              <i class="fas fa-user-graduate"></i>
              Student Enrollment Trend
            </h4>
            <div class="chart-filter">
              <button class="filter-btn active" onclick="updateEnrollmentChart('weekly')" data-period="weekly">Weekly</button>
              <button class="filter-btn" onclick="updateEnrollmentChart('monthly')" data-period="monthly">Monthly</button>
              <button class="filter-btn" onclick="updateEnrollmentChart('yearly')" data-period="yearly">Yearly</button>
            </div>
          </div>
          <div class="chart-container">
            <canvas id="enrollmentChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Enrollment by Tutorial Type -->
      <div class="col-lg-4">
        <div class="chart-card">
          <div class="chart-header">
            <h4 class="chart-title">
              <i class="fas fa-book"></i>
              Tutorial Types
            </h4>
          </div>
          <div class="chart-container">
            <canvas id="tutorialTypeChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue Analytics Section -->
  <div class="revenue-section">
    <div class="section-header">

    </div>

    <!-- Revenue Charts -->
    <div class="row g-4">
      <!-- Monthly Revenue Chart -->
      <div class="col-lg-8">
        <div class="chart-card">
          <div class="chart-header">
            <h4 class="chart-title">
              <i class="fas fa-chart-line"></i>
              Monthly Revenue Trend
            </h4>
            <div class="chart-filter">
              <button class="filter-btn active" onclick="updateChart('monthly')">Monthly</button>
              <button class="filter-btn" onclick="updateChart('quarterly')">Quarterly</button>
              <button class="filter-btn" onclick="updateChart('yearly')">Yearly</button>
            </div>
          </div>
          <div class="chart-container">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Revenue by Tutorial Type -->
      <div class="col-lg-4">
        <div class="chart-card">
          <div class="chart-header">
            <h4 class="chart-title">
              <i class="fas fa-chart-pie"></i>
              Revenue by Type
            </h4>
          </div>
          <div class="chart-container">
            <canvas id="typeChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Payments -->
    <div class="row g-4 mt-2">
      <div class="col-lg-12">
        <div class="recent-payments-card">
          <div class="chart-header">
            <h4 class="chart-title">
              <i class="fas fa-receipt"></i>
              Recent Payments
            </h4>
            <a href="adminbilling.php" class="filter-btn">View All</a>
          </div>

          <?php if (!empty($recentPayments)): ?>
            <?php foreach ($recentPayments as $payment): ?>
              <div class="payment-item">
                <div class="payment-info">
                  <div class="payment-avatar">
                    <?php echo strtoupper(substr($payment['student_name'], 0, 1)); ?>
                  </div>
                  <div class="payment-details">
                    <h6><?php echo htmlspecialchars($payment['student_name']); ?></h6>
                    <p><?php echo htmlspecialchars(isset($payment['billing_type']) ? ucfirst($payment['billing_type']) : 'Tutorial Payment'); ?></p>
                  </div>
                </div>
                <div class="payment-amount">
                  <div class="amount">₱<?php echo number_format($payment['amount'], 2); ?></div>
                  <div class="date"><?php echo date('M d, Y', strtotime($payment['due_date'])); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center py-4 text-muted">
              <i class="fas fa-inbox fa-3x mb-3"></i>
              <p>No recent payments</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Enrollment Data from PHP
const weeklyEnrollmentData = <?php echo json_encode($weeklyEnrollments); ?>;
const monthlyEnrollmentData = <?php echo json_encode($monthlyEnrollments); ?>;
const yearlyEnrollmentData = <?php echo json_encode($yearlyEnrollments); ?>;
const yearLabelsData = <?php echo json_encode($yearLabels); ?>;

// Generate week labels
const weekLabels = [];
for (let i = 11; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - (i * 7));
    weekLabels.push(date.toLocaleDateString('en-US', {month: 'short', day: 'numeric'}));
}

// Generate month labels
const monthLabels = [];
for (let i = 11; i >= 0; i--) {
    const date = new Date();
    date.setMonth(date.getMonth() - i);
    monthLabels.push(date.toLocaleDateString('en-US', {month: 'short', year: '2-digit'}));
}

// Enrollment Chart
const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
let enrollmentChart = new Chart(enrollmentCtx, {
    type: 'bar',
    data: {
        labels: weekLabels,
        datasets: [{
            label: 'New Enrollments',
            data: weeklyEnrollmentData,
            backgroundColor: 'rgba(139, 92, 246, 0.7)',
            borderColor: 'rgb(139, 92, 246)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(139, 92, 246, 0.9)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        return 'Students: ' + context.parsed.y;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    stepSize: 1,
                    callback: function(value) {
                        return Math.floor(value);
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Update Enrollment Chart function
function updateEnrollmentChart(period) {
    // Update active button
    document.querySelectorAll('[data-period]').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    let newLabels, newData, newLabel;
    
    if (period === 'weekly') {
        newLabels = weekLabels;
        newData = weeklyEnrollmentData;
        newLabel = 'Weekly Enrollments';
    } else if (period === 'monthly') {
        newLabels = monthLabels;
        newData = monthlyEnrollmentData;
        newLabel = 'Monthly Enrollments';
    } else if (period === 'yearly') {
        newLabels = yearLabelsData;
        newData = yearlyEnrollmentData;
        newLabel = 'Yearly Enrollments';
    }
    
    enrollmentChart.data.labels = newLabels;
    enrollmentChart.data.datasets[0].data = newData;
    enrollmentChart.data.datasets[0].label = newLabel;
    enrollmentChart.update();
}

// Enrollment Data from PHP
const enrollmentByTutorial = <?php echo json_encode($enrollmentByTutorial); ?>;

// Monthly Revenue Data from PHP
const monthlyData = <?php echo json_encode($monthlyRevenue); ?>;
const revenueByType = <?php echo json_encode($revenueByType); ?>;

// Tutorial Type Pie Chart
const tutorialTypeCtx = document.getElementById('tutorialTypeChart').getContext('2d');
const tutorialTypeChart = new Chart(tutorialTypeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Subject Only', 'Computer Only', 'Both'],
        datasets: [{
            data: [
                enrollmentByTutorial.subject_only || 0,
                enrollmentByTutorial.computer_only || 0,
                enrollmentByTutorial.both || 0
            ],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)'
            ],
            borderColor: [
                'rgb(59, 130, 246)',
                'rgb(245, 158, 11)',
                'rgb(139, 92, 246)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '600'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return label + ': ' + value + ' students (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Monthly Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue (₱)',
            data: monthlyData,
            borderColor: 'rgb(236, 72, 153)',
            backgroundColor: 'rgba(236, 72, 153, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgb(236, 72, 153)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        return 'Revenue: ₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString('en-PH');
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Revenue by Type Pie Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
const typeLabels = Object.keys(revenueByType);
const typeData = Object.values(revenueByType);

const typeChart = new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: typeLabels.length > 0 ? typeLabels : ['No Data'],
        datasets: [{
            data: typeData.length > 0 ? typeData : [1],
            backgroundColor: [
                'rgba(236, 72, 153, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderColor: [
                'rgb(236, 72, 153)',
                'rgb(139, 92, 246)',
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(245, 158, 11)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        weight: '600'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ₱' + value.toLocaleString('en-PH', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Filter button functionality for revenue
function updateChart(period) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.getAttribute('data-period')) return; // Skip enrollment buttons
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Here you can add AJAX call to fetch different period data
    console.log('Filtering revenue by:', period);
}
</script>

</body>
</html>