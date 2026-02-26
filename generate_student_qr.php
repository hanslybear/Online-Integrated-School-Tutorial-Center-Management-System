<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Function to generate unique QR code
function generateUniqueQRCode($student_id, $student_name) {
    // Create a unique string combining student ID, name, and timestamp
    $unique_string = "ACTS-" . str_pad($student_id, 6, "0", STR_PAD_LEFT) . "-" . strtoupper(substr($student_name, 0, 3)) . "-" . time();
    return hash('sha256', $unique_string);
}

// Get all approved students without QR codes
$sql = "SELECT id, student_name FROM users WHERE status = 'approved' AND (qr_code IS NULL OR qr_code = '')";
$result = $conn->query($sql);

$updated_count = 0;

while ($student = $result->fetch_assoc()) {
    $student_id = $student['id'];
    $student_name = $student['student_name'];
    
    // Generate unique QR code
    $qr_code = generateUniqueQRCode($student_id, $student_name);
    
    // Update student record
    $update_stmt = $conn->prepare("UPDATE users SET qr_code = ? WHERE id = ?");
    $update_stmt->bind_param("si", $qr_code, $student_id);
    
    if ($update_stmt->execute()) {
        $updated_count++;
    }
}

header("Location: adminstudents.php?success=" . $updated_count . " QR codes generated successfully");
exit();
?>