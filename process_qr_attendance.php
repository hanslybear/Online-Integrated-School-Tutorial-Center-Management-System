<?php
session_start();
include "db_connect.php";

// Set JSON header
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$qr_code = isset($input['qr_code']) ? trim($input['qr_code']) : '';
$date = isset($input['date']) ? trim($input['date']) : date('Y-m-d');

if (empty($qr_code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR code']);
    exit();
}

// Find student by QR code
$stmt = $conn->prepare("SELECT id, student_name, email FROM users WHERE qr_code = ? AND status = 'approved'");
$stmt->bind_param("s", $qr_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Student not found or not approved']);
    exit();
}

$student = $result->fetch_assoc();
$student_id = $student['id'];
$student_name = $student['student_name'];

// Check if attendance already exists for this student today
$check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
$check_stmt->bind_param("is", $student_id, $date);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        'success' => false, 
        'message' => $student_name . ' has already been marked present today'
    ]);
    exit();
}

// Insert attendance record
$current_time = date('H:i:s');
$status = 'present';
$remarks = 'Scanned via QR Code';

$insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, time_in, remarks) VALUES (?, ?, ?, ?, ?)");
$insert_stmt->bind_param("issss", $student_id, $date, $status, $current_time, $remarks);

if ($insert_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'student_name' => $student_name,
        'student_id' => $student_id,
        'time_in' => $current_time
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save attendance record'
    ]);
}

$stmt->close();
$check_stmt->close();
$insert_stmt->close();
$conn->close();
?>