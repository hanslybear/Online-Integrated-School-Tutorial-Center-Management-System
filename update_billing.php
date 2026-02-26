<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $billing_id = isset($_POST['billing_id']) ? intval($_POST['billing_id']) : 0;
    $billing_type = isset($_POST['billing_type']) ? trim($_POST['billing_type']) : '';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    // Validate required fields
    if ($billing_id <= 0) {
        header("Location: adminbilling.php?error=Invalid billing ID");
        exit();
    }
    
    if (empty($billing_type)) {
        header("Location: adminbilling.php?error=Billing type is required");
        exit();
    }
    
    if ($amount <= 0) {
        header("Location: adminbilling.php?error=Amount must be greater than zero");
        exit();
    }
    
    if (empty($due_date)) {
        header("Location: adminbilling.php?error=Due date is required");
        exit();
    }
    
    if (empty($status) || !in_array($status, ['pending', 'paid'])) {
        header("Location: adminbilling.php?error=Invalid payment status");
        exit();
    }
    
    // Check if billing record exists
    $check_sql = "SELECT id FROM billing WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $billing_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        header("Location: adminbilling.php?error=Billing record not found");
        exit();
    }
    
    // Update billing record
    $update_sql = "UPDATE billing 
                   SET billing_type = ?, 
                       amount = ?, 
                       description = ?, 
                       due_date = ?, 
                       status = ?
                   WHERE id = ?";
    
    $stmt = $conn->prepare($update_sql);
    
    if (!$stmt) {
        header("Location: adminbilling.php?error=Database error: " . $conn->error);
        exit();
    }
    
    $stmt->bind_param("sdsssi", $billing_type, $amount, $description, $due_date, $status, $billing_id);
    
    if ($stmt->execute()) {
        header("Location: adminbilling.php?success=Billing record updated successfully");
        exit();
    } else {
        header("Location: adminbilling.php?error=Failed to update billing record: " . $stmt->error);
        exit();
    }
} else {
    header("Location: adminbilling.php");
    exit();
}
?>