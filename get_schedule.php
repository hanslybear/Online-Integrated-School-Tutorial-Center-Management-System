<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT * FROM class_schedules WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Schedule not found']);
    }
}
?>