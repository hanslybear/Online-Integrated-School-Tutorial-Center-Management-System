<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM billing WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: billing.php?success=Billing deleted successfully");
    } else {
        header("Location: billing.php?error=Failed to delete billing");
    }
    exit();
}
?>