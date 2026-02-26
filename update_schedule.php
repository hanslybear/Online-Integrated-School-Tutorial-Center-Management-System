<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id = $_POST['id'];
    $student_id = $_POST['students_enrolled'];
    $tutor_id = $_POST['id'];
    $subject = $_POST['subject'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    // Validate required fields
    if (empty($id) || empty($student_id) || empty($tutor_id) || empty($subject) || empty($schedule_date) || empty($start_time) || empty($end_time)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: adminschedule.php");
        exit();
    }
    
    // Validate time
    if ($start_time >= $end_time) {
        $_SESSION['error'] = "End time must be after start time.";
        header("Location: adminschedule.php");
        exit();
    }
    
    // Update database using prepared statement
    $sql = "UPDATE class_schedules 
            SET subject = ?, 
                schedule_date = ?, 
                start_time = ?, 
                end_time = ?, 
                students_enrolled = ?, 
                tutor_id = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiii", $subject, $schedule_date, $start_time, $end_time, $student_id, $tutor_id, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tutorial session updated successfully!";
        header("Location: adminschedule.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating tutorial session: " . $conn->error;
        header("Location: adminschedule.php");
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header("Location: adminschedule.php");
    exit();
}
?>