<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $billing_type = $_POST['billing_type'];
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];

    $sql = "INSERT INTO billing (student_id, billing_type, amount, due_date, description, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $student_id, $billing_type, $amount, $due_date, $description);
    
    if ($stmt->execute()) {
        header("Location: billing.php?success=Billing added successfully");
    } else {
        header("Location: billing.php?error=Failed to add billing");
    }
    exit();
}
?>