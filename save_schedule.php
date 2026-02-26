<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $students_enrolled = $_POST['students_enrolled'];
    $tutor_id = $_POST['tutor_id'];
    $subject = $_POST['subject'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $sql = "INSERT INTO class_schedules (students_enrolled, tutor_id, subject, schedule_date, start_time, end_time) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $students_enrolled, $tutor_id, $subject, $schedule_date, $start_time, $end_time);
    
    if ($stmt->execute()) {
        header("Location: adminschedule.php?success=1");
    } else {
        header("Location: adminschedule.php?error=" . urlencode($stmt->error));
    }
    
    $stmt->close();
    $conn->close();
}
?>