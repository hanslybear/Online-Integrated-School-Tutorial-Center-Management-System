<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Class Schedule | ACTS Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="adminstyle.css">
</head>
<body>

<div class="main">
  <div class="container mt-5">
    <div class="card p-4">
      <h3><i class="fas fa-plus-circle"></i> Add Class Schedule</h3>
      <hr>

      <form action="save_schedule.php" method="POST">
        <div class="row">
          
          <div class="col-md-6 mb-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Day</label>
            <select name="day" class="form-control" required>
              <option value="">Select Day</option>
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Teacher</label>
            <input type="text" name="teacher" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Room</label>
            <input type="text" name="room" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Program</label>
            <input type="text" name="program" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label>Year Level</label>
            <input type="text" name="year_level" class="form-control" required>
          </div>

        </div>

        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i> Save Schedule
        </button>

        <a href="adminschedule.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </form>

    </div>
  </div>
</div>

</body>
</html>
