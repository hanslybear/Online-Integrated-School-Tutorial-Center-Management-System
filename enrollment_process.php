<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data - matching database columns exactly
    $student_name = trim($_POST['student_name']);
    $parents_name = trim($_POST['parents_name']);
    $address = trim($_POST['address']);
    $birthday = trim($_POST['birthday']);
    $gender = trim($_POST['gender']);
    $contact = trim($_POST['contact']);
    $parents_contact = trim($_POST['parents_contact']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists
        $_SESSION['error_message'] = "This email is already registered. Please use a different email or sign in.";
        
        // Save form data to session for pre-fill
        $_SESSION['student_name'] = $student_name;
        $_SESSION['parents_name'] = $parents_name;
        $_SESSION['address'] = $address;
        $_SESSION['birthday'] = $birthday;
        $_SESSION['gender'] = $gender;
        $_SESSION['contact'] = $contact;
        $_SESSION['parents_contact'] = $parents_contact;
        $_SESSION['email'] = $email;
        
        header("Location: enrollment.php");
        exit();
    }
    
    // Insert new student - matching exact database columns
    // Columns from database: student_name, parents_name, address, birthday, gender, contact, parents_contact, email, password
    // QR code will be generated later, status defaults to 'pending'
    $sql = "INSERT INTO users (
                student_name, 
                parents_name,
                address,
                birthday,
                gender,
                contact,
                parents_contact,
                email, 
                password,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssss",
        $student_name,
        $parents_name,
        $address,
        $birthday,
        $gender,
        $contact,
        $parents_contact,
        $email,
        $hashed_password
    );
    
    if ($stmt->execute()) {
        // Clear any saved form data from session
        unset($_SESSION['student_name']);
        unset($_SESSION['parents_name']);
        unset($_SESSION['address']);
        unset($_SESSION['birthday']);
        unset($_SESSION['gender']);
        unset($_SESSION['contact']);
        unset($_SESSION['parents_contact']);
        unset($_SESSION['email']);
        unset($_SESSION['error_message']);
        
        // Redirect to completion page
        header("Location: enrollment.php?step=complete");
        exit();
    } else {
        // Error during insertion
        $_SESSION['error_message'] = "There was an error processing your enrollment. Please try again.";
        
        // Save form data to session for pre-fill
        $_SESSION['student_name'] = $student_name;
        $_SESSION['parents_name'] = $parents_name;
        $_SESSION['address'] = $address;
        $_SESSION['birthday'] = $birthday;
        $_SESSION['gender'] = $gender;
        $_SESSION['contact'] = $contact;
        $_SESSION['parents_contact'] = $parents_contact;
        $_SESSION['email'] = $email;
        
        header("Location: enrollment.php");
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    // If accessed directly without POST
    header("Location: enrollment.php");
    exit();
}
?>