<?php
session_start();
include "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: homepage.php");
    exit();
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            addTutor($conn);
            break;
        case 'edit':
            editTutor($conn);
            break;
        case 'delete':
            deleteTutor($conn);
            break;
        default:
            $_SESSION['tutor_message'] = 'Invalid action';
            $_SESSION['tutor_message_type'] = 'danger';
            header("Location: adminlistoftutor.php");
            exit();
    }
}

function addTutor($conn) {
    // Get form data
    $tutor_name = mysqli_real_escape_string($conn, $_POST['tutor_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $rating = floatval($_POST['rating']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $subjects = mysqli_real_escape_string($conn, $_POST['subjects']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $total_students = 0; // Default value
    
    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/tutors/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Check file size (5MB max)
            if ($_FILES['profile_image']['size'] <= 5 * 1024 * 1024) {
                $new_filename = 'tutor_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = $upload_path;
                }
            }
        }
    }
    
    // Insert into database
    $sql = "INSERT INTO tutors (tutor_name, email, phone, education, experience, rating, specialization, subjects, status, total_students, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssdsssss", $tutor_name, $email, $phone, $education, $experience, $rating, $specialization, $subjects, $status, $total_students, $profile_image);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['tutor_message'] = 'Tutor added successfully!';
        $_SESSION['tutor_message_type'] = 'success';
    } else {
        $_SESSION['tutor_message'] = 'Error adding tutor: ' . mysqli_error($conn);
        $_SESSION['tutor_message_type'] = 'danger';
    }
    
    mysqli_stmt_close($stmt);
    header("Location: adminlistoftutor.php");
    exit();
}

function editTutor($conn) {
    $tutor_id = intval($_POST['tutor_id']);
    $tutor_name = mysqli_real_escape_string($conn, $_POST['tutor_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $education = mysqli_real_escape_string($conn, $_POST['education']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    $rating = floatval($_POST['rating']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $subjects = mysqli_real_escape_string($conn, $_POST['subjects']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Handle profile image upload
    $profile_image_update = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/tutors/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['profile_image']['size'] <= 5 * 1024 * 1024) {
                $new_filename = 'tutor_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    // Delete old image
                    $old_image_query = "SELECT profile_image FROM tutors WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $old_image_query);
                    mysqli_stmt_bind_param($stmt, "i", $tutor_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    
                    if ($row && !empty($row['profile_image']) && file_exists($row['profile_image'])) {
                        unlink($row['profile_image']);
                    }
                    mysqli_stmt_close($stmt);
                    
                    $profile_image_update = ", profile_image = '$upload_path'";
                }
            }
        }
    }
    
    // Update database
    $sql = "UPDATE tutors SET 
            tutor_name = ?, 
            email = ?, 
            phone = ?, 
            education = ?, 
            experience = ?, 
            rating = ?, 
            specialization = ?, 
            subjects = ?, 
            status = ?
            $profile_image_update
            WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssdsssi", $tutor_name, $email, $phone, $education, $experience, $rating, $specialization, $subjects, $status, $tutor_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['tutor_message'] = 'Tutor updated successfully!';
        $_SESSION['tutor_message_type'] = 'success';
    } else {
        $_SESSION['tutor_message'] = 'Error updating tutor: ' . mysqli_error($conn);
        $_SESSION['tutor_message_type'] = 'danger';
    }
    
    mysqli_stmt_close($stmt);
    header("Location: adminlistoftutor.php");
    exit();
}

function deleteTutor($conn) {
    header('Content-Type: application/json');
    
    $tutor_id = intval($_POST['tutor_id']);
    
    // Get profile image path before deletion
    $image_query = "SELECT profile_image FROM tutors WHERE id = ?";
    $stmt = mysqli_prepare($conn, $image_query);
    mysqli_stmt_bind_param($stmt, "i", $tutor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Delete tutor from database
    $delete_sql = "DELETE FROM tutors WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $tutor_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete profile image if exists
        if ($row && !empty($row['profile_image']) && file_exists($row['profile_image'])) {
            unlink($row['profile_image']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Tutor deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting tutor: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    exit();
}
?>