<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_id = intval($_POST['feedback_id']);
    $status = $_POST['status'];
    
    // Validate status
    if (!in_array($status, ['pending', 'reviewed'])) {
        header("Location: adminfeedback.php?error=Invalid status");
        exit();
    }
    
    $sql = "UPDATE studentfeedback SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $feedback_id);
    
    if ($stmt->execute()) {
        header("Location: adminfeedback.php?success=Feedback status updated successfully");
    } else {
        header("Location: adminfeedback.php?error=Failed to update feedback status");
    }
} else {
    header("Location: adminfeedback.php");
}

exit();
?>