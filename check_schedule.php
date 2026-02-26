<?php
session_start();
include "db_connect.php";

// Temporarily set student_id for testing
$student_id = 1; // Change to your actual student ID

echo "<h3>Schedule Diagnostic</h3>";

// 1. Check student exists
$student_sql = "SELECT id, student_name FROM users WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
    echo "<p>✓ Student found: " . $student['student_name'] . " (ID: " . $student['id'] . ")</p>";
} else {
    echo "<p>✗ Student not found or not approved</p>";
}

// 2. Check schedules for this student
$schedule_sql = "SELECT COUNT(*) as total FROM class_schedules WHERE students_enrolled = ?";
$stmt2 = $conn->prepare($schedule_sql);
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$schedule_result = $stmt2->get_result();
$schedule_count = $schedule_result->fetch_assoc()['total'];

echo "<p>Schedules found in database: " . $schedule_count . "</p>";

// 3. Show all schedules
if ($schedule_count > 0) {
    $detail_sql = "SELECT * FROM class_schedules WHERE students_enrolled = ? ORDER BY schedule_date";
    $stmt3 = $conn->prepare($detail_sql);
    $stmt3->bind_param("i", $student_id);
    $stmt3->execute();
    $details = $stmt3->get_result();
    
    echo "<table border='1'><tr><th>ID</th><th>Subject</th><th>Date</th><th>Time</th><th>Student ID</th></tr>";
    while ($row = $details->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['subject'] . "</td>";
        echo "<td>" . $row['schedule_date'] . "</td>";
        echo "<td>" . $row['start_time'] . " - " . $row['end_time'] . "</td>";
        echo "<td>" . $row['students_enrolled'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Check database structure
echo "<h4>Table Structure:</h4>";
$structure_sql = "DESCRIBE class_schedules";
$structure_result = $conn->query($structure_sql);
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $structure_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>