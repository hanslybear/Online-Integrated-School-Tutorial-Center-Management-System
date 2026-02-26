<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Get user
$sql = "SELECT id, student_name, password, status FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php?error=Invalid email or password");
    exit();
}

$user = $result->fetch_assoc();

// ❌ If account is not approved
if ($user['status'] !== 'approved') {
    header("Location: index.php?error=Account pending approval");
    exit();
}

// 🔐 Verify password
if (!password_verify($password, $user['password'])) {
    header("Location: index.php?error=Invalid email or password");
    exit();
}

// ✅ CREATE SESSION
$_SESSION['student_id'] = $user['id'];
$_SESSION['student_name'] = $user['student_name'];

// 🚀 Redirect to dashboard
header("Location: studentdashboard.php");
exit();
?>
