<?php
session_start();
include "db_connect.php";

// Check if tutor is logged in
if (!isset($_SESSION['tutor_id'])) {
    header("Location: tutor_login.php?error=Please login first");
    exit();
}

$tutor_id = $_SESSION['tutor_id'];
$tutor_name = $_SESSION['tutor_name'];

// Get tutor statistics
$stats_sql = "SELECT 
                (SELECT COUNT(*) FROM class_schedules WHERE tutor_id = ?) as total_classes,
                (SELECT COUNT(DISTINCT student_id) FROM class_schedules WHERE tutor_id = ?) as total_students
              FROM tutors WHERE id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("iii", $tutor_id, $tutor_id, $tutor_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tutor Dashboard | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
* {
    font-family: 'DM Sans', sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f8fafc;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 280px;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    color: white;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 32px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h4 {
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 8px;
}

.sidebar-header p {
    font-size: 0.9rem;
    opacity: 0.9;
}

.sidebar-menu {
    padding: 24px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 24px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: rgba(255, 255, 255, 0.15);
    border-left: 4px solid white;
}

.sidebar-menu a i {
    font-size: 1.2rem;
    width: 24px;
}

.main {
    margin-left: 280px;
    padding: 32px;
    min-height: 100vh;
}

.welcome-banner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 32px;
    color: white;
    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
}

.welcome-banner h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 12px;
}

.welcome-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.stat-card-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 20px;
}

.stat-card-icon.green {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
    color: #10b981;
}

.stat-card-icon.blue {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
    color: #3b82f6;
}

.stat-card-icon.purple {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(124, 58, 237, 0.15));
    color: #8b5cf6;
}

.stat-card-value {
    font-size: 2.5rem;
    font-weight: 800;
    font-family: 'Playfair Display', serif;
    color: #1a202c;
    margin-bottom: 8px;
}

.stat-card-label {
    font-size: 0.95rem;
    color: #64748b;
    font-weight: 600;
}

.quick-actions {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
}

.quick-actions h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 24px;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.action-btn {
    padding: 24px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-decoration: none;
    color: #1a202c;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
}

.action-btn:hover {
    background: white;
    border-color: #10b981;
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.action-btn i {
    font-size: 2rem;
    color: #10b981;
}

.action-btn span {
    font-weight: 600;
}

@media (max-width: 968px) {
    .sidebar {
        transform: translateX(-280px);
    }
    
    .main {
        margin-left: 0;
    }
    
    .welcome-banner h1 {
        font-size: 2rem;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-chalkboard-teacher"></i> Tutor Portal</h4>
        <p>ACTS Learning Center</p>
    </div>
    
    <div class="sidebar-menu">
        <a href="tutordashboard.php" class="active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="tutor_classes.php">
            <i class="fas fa-book-open"></i>
            <span>My Classes</span>
        </a>
        <a href="tutor_students.php">
            <i class="fas fa-user-graduate"></i>
            <span>My Students</span>
        </a>
        <a href="tutor_schedule.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Schedule</span>
        </a>
        <a href="tutor_attendance.php">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="tutor_grades.php">
            <i class="fas fa-chart-bar"></i>
            <span>Grades</span>
        </a>
        <a href="tutor_profile.php">
            <i class="fas fa-user-circle"></i>
            <span>My Profile</span>
        </a>
        <a href="logout.php" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="main">
    <div class="welcome-banner">
        <h1>Welcome back, <?php echo htmlspecialchars($tutor_name); ?>! 👋</h1>
        <p>Here's what's happening with your classes today</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-card-value"><?php echo isset($stats['total_classes']) ? $stats['total_classes'] : 0; ?></div>
            <div class="stat-card-label">Total Classes</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-card-value"><?php echo isset($stats['total_students']) ? $stats['total_students'] : 0; ?></div>
            <div class="stat-card-label">Total Students</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon purple">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-value">0</div>
            <div class="stat-card-label">Classes Today</div>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="tutor_classes.php" class="action-btn">
                <i class="fas fa-book-open"></i>
                <span>View Classes</span>
            </a>
            <a href="tutor_attendance.php" class="action-btn">
                <i class="fas fa-clipboard-check"></i>
                <span>Mark Attendance</span>
            </a>
            <a href="tutor_grades.php" class="action-btn">
                <i class="fas fa-chart-bar"></i>
                <span>Enter Grades</span>
            </a>
            <a href="tutor_schedule.php" class="action-btn">
                <i class="fas fa-calendar-alt"></i>
                <span>View Schedule</span>
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>