<!--Announcements Page-->
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

// Fetch announcements
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements_result = mysqli_query($conn, $announcements_sql);

$announcements = [];
if ($announcements_result) {
    while ($row = mysqli_fetch_assoc($announcements_result)) {
        $announcements[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Announcements | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">
<style>
* {
    font-family: 'DM Sans', sans-serif;
}

body {
    background: #f8fafc;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 32px;
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.page-header-content {
    position: relative;
    z-index: 1;
}

.page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.page-header p {
    font-size: 1.1rem;
    opacity: 0.95;
    margin: 0;
}

.announcements-container {
    display: grid;
    gap: 24px;
}

.announcement-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.announcement-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #667eea, #764ba2);
}

.announcement-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.announcement-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 20px;
    flex-wrap: wrap;
}

.announcement-icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #667eea;
    flex-shrink: 0;
}

.announcement-meta {
    flex: 1;
    min-width: 0;
}

.announcement-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
    line-height: 1.3;
}

.announcement-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.announcement-date {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
}

.announcement-category {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.category-general {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    color: #667eea;
    border: 2px solid rgba(102, 126, 234, 0.3);
}

.category-important {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
    color: #ef4444;
    border: 2px solid rgba(239, 68, 68, 0.3);
}

.category-event {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
    color: #10b981;
    border: 2px solid rgba(16, 185, 129, 0.3);
}

.category-reminder {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.15));
    color: #f59e0b;
    border: 2px solid rgba(245, 158, 11, 0.3);
}

.announcement-content {
    color: #4b5563;
    font-size: 1rem;
    line-height: 1.8;
    margin-bottom: 24px;
}

.announcement-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
}

.announcement-author {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.author-info {
    display: flex;
    flex-direction: column;
}

.author-label {
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.author-name {
    font-size: 0.95rem;
    color: #1a202c;
    font-weight: 700;
}

.announcement-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.btn-read-more {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-read-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.filter-section {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
}

.filter-tabs {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 24px;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    background: white;
    color: #64748b;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-tab:hover {
    border-color: #667eea;
    background: #f8f9ff;
    color: #667eea;
}

.filter-tab.active {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.empty-state i {
    font-size: 5rem;
    color: #cbd5e1;
    margin-bottom: 24px;
}

.empty-state h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: #1a202c;
    margin-bottom: 12px;
    font-weight: 700;
}

.empty-state p {
    color: #64748b;
    font-size: 1rem;
}

.modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 24px 32px;
    border: none;
}

.modal-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.5rem;
}

.btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 32px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-announcement-content {
    color: #4b5563;
    font-size: 1.05rem;
    line-height: 1.8;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.stat-box {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 20px;
    border-radius: 14px;
    border: 2px solid rgba(102, 126, 234, 0.2);
}

.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 800;
    color: #667eea;
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

.announcement-card {
    animation: fadeInUp 0.5s ease forwards;
}

.announcement-card:nth-child(1) { animation-delay: 0.1s; }
.announcement-card:nth-child(2) { animation-delay: 0.2s; }
.announcement-card:nth-child(3) { animation-delay: 0.3s; }
.announcement-card:nth-child(4) { animation-delay: 0.4s; }

@media (max-width: 768px) {
    .page-header {
        padding: 28px 24px;
    }
    
    .page-header h2 {
        font-size: 2rem;
    }
    
    .announcement-card {
        padding: 24px;
    }
    
    .announcement-title {
        font-size: 1.3rem;
    }
    
    .announcement-header {
        flex-direction: column;
    }
    
    .announcement-footer {
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
        <a href="announcements.php" class="active">
            <i class="fas fa-bullhorn"></i>
            <span>Announcements</span>
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
        <div class="page-header-content">
            <h2>
                <i class="fas fa-bullhorn"></i>
                Announcements
            </h2>
            <p>Stay updated with the latest news and important information</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Total Announcements</div>
            <div class="stat-value"><?php echo count($announcements); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">This Month</div>
            <div class="stat-value">
                <?php 
                $this_month = 0;
                foreach ($announcements as $ann) {
                    if (date('Y-m', strtotime($ann['created_at'])) === date('Y-m')) {
                        $this_month++;
                    }
                }
                echo $this_month;
                ?>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Important</div>
            <div class="stat-value">
                <?php 
                $important = 0;
                foreach ($announcements as $ann) {
                    if ($ann['category'] === 'important') {
                        $important++;
                    }
                }
                echo $important;
                ?>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterAnnouncements('all')">
                <i class="fas fa-list"></i> All
            </button>
            <button class="filter-tab" onclick="filterAnnouncements('general')">
                <i class="fas fa-info-circle"></i> General
            </button>
            <button class="filter-tab" onclick="filterAnnouncements('important')">
                <i class="fas fa-exclamation-circle"></i> Important
            </button>
            <button class="filter-tab" onclick="filterAnnouncements('event')">
                <i class="fas fa-calendar-alt"></i> Events
            </button>
            <button class="filter-tab" onclick="filterAnnouncements('reminder')">
                <i class="fas fa-bell"></i> Reminders
            </button>
        </div>
    </div>

    <div class="announcements-container" id="announcementsContainer">
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card" data-category="<?php echo htmlspecialchars($announcement['category']); ?>">
                    <div class="announcement-header">
                        <div class="announcement-icon-wrapper">
                            <?php
                            $icons = [
                                'general' => 'fa-info-circle',
                                'important' => 'fa-exclamation-circle',
                                'event' => 'fa-calendar-star',
                                'reminder' => 'fa-bell'
                            ];
                            $icon = isset($icons[$announcement['category']]) ? $icons[$announcement['category']] : 'fa-bullhorn';
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="announcement-meta">
                            <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <div class="announcement-info">
                                <span class="announcement-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('F d, Y', strtotime($announcement['created_at'])); ?>
                                </span>
                                <span class="announcement-category category-<?php echo $announcement['category']; ?>">
                                    <i class="fas fa-tag"></i>
                                    <?php echo ucfirst($announcement['category']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="announcement-content">
                        <?php 
                        $content = $announcement['content'];
                        $preview = strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                        echo nl2br(htmlspecialchars($preview));
                        ?>
                    </div>

                    <div class="announcement-footer">
                        <div class="announcement-author">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($announcement['author'], 0, 1)); ?>
                            </div>
                            <div class="author-info">
                                <span class="author-label">Posted by</span>
                                <span class="author-name"><?php echo htmlspecialchars($announcement['author']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (strlen($announcement['content']) > 200): ?>
                            <div class="announcement-actions">
                                <button class="btn-action btn-read-more" 
                                        onclick='showFullAnnouncement(<?php echo json_encode($announcement); ?>)'>
                                    <i class="fas fa-book-open"></i>
                                    Read More
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <h4>No Announcements Yet</h4>
                <p>Check back later for important updates and news</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="announcement-info mb-4" id="modalInfo"></div>
                <div class="modal-announcement-content" id="modalContent"></div>
                <div class="announcement-author mt-4 pt-4" style="border-top: 2px solid #f1f5f9;" id="modalAuthor"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function filterAnnouncements(category) {
    const cards = document.querySelectorAll('.announcement-card');
    const tabs = document.querySelectorAll('.filter-tab');
    
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.closest('.filter-tab').classList.add('active');
    
    cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function showFullAnnouncement(announcement) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-bullhorn"></i> ' + announcement.title;
    
    const categoryClass = 'category-' + announcement.category;
    const categoryIcon = {
        'general': 'fa-info-circle',
        'important': 'fa-exclamation-circle',
        'event': 'fa-calendar-star',
        'reminder': 'fa-bell'
    };
    
    document.getElementById('modalInfo').innerHTML = `
        <div class="announcement-info">
            <span class="announcement-date">
                <i class="fas fa-clock"></i>
                ${new Date(announcement.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
            </span>
            <span class="announcement-category ${categoryClass}">
                <i class="fas ${categoryIcon[announcement.category] || 'fa-tag'}"></i>
                ${announcement.category.charAt(0).toUpperCase() + announcement.category.slice(1)}
            </span>
        </div>
    `;
    
    document.getElementById('modalContent').innerHTML = announcement.content.replace(/\n/g, '<br>');
    
    document.getElementById('modalAuthor').innerHTML = `
        <div class="author-avatar">
            ${announcement.author.charAt(0).toUpperCase()}
        </div>
        <div class="author-info">
            <span class="author-label">Posted by</span>
            <span class="author-name">${announcement.author}</span>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('announcementModal')).show();
}
</script>

</body>
</html>