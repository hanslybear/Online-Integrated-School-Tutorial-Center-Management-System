<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_id = intval($_POST['feedback_id']);
    
    $sql = "DELETE FROM studentfeedback WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $feedback_id);
    
    if ($stmt->execute()) {
        header("Location: adminfeedback.php?success=Feedback deleted successfully");
    } else {
        header("Location: adminfeedback.php?error=Failed to delete feedback");
    }
} else {
    header("Location: adminfeedback.php");
}

exit();
?>