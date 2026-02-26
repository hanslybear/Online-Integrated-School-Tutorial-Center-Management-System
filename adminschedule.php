<?php
session_start();
include "db_connect.php";

// Fetch all tutorials
$tutorials_sql = "SELECT DISTINCT subject_tutorials, computer_tutorials FROM users WHERE status = 'approved' AND (subject_tutorials IS NOT NULL OR computer_tutorials IS NOT NULL)";
$tutorials_result = $conn->query($tutorials_sql);

$all_tutorials = [];
while ($row = $tutorials_result->fetch_assoc()) {
    if (!empty($row['subject_tutorials'])) {
        $subjects = explode(', ', $row['subject_tutorials']);
        foreach ($subjects as $subject) {
            if (!in_array($subject, $all_tutorials)) {
                $all_tutorials[] = $subject;
            }
        }
    }
    if (!empty($row['computer_tutorials'])) {
        $computers = explode(', ', $row['computer_tutorials']);
        foreach ($computers as $computer) {
            if (!in_array($computer, $all_tutorials)) {
                $all_tutorials[] = $computer;
            }
        }
    }
}
sort($all_tutorials);

// Fetch all approved students
$students_sql = "SELECT id, student_name, email FROM users WHERE status = 'approved' ORDER BY student_name ASC";
$students_result = $conn->query($students_sql);
$all_students = [];
while ($student = $students_result->fetch_assoc()) {
    $all_students[] = $student;
}

// Fetch all tutors
$tutors_sql = "SELECT id, tutor_name FROM tutors ORDER BY tutor_name ASC";
$tutors_result = $conn->query($tutors_sql);
$all_tutors = [];
while ($tutor = $tutors_result->fetch_assoc()) {
    $all_tutors[] = $tutor;
}

// Fetch schedules
$sql = "SELECT * FROM class_schedules ORDER BY schedule_date, start_time";
$result = $conn->query($sql);

$schedule_data = [];
while ($row = $result->fetch_assoc()) {
    $schedule_data[$row['schedule_date']][] = [
        'id' => $row['id'],
        'time' => date("h:i A", strtotime($row['start_time'])) . " - " . date("h:i A", strtotime($row['end_time'])),
        'subject' => $row['subject'],
        'students_enrolled' => isset($row['students_enrolled']) ? $row['students_enrolled'] : '',
        'tutor_id' => isset($row['tutor_id']) ? $row['tutor_id'] : '',
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'schedule_date' => $row['schedule_date']
    ];
}

$current_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tutorial Schedule | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="adminstyle.css">
<link rel="icon" type="image/png" href="actslogo.png">

<style>
.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px;
  border-radius: 20px;
  margin-bottom: 32px;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
  color: white;
}

.page-header h2 {
  font-weight: 700;
  margin: 0 0 12px 0;
  font-size: 2rem;
}

.btn-add-class {
  background: white;
  color: #667eea;
  border: none;
  padding: 14px 28px;
  border-radius: 12px;
  font-weight: 700;
  transition: all 0.3s ease;
  margin-top: 20px;
}

.btn-add-class:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.schedule-calendar {
  background: white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.date-header {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 20px 24px;
  border-radius: 16px;
  margin-bottom: 24px;
}

.schedule-item {
  background: white;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border-left: 6px solid #667eea;
}

.schedule-time {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 700;
  color: #667eea;
  display: inline-block;
  margin-bottom: 16px;
}

.empty-schedule {
  text-align: center;
  padding: 80px 40px;
}

.modal-header {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 20px 20px 0 0;
  padding: 24px 32px;
}

.modal-header .btn-close {
  filter: brightness(0) invert(1);
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
    <a href="adminschedule.php" class="active">
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
  <div class="page-header">
    <h2><i class="fas fa-calendar-week"></i> Tutorial Schedule</h2>
    <button class="btn-add-class" data-bs-toggle="modal" data-bs-target="#addClassModal">
      <i class="fas fa-plus-circle"></i> Add Tutorial Session
    </button>
  </div>

  <div class="schedule-calendar">
    <?php 
    $dates = array_keys($schedule_data);
    sort($dates);
    
    if (count($dates) > 0):
      foreach ($dates as $date): 
        $formatted_date = date('l, F j, Y', strtotime($date));
    ?>
      <div class="schedule-group">
        <div class="date-header">
          <h4><i class="fas fa-calendar-day"></i> <?php echo $formatted_date; ?></h4>
        </div>

        <?php foreach ($schedule_data[$date] as $class): ?>
          <?php 
            $student_id = $class['students_enrolled'];
            $student_name = '';
            if ($student_id) {
              $stmt = $conn->prepare("SELECT student_name FROM users WHERE id = ?");
              $stmt->bind_param("i", $student_id);
              $stmt->execute();
              $result = $stmt->get_result();
              if ($row = $result->fetch_assoc()) {
                $student_name = $row['student_name'];
              }
            }
            
            $tutor_id = $class['tutor_id'];
            $tutor_name = '';
            if ($tutor_id) {
              $stmt = $conn->prepare("SELECT tutor_name FROM tutors WHERE id = ?");
              $stmt->bind_param("i", $tutor_id);
              $stmt->execute();
              $result = $stmt->get_result();
              if ($row = $result->fetch_assoc()) {
                $tutor_name = $row['tutor_name'];
              }
            }
          ?>
          <div class="schedule-item">
            <div class="schedule-time">
              <i class="fas fa-clock"></i> <?php echo $class['time']; ?>
            </div>
            <h5><?php echo htmlspecialchars($class['subject']); ?></h5>
            <p><i class="fas fa-user"></i> Student: <?php echo $student_name ?: 'None'; ?></p>
            <p><i class="fas fa-chalkboard-teacher"></i> Tutor: <?php echo $tutor_name ?: 'None'; ?></p>
            <button class="btn btn-warning btn-sm" onclick='openEditModal(<?php echo $class["id"]; ?>, "<?php echo addslashes($class["subject"]); ?>", "<?php echo $date; ?>", "<?php echo $class["start_time"]; ?>", "<?php echo $class["end_time"]; ?>", "<?php echo $student_id; ?>", "<?php echo $tutor_id; ?>")'>
              <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteSchedule(<?php echo $class['id']; ?>)">
              <i class="fas fa-trash"></i> Delete
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php 
      endforeach;
    else:
    ?>
      <div class="empty-schedule">
        <i class="fas fa-calendar-times" style="font-size: 5rem; color: #ccc;"></i>
        <h4>No Tutorials Scheduled</h4>
        <p>Click "Add Tutorial Session" to create one.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add Tutorial Session</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addClassForm" action="save_schedule.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Student *</label>
            <select name="students_enrolled" id="students_add" class="form-select" required>
              <option value="">Select student...</option>
              <?php foreach ($all_students as $s): ?>
                <option value="<?php echo $s['id']; ?>">
                  <?php echo htmlspecialchars($s['student_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Tutor *</label>
            <select name="tutor_id" id="tutor_add" class="form-select" required>
              <option value="">Select tutor...</option>
              <?php foreach ($all_tutors as $t): ?>
                <option value="<?php echo $t['id']; ?>">
                  <?php echo htmlspecialchars($t['tutor_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Tutorial / Subject *</label>
            <select name="subject" id="subject_add" class="form-select" required disabled>
              <option value="">Select student first</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Date *</label>
            <input type="date" name="schedule_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Time *</label>
              <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Time *</label>
              <input type="time" name="end_time" class="form-control" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addClassForm" class="btn btn-success">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Tutorial Session</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editClassForm" action="update_schedule.php" method="POST">
          <input type="hidden" name="id" id="edit_id">
          
          <div class="mb-3">
            <label class="form-label">Student *</label>
            <select name="students_enrolled" id="students_edit" class="form-select" required>
              <option value="">Select student...</option>
              <?php foreach ($all_students as $s): ?>
                <option value="<?php echo $s['id']; ?>">
                  <?php echo htmlspecialchars($s['student_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Tutor *</label>
            <select name="tutor_id" id="tutor_edit" class="form-select" required>
              <option value="">Select tutor...</option>
              <?php foreach ($all_tutors as $t): ?>
                <option value="<?php echo $t['id']; ?>">
                  <?php echo htmlspecialchars($t['tutor_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Tutorial / Subject *</label>
            <select name="subject" id="edit_subject" class="form-select" required>
              <option value="">Select Tutorial</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Date *</label>
            <input type="date" name="schedule_date" id="edit_schedule_date" class="form-control" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Time *</label>
              <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Time *</label>
              <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="editClassForm" class="btn btn-warning">Update</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const studentTutorials = {
  <?php 
  $st_query = "SELECT id, subject_tutorials, computer_tutorials FROM users WHERE status = 'approved'";
  $st_result = $conn->query($st_query);
  $first = true;
  while ($st = $st_result->fetch_assoc()) {
    if (!$first) echo ",\n  ";
    $first = false;
    
    $tutorials = [];
    if (!empty($st['subject_tutorials'])) {
      foreach (explode(', ', $st['subject_tutorials']) as $subj) {
        $tutorials[] = trim($subj);
      }
    }
    if (!empty($st['computer_tutorials'])) {
      foreach (explode(', ', $st['computer_tutorials']) as $comp) {
        $tutorials[] = trim($comp);
      }
    }
    
    echo $st['id'] . ': ' . json_encode($tutorials);
  }
  ?>
};

$(document).ready(function() {
  $('#students_add, #students_edit, #tutor_add, #tutor_edit').select2({
    theme: 'bootstrap-5',
    width: '100%'
  });
  
  $('#students_add').on('change', function() {
    const id = $(this).val();
    const subj = $('#subject_add');
    
    if (id && studentTutorials[id]) {
      const tuts = studentTutorials[id];
      subj.prop('disabled', false).empty().append('<option value="">Select...</option>');
      
      if (tuts.length > 0) {
        tuts.forEach(t => subj.append(`<option value="${t}">${t}</option>`));
      } else {
        subj.append('<option disabled>No tutorials enrolled</option>');
      }
    } else {
      subj.prop('disabled', true).empty().append('<option value="">Select student first</option>');
    }
  });
  
  $('#students_edit').on('change', function() {
    const id = $(this).val();
    const subj = $('#edit_subject');
    const curr = subj.data('current-subject');
    
    if (id && studentTutorials[id]) {
      const tuts = studentTutorials[id];
      subj.empty().append('<option value="">Select...</option>');
      
      if (tuts.length > 0) {
        tuts.forEach(t => {
          const sel = (t === curr) ? 'selected' : '';
          subj.append(`<option value="${t}" ${sel}>${t}</option>`);
        });
      }
    }
  });
});

function openEditModal(id, subject, date, start, end, student, tutor) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_schedule_date').value = date;
  document.getElementById('edit_start_time').value = start;
  document.getElementById('edit_end_time').value = end;
  
  $('#edit_subject').data('current-subject', subject);
  $('#students_edit').val(student).trigger('change');
  $('#tutor_edit').val(tutor).trigger('change');
  
  setTimeout(() => document.getElementById('edit_subject').value = subject, 150);
  
  new bootstrap.Modal(document.getElementById('editClassModal')).show();
}

function deleteSchedule(id) {
  if (confirm("Delete this tutorial session?")) {
    window.location.href = "delete_schedule.php?id=" + id;
  }
}
</script>

</body>
</html>