<?php
session_start();
include "db_connect.php";

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get tutor_id from request
$tutor_id = isset($_GET['tutor_id']) ? intval($_GET['tutor_id']) : 0;

if ($tutor_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid tutor ID']);
    exit();
}

// Query to get all unique students assigned to this tutor through class schedules
$query = "SELECT DISTINCT 
            u.id,
            u.student_name,
            u.email,
            u.subject_tutorials,
            u.computer_tutorials,
            u.sessions_per_week,
            u.schedule_preference
          FROM users u
          INNER JOIN class_schedules cs ON u.id = cs.students_enrolled
          WHERE cs.tutor_id = ?
          AND u.status = 'approved'
          ORDER BY u.student_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'id' => $row['id'],
        'student_name' => $row['student_name'],
        'email' => $row['email'],
        'subject_tutorials' => $row['subject_tutorials'],
        'computer_tutorials' => $row['computer_tutorials'],
        'sessions_per_week' => $row['sessions_per_week'],
        'schedule_preference' => $row['schedule_preference']
    ];
}

echo json_encode([
    'success' => true,
    'students' => $students,
    'count' => count($students)
]);

$stmt->close();
$conn->close();
?>