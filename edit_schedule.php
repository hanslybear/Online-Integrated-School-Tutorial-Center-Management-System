<?php
session_start();
include "db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: schedule.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM class_schedules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    header("Location: schedule.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Schedule</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/studentstyle.css">
 <link rel="icon" type="image/png" href="actslogo.png">

</head>
<body>

<h2>Edit Class Schedule</h2>

<form method="POST" action="update_schedule.php">
  <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">

  <label>Subject</label>
  <input type="text" name="subject" value="<?php echo $schedule['subject']; ?>" required>

  <label>Teacher</label>
  <input type="text" name="teacher" value="<?php echo $schedule['teacher']; ?>" required>

  <label>Room</label>
  <input type="text" name="room" value="<?php echo $schedule['room']; ?>" required>

  <label>Program</label>
  <input type="text" name="program" value="<?php echo $schedule['program']; ?>" required>

  <label>Year Level</label>
  <input type="text" name="year_level" value="<?php echo $schedule['year_level']; ?>" required>

  <label>Day</label>
  <select name="day">
    <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday'] as $d): ?>
      <option value="<?php echo $d; ?>" 
        <?php echo $schedule['day'] == $d ? 'selected' : ''; ?>>
        <?php echo $d; ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Start Time</label>
  <input type="time" name="start_time" value="<?php echo $schedule['start_time']; ?>" required>

  <label>End Time</label>
  <input type="time" name="end_time" value="<?php echo $schedule['end_time']; ?>" required>

  <button type="submit">Update Schedule</button>
</form>

</body>
</html>

