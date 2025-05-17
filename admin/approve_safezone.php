<?php
require_once('../auth/db.php');
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE safezones SET is_approved=1 WHERE id=?");
    $stmt->bind_param("s", $_GET['id']);
    $stmt->execute();
    $stmt->close();
}
header('Location: dashboard.php');
exit(); 