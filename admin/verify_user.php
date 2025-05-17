<?php
require_once('../auth/db.php');
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE users SET verified=1, verification_status='approved' WHERE id=?");
    $stmt->bind_param("s", $_GET['id']);
    $stmt->execute();
    $stmt->close();
}
header('Location: dashboard.php');
exit(); 