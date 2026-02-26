<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No tutor ID provided']);
    exit();
}

$tutorId = intval($_GET['id']);

// Fetch tutor details with actual student count
$query = "SELECT t.*, 
          COUNT(DISTINCT cs.students_enrolled) as actual_student_count
          FROM tutors t
          LEFT JOIN class_schedules cs ON t.id = cs.tutor_id
          WHERE t.id = ?
          GROUP BY t.id";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $tutorId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'tutor' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Tutor not found'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>