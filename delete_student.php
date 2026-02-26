<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminstudents.php.php?error=Access denied");
    exit();
}

// Check if 'id' is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: adminstudents.php?error=Invalid student ID");
    exit();
}

$student_id = intval($_GET['id']); // Convert to integer for safety

// Prepare and execute delete statement
$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: adminstudents.php?success=Student deleted successfully");
    exit();
} else {
    $stmt->close();
    header("Location: adminstudents.php?error=Failed to delete student");
    exit();
}
?>
