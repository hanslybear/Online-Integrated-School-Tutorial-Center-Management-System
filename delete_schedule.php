<?php
session_start();
include "db_connect.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete using prepared statement
    $sql = "DELETE FROM class_schedules WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Tutorial session deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting tutorial session: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: adminschedule.php");
    exit();
    
} else {
    header("Location: adminschedule.php");
    exit();
}
?>