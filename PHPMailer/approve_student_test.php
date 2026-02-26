<?php
// =======================
// CONFIG
// =======================
include "db_connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If using Composer
// require 'vendor/autoload.php';

// If using manual PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// =======================
// GET STUDENT ID (TEST)
// =======================
if (!isset($_GET['id'])) {
    die("❌ No student ID provided.");
}

$student_id = (int) $_GET['id'];

// =======================
// FETCH STUDENT
// =======================
$sql = "SELECT id, student_name, email, status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Student not found.");
}

$student = $result->fetch_assoc();

if ($student['status'] === 'approved') {
    die("⚠️ Student already approved.");
}

// =======================
// APPROVE STUDENT
// =======================
$update = "UPDATE users SET status = 'approved' WHERE id = ?";
$update_stmt = $conn->prepare($update);
$update_stmt->bind_param("i", $student_id);

if (!$update_stmt->execute()) {
    die("❌ Failed to approve student.");
}

// =======================
// SEND EMAIL
// =======================
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'yourgmail@gmail.com';       // YOUR GMAIL
    $mail->Password   = 'YOUR_APP_PASSWORD';         // GMAIL APP PASSWORD
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('yourgmail@gmail.com', 'ACTS Learning Center');
    $mail->addAddress($student['email'], $student['student_name']);

    $mail->isHTML(true);
    $mail->Subject = 'Account Approved';
    $mail->Body = "
        <h2>Hi {$student['student_name']},</h2>
        <p>Your account has been <strong>approved</strong>.</p>
        <p>You may now log in to your student dashboard.</p>
        <a href='http://localhost/yourproject/index.php'>Login Now</a>
    ";

    $mail->AltBody = "Your account has been approved. You can now log in.";

    $mail->send();

    echo "✅ Student approved AND email sent successfully!";

} catch (Exception $e) {
    echo "⚠️ Student approved, but email failed: " . $mail->ErrorInfo;
}
