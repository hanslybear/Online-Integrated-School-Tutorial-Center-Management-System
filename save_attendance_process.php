<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $students = $_POST['students'];
    
    $success_count = 0;
    
    foreach ($students as $student_id) {
        $status = isset($_POST['status_' . $student_id]) ? $_POST['status_' . $student_id] : '';
        
        if (!empty($status)) {
            // Set time_in for present and late students
            $time_in = ($status === 'present' || $status === 'late') ? date('H:i:s') : null;
            
            $sql = "INSERT INTO attendance (student_id, date, status, time_in) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE status = ?, time_in = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $student_id, $date, $status, $time_in, $status, $time_in);
            
            if ($stmt->execute()) {
                $success_count++;
            }
        }
    }
    
    if ($success_count > 0) {
        header("Location: adminattendance.php?success=Attendance marked for $success_count students");
    } else {
        header("Location: adminattendance.php?error=No attendance was marked");
    }
    exit();
}
?>