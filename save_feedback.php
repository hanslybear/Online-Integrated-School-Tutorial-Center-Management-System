<?php
session_start();
include "db_connect.php";

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Get form data
$student_id = $_POST['student_id'];
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$category = mysqli_real_escape_string($conn, $_POST['category']);
$subject = mysqli_real_escape_string($conn, $_POST['subject']);
$message = mysqli_real_escape_string($conn, $_POST['message']);

// Set status to 'reviewed' directly (no approval needed)
$status = 'reviewed';

// Insert feedback into database
$sql = "INSERT INTO studentfeedback (student_id, rating, category, subject, message, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissss", $student_id, $rating, $category, $subject, $message, $status);

if ($stmt->execute()) {
    $_SESSION['feedback_success'] = "Thank you for your feedback! Your feedback has been submitted successfully.";
    header("Location: student_feedback.php");
} else {
    $_SESSION['feedback_error'] = "Failed to submit feedback. Please try again.";
    header("Location: student_feedback.php");
}

$stmt->close();
$conn->close();
exit();
?>