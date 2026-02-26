<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

$date = isset($_POST['date']) ? trim($_POST['date']) : date('Y-m-d');
$students = isset($_POST['students']) ? $_POST['students'] : [];

$success_count = 0;
$error_count = 0;

foreach ($students as $student_id) {
    $status_field = 'status_' . $student_id;
    $status = isset($_POST[$status_field]) ? trim($_POST[$status_field]) : '';
    
    // Skip if no status selected
    if (empty($status)) {
        continue;
    }
    
    // Check if attendance already exists
    $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
    $check_stmt->bind_param("is", $student_id, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_stmt = $conn->prepare("UPDATE attendance SET status = ?, time_in = ? WHERE student_id = ? AND date = ?");
        $current_time = date('H:i:s');
        $update_stmt->bind_param("ssis", $status, $current_time, $student_id, $date);
        
        if ($update_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }
    } else {
        // Insert new record
        $current_time = date('H:i:s');
        $remarks = 'Marked manually by admin';
        
        $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, time_in, remarks) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("issss", $student_id, $date, $status, $current_time, $remarks);
        
        if ($insert_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
}

if ($success_count > 0) {
    header("Location: adminattendance.php?success=" . $success_count . " attendance records saved successfully");
} else {
    header("Location: adminattendance.php?error=No attendance records were saved");
}

exit();
?>