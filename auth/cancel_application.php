<?php
session_start();
require_once('../config/database.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM task_applications WHERE task_id = ? AND helper_id = ?");
    $stmt->bind_param("ss", $task_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Application cancelled successfully!";
    } else {
        $_SESSION['error_message'] = "Error cancelling application: " . $stmt->error;
    }
    $stmt->close();
}

header('Location: ../user/mytasks.php');
exit();
?>