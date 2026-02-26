<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: homepage.php?error=Please log in");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get form data
$subject_tutorials = isset($_POST['subject_tutorials']) ? trim($_POST['subject_tutorials']) : '';
$computer_tutorials = isset($_POST['computer_tutorials']) ? trim($_POST['computer_tutorials']) : '';
$sessions_per_week = isset($_POST['sessions_per_week']) ? trim($_POST['sessions_per_week']) : '';
$schedule_preference = isset($_POST['schedule_preference']) ? trim($_POST['schedule_preference']) : '';

// Validate that at least one tutorial is selected
if (empty($subject_tutorials) && empty($computer_tutorials)) {
    header("Location: studentdashboard.php?error=Please select at least one tutorial service");
    exit();
}

// Validate required fields
if (empty($sessions_per_week) || empty($schedule_preference)) {
    header("Location: studentdashboard.php?error=Please complete all required fields");
    exit();
}

// Update the user's tutorial enrollment
$sql = "UPDATE users 
        SET subject_tutorials = ?, 
            computer_tutorials = ?, 
            sessions_per_week = ?, 
            schedule_preference = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $subject_tutorials, $computer_tutorials, $sessions_per_week, $schedule_preference, $student_id);

if ($stmt->execute()) {
    header("Location: studentdashboard.php?success=Tutorial enrollment successful!");
    exit();
} else {
    header("Location: studentdashboard.php?error=Enrollment failed. Please try again.");
    exit();
}

$stmt->close();
$conn->close();
?>