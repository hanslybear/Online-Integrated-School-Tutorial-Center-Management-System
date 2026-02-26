<?php
session_start();
include "db_connect.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: tutor_login.php?error=Please fill in all fields");
        exit();
    }
    
    // Query to check tutor credentials
    $sql = "SELECT * FROM tutors WHERE email = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $tutor = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $tutor['password'])) {
            // Set session variables
            $_SESSION['tutor_id'] = $tutor['id'];
            $_SESSION['tutor_name'] = $tutor['tutor_name'];
            $_SESSION['tutor_email'] = $tutor['email'];
            $_SESSION['user_type'] = 'tutor';
            
            // Update last login
            $update_sql = "UPDATE tutors SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $tutor['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect to tutor dashboard
            header("Location: tutordashboard.php");
            exit();
        } else {
            header("Location: tutor_login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: tutor_login.php?error=Tutor account not found or inactive");
        exit();
    }
    
    $stmt->close();
} else {
    header("Location: tutor_login.php");
    exit();
}

$conn->close();
?>