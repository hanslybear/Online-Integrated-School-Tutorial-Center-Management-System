<?php
session_start();
include "db_connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid student ID.";
    header("Location: adminpending_students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Fetch student info
$stmt = $conn->prepare("SELECT student_name, email FROM users WHERE id = ? AND status = 'pending'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Student not found or already approved.";
    header("Location: adminpending_students.php");
    exit();
}

$student = $result->fetch_assoc();
$student_name = $student['student_name'];
$student_email = $student['email'];

// Update student status to approved
$update = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
$update->bind_param("i", $student_id);

if ($update->execute()) {
    // Send email notification using PHPMailer
    $mail = new PHPMailer(true);
try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hanslybear51703@gmail.com'; // your SMTP email
        $mail->Password   = 'wsptnttfzaomrrok';  // SMTP password or App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('hanslybear51703@gmail.com', 'ACTS Learning Center');
        $mail->addAddress($student_email, $student_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Student Account Has Been Approved!';
        $mail->Body    = "
           <h3>Hello $student_name,</h3>
<p>We are pleased to inform you that your student account at <strong>ACTS Learning & Development Center</strong> has been <strong>successfully reviewed and approved</strong>.</p>
<p>You may now access your student dashboard and begin your learning journey with us by clicking the link below:</p>
<p><a href='http://yourdomain.com/index.php'>Student Dashboard</a></p>
<p>Welcome to ACTS Learning & Development Center. We look forward to supporting you throughout your learning experience.</p>
<p>Best regards,<br>ACTS Learning & Development Center</p>

        ";

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Student approved, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: adminpending_students.php");
        exit();
    }    

    $_SESSION['success_message'] = "$student_name has been approved and notified via email.";
    header("Location: adminpending_students.php");
    exit();
} else {
    $_SESSION['error_message'] = "Failed to approve $student_name. Please try again.";
    header("Location: adminpending_students.php");
    exit();
}
