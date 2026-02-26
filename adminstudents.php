  <?php
  session_start();
  include "db_connect.php";

  // Handle search and filters
  $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
  $tutorial_type = isset($_GET['tutorial_type']) ? $_GET['tutorial_type'] : '';

  // Build the query with filters
  $sql = "SELECT 
            id,
            student_name AS name,
            email,
            contact AS phone,
            subject_tutorials,
            computer_tutorials,
            sessions_per_week,
            schedule_preference,
            created_at AS enrollment_date
          FROM users
          WHERE status = 'approved'";

  // Add search condition
  if (!empty($search)) {
      $sql .= " AND (student_name LIKE '%$search%' OR email LIKE '%$search%')";
  }

  // Add tutorial type filter
  if (!empty($tutorial_type)) {
      if ($tutorial_type === 'subject') {
          $sql .= " AND subject_tutorials IS NOT NULL AND subject_tutorials != ''";
      } elseif ($tutorial_type === 'computer') {
          $sql .= " AND computer_tutorials IS NOT NULL AND computer_tutorials != ''";
      } elseif ($tutorial_type === 'both') {
          $sql .= " AND subject_tutorials IS NOT NULL AND subject_tutorials != '' 
                    AND computer_tutorials IS NOT NULL AND computer_tutorials != ''";
      }
  }

  $sql .= " ORDER BY student_name ASC";

  $result = $conn->query($sql);

  $enrolled_students = [];
  while ($row = $result->fetch_assoc()) {
      $enrolled_students[] = $row;
  }

  $total_students = count($enrolled_students);
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Enrolled Students | ACTS Admin</title>
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

  .page-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .page-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .header-actions {
    display: flex;
    gap: 12px;
  }

  .btn-add {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-add:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
  }

  .btn-export {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid white;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-export:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
  }

  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }

  .mini-stat {
    text-align: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    border: 2px solid rgba(255, 255, 255, 0.2);
  }

  .mini-stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: white;
    margin-bottom: 8px;
    font-family: 'Playfair Display', serif;
  }

  .mini-stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
  }

  .filter-section {
    background: white;
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
  }

  .filter-section .row {
    align-items: end;
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
    border: 2px solid #e5e7eb;
    padding: 10px 16px;
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
    font-weight: 700;
    transition: all 0.3s ease;
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
    color: white;
  }

  .btn-clear {
    background: #f3f4f6;
    color: #1a202c;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
  }

  .btn-clear:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
  }

  .view-toggle {
    display: flex;
    gap: 8px;
    background: #f3f4f6;
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
    color: #64748b;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }

  .view-toggle button.active {
    background: white;
    color: #667eea;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    transition: opacity 0.3s ease;
  }

  .student-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .student-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .student-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
  }

  .student-card:hover::before {
    transform: scaleX(1);
  }

  .card-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f3f4f6;
  }

  .student-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
    margin: 0 auto 16px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    position: relative;
  }

  .student-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 8px;
  }

  .tutorial-info {
    margin-bottom: 8px;
  }

  .tutorial-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
  }

  .tutorial-value {
    color: #667eea;
    font-weight: 600;
    font-size: 0.85rem;
    line-height: 1.4;
  }

  .tutorial-value.empty {
    color: #9ca3af;
    font-style: italic;
  }

  .card-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
    margin-top: 16px;
  }

  .stat-box {
    background: #f9fafb;
    padding: 12px;
    border-radius: 10px;
    text-align: center;
  }

  .stat-box-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 4px;
  }

  .stat-box-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a202c;
  }

  .stat-box.sessions .stat-box-value {
    color: #10b981;
  }

  .stat-box.schedule .stat-box-value {
    color: #3b82f6;
    font-size: 0.9rem;
  }

  .card-actions {
    display: flex;
    gap: 8px;
  }

  .btn-card-action {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.9rem;
    cursor: pointer;
  }

  .btn-view-profile {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
  }

  .btn-view-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  .btn-edit {
    background: #f3f4f6;
    color: #1a202c;
  }

  .btn-edit:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
  }

  .students-table {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid #f1f5f9;
    overflow-x: auto;
    display: none;
    transition: opacity 0.3s ease;
  }

  .table-custom {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
  }

  .table-custom thead th {
    background: transparent;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border: none;
  }

  .table-custom tbody tr {
    background: #f9fafb;
    transition: all 0.3s ease;
  }

  .table-custom tbody tr:hover {
    background: #f3f4f6;
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .table-custom tbody td {
    padding: 16px;
    border: none;
    vertical-align: middle;
  }

  .table-custom tbody tr td:first-child {
    border-radius: 10px 0 0 10px;
  }

  .table-custom tbody tr td:last-child {
    border-radius: 0 10px 10px 0;
  }

  .table-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
  }

  .table-name {
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 2px;
  }

  .table-email {
    font-size: 0.85rem;
    color: #64748b;
  }

  .action-btns {
    display: flex;
    gap: 8px;
  }

  .btn-table-action {
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

  .btn-table-action.view {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
  }

  .btn-table-action.edit {
    background: linear-gradient(135deg, #f59e0b, #d97706);
  }

  .btn-table-action.delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
  }

  .btn-table-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .tutorial-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
  }

  .tutorial-tag {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(102, 126, 234, 0.2);
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  }

  .empty-state i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 20px;
  }

  .empty-state h4 {
    font-family: 'Playfair Display', serif;
    color: #1a202c;
    margin-bottom: 12px;
  }

  .empty-state p {
    color: #64748b;
    margin-bottom: 24px;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .student-card {
    animation: fadeInUp 0.5s ease forwards;
  }

  .student-card:nth-child(1) { animation-delay: 0.05s; }
  .student-card:nth-child(2) { animation-delay: 0.1s; }
  .student-card:nth-child(3) { animation-delay: 0.15s; }
  .student-card:nth-child(4) { animation-delay: 0.2s; }
  .student-card:nth-child(5) { animation-delay: 0.25s; }
  .student-card:nth-child(6) { animation-delay: 0.3s; }

  @media (max-width: 768px) {
    .students-grid {
      grid-template-columns: 1fr;
    }
    
    .header-actions {
      flex-direction: column;
      width: 100%;
    }
    
    .btn-add, .btn-export {
      width: 100%;
      justify-content: center;
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
      <a href="adminstudents.php" class="active">
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
      <div class="page-header-content">
        <div class="page-header-top">
          <h2>
            <i class="fas fa-user-graduate"></i>
            Enrolled Students
          </h2>
          <div class="header-actions">
            <button class="btn btn-export">
              <i class="fas fa-file-excel"></i>
              Export
            </button>
            <button class="btn btn-add" onclick="window.location.href='add_student.php'">
              <i class="fas fa-plus"></i>
              Add Student
            </button>
          </div>
        </div>
        
        <div class="stats-row">
          <div class="mini-stat">
            <div class="mini-stat-value"><?php echo $total_students; ?></div>
            <div class="mini-stat-label">Total Students</div>
          </div>
          <div class="mini-stat">
            <div class="mini-stat-value">
              <?php 
                $avg_sessions = 0;
                $count = 0;
                foreach ($enrolled_students as $s) {
                  if (!empty($s['sessions_per_week'])) {
                    $avg_sessions += $s['sessions_per_week'];
                    $count++;
                  }
                }
                echo $count > 0 ? round($avg_sessions / $count, 1) : '0';
              ?>
            </div>
            <div class="mini-stat-label">Avg Sessions/Week</div>
          </div>
          <div class="mini-stat">
            <div class="mini-stat-value">
              <?php 
                $subject_count = 0;
                foreach ($enrolled_students as $s) {
                  if (!empty($s['subject_tutorials'])) {
                    $subject_count++;
                  }
                }
                echo $subject_count;
              ?>
            </div>
            <div class="mini-stat-label">Subject Tutorials</div>
          </div>
          <div class="mini-stat">
            <div class="mini-stat-value">
              <?php 
                $computer_count = 0;
                foreach ($enrolled_students as $s) {
                  if (!empty($s['computer_tutorials'])) {
                    $computer_count++;
                  }
                }
                echo $computer_count;
              ?>
            </div>
            <div class="mini-stat-label">Computer Tutorials</div>
          </div>
        </div>
      </div>
    </div>

    <div class="filter-section">
      <form method="GET" action="" id="filterForm">
        <div class="row g-3">
          <div class="col-md-5">
            <label>Search Student</label>
            <input type="text" class="form-control" name="search" 
                  placeholder="Search by name or email..." 
                  value="<?php echo htmlspecialchars($search); ?>">
          </div>
          <div class="col-md-4">
            <label>Tutorial Type</label>
            <select class="form-control" name="tutorial_type">
              <option value="">All Types</option>
              <option value="subject" <?php echo $tutorial_type === 'subject' ? 'selected' : ''; ?>>Subject Tutorials</option>
              <option value="computer" <?php echo $tutorial_type === 'computer' ? 'selected' : ''; ?>>Computer Tutorials</option>
              <option value="both" <?php echo $tutorial_type === 'both' ? 'selected' : ''; ?>>Both</option>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-filter w-100">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
            <?php if (!empty($search) || !empty($tutorial_type)): ?>
              <button type="button" class="btn btn-clear w-100 mt-2" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear
              </button>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>

    <div class="view-toggle">
      <button class="active" data-view="grid" onclick="toggleView('grid')">
        <i class="fas fa-th"></i>
        Grid View
      </button>
      <button data-view="list" onclick="toggleView('list')">
        <i class="fas fa-list"></i>
        List View
      </button>
    </div>

    <?php if (count($enrolled_students) > 0): ?>
      <div class="students-grid">
        <?php foreach ($enrolled_students as $student): ?>
          <div class="student-card">
            <div class="card-header">
              <div class="student-avatar">
                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
              </div>
              <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
              
              <div class="tutorial-info">
                <span class="tutorial-label">Subject Tutorials</span>
                <div class="tutorial-value <?php echo empty($student['subject_tutorials']) ? 'empty' : ''; ?>">
                  <?php echo !empty($student['subject_tutorials']) ? htmlspecialchars($student['subject_tutorials']) : 'None'; ?>
                </div>
              </div>

              <div class="tutorial-info">
                <span class="tutorial-label">Computer Tutorials</span>
                <div class="tutorial-value <?php echo empty($student['computer_tutorials']) ? 'empty' : ''; ?>">
                  <?php echo !empty($student['computer_tutorials']) ? htmlspecialchars($student['computer_tutorials']) : 'None'; ?>
                </div>
              </div>
            </div>

            <div class="card-stats">
              <div class="stat-box sessions">
                <div class="stat-box-label">Sessions/Week</div>
                <div class="stat-box-value"><?php echo isset($student['sessions_per_week']) ? $student['sessions_per_week'] : '—'; ?></div>
              </div>
              <div class="stat-box schedule">
                <div class="stat-box-label">Schedule</div>
                <div class="stat-box-value">
                  <?php 
                    if ($student['schedule_preference'] === 'self') {
                      echo 'Self';
                    } elseif ($student['schedule_preference'] === 'tutor') {
                      echo 'Tutor';
                    } else {
                      echo '—';
                    }
                  ?>
                </div>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn-card-action btn-view-profile" onclick="viewProfile(<?php echo $student['id']; ?>)">
                <i class="fas fa-user"></i>
                View Profile
              </button>
              <button class="btn-card-action btn-edit" onclick="editStudent(<?php echo $student['id']; ?>)">
                <i class="fas fa-edit"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="students-table">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Student</th>
              <th>Subject Tutorials</th>
              <th>Computer Tutorials</th>
              <th>Sessions/Week</th>
              <th>Schedule</th>
              <th>Enrolled</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enrolled_students as $student): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="table-avatar">
                      <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>
                    <div>
                      <div class="table-name"><?php echo htmlspecialchars($student['name']); ?></div>
                      <div class="table-email"><?php echo htmlspecialchars($student['email']); ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if (!empty($student['subject_tutorials'])): ?>
                    <div class="tutorial-tags">
                      <?php 
                        $subjects = explode(', ', $student['subject_tutorials']);
                        foreach ($subjects as $subject): 
                      ?>
                        <span class="tutorial-tag"><?php echo htmlspecialchars($subject); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span style="color: #9CA3AF; font-style: italic;">None</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($student['computer_tutorials'])): ?>
                    <div class="tutorial-tags">
                      <?php 
                        $computers = explode(', ', $student['computer_tutorials']);
                        foreach ($computers as $computer): 
                      ?>
                        <span class="tutorial-tag"><?php echo htmlspecialchars($computer); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span style="color: #9CA3AF; font-style: italic;">None</span>
                  <?php endif; ?>
                </td>
                <td><strong><?php echo isset($student['sessions_per_week']) ? $student['sessions_per_week'] : '—'; ?></strong></td>
                <td>
                  <?php 
                    if ($student['schedule_preference'] === 'self') {
                      echo '<span class="tutorial-tag">Self-Scheduled</span>';
                    } elseif ($student['schedule_preference'] === 'tutor') {
                      echo '<span class="tutorial-tag">Tutor-Suggested</span>';
                    } else {
                      echo '—';
                    }
                  ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-table-action view" onclick="viewProfile(<?php echo $student['id']; ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-table-action edit" onclick="editStudent(<?php echo $student['id']; ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-table-action delete" onclick="deleteStudent(<?php echo $student['id']; ?>)">
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
        <i class="fas fa-user-graduate"></i>
        <h4>No Students Found</h4>
        <p>
          <?php if (!empty($search) || !empty($tutorial_type)): ?>
            No students match your search criteria. Try adjusting your filters.
          <?php else: ?>
            There are no enrolled students yet. Add your first student to get started.
          <?php endif; ?>
        </p>
        <?php if (!empty($search) || !empty($tutorial_type)): ?>
          <button class="btn btn-add" onclick="clearFilters()">
            <i class="fas fa-times"></i>
            Clear Filters
          </button>
        <?php else: ?>
          <button class="btn btn-add" onclick="window.location.href='add_student.php'">
            <i class="fas fa-plus"></i>
            Add Student
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
  function toggleView(view) {
    const gridView = document.querySelector('.students-grid');
    const listView = document.querySelector('.students-table');
    const buttons = document.querySelectorAll('.view-toggle button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`button[data-view="${view}"]`).classList.add('active');
    
    if (view === 'grid') {
      gridView.style.display = 'grid';
      listView.style.display = 'none';
    } else {
      gridView.style.display = 'none';
      listView.style.display = 'block';
    }
  }

  function clearFilters() {
    window.location.href = 'adminstudents.php';
  }

  function viewProfile(id) {
    window.location.href = 'adminstudent_profile.php?id=' + id;
  }

  function editStudent(id) {
    window.location.href = 'edit_student.php?id=' + id;
  }

  function deleteStudent(id) {
    if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
      window.location.href = 'delete_student.php?id=' + id;
    }
  }
  </script>

  </body>
  </html>