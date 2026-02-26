<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $billing_type = $_POST['billing_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    
    // Validate required fields
    if (empty($student_id) || empty($billing_type) || empty($amount) || empty($due_date) || empty($status)) {
        header("Location: adminbilling.php?error=All required fields must be filled");
        exit();
    }
    
    // Validate amount is positive
    if ($amount <= 0) {
        header("Location: adminbilling.php?error=Amount must be greater than zero");
        exit();
    }
    
    // Validate student exists
    $check_student = "SELECT id FROM users WHERE id = ? AND status = 'approved'";
    $check_stmt = $conn->prepare($check_student);
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        header("Location: adminbilling.php?error=Invalid student selected");
        exit();
    }
    
    // Insert billing record
    $insert_sql = "INSERT INTO billing (student_id, billing_type, amount, description, due_date, status, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("isdsss", $student_id, $billing_type, $amount, $description, $due_date, $status);
    
    if ($stmt->execute()) {
        header("Location: adminbilling.php?success=Billing record added successfully");
        exit();
    } else {
        header("Location: adminbilling.php?error=Failed to add billing record: " . $conn->error);
        exit();
    }
} else {
    header("Location: adminbilling.php");
    exit();
}
?>