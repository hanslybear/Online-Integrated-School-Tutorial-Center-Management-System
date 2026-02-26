<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location:/index.php");
    exit();
}

include "db_connect.php";

$id = intval($_GET['id']);

$conn->query("UPDATE users SET status='rejected' WHERE id=$id");

header("Location: pending_students.php");
exit();
