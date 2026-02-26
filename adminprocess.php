<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM admin_accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        // Check if password matches
        if (password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"] = $admin["id_admin"];
            $_SESSION['admin_username'] = $admin["username"];
            header("Location: admindashboard.php");
            exit;
        } else {
            header("Location: index.php?modal=admin&error=Incorrect password");
            exit;
        }
    } else {
        header("Location: index.php?modal=admin&error=No admin account found");
        exit;
    }
}
?>
