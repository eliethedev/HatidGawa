<?php
require_once('../auth/db.php');
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET verification_status = 'banned' WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
}
header('Location: dashboard.php');
exit(); 