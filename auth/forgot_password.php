<?php
require_once('db.php'); // path to your db connection
session_start();

// 1. Check if token is provided
if (!isset($_GET['token'])) {
    die('Invalid link.');
}

$token = $_GET['token'];

// 2. Look up token
$stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$reset = $result->fetch_assoc();
$stmt->close();

if (!$reset) {
    die('Invalid or expired link.');
}

// 3. Check if token is expired
if (strtotime($reset['expires_at']) < time()) {
    die('Token has expired.');
}

// 4. If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'], $_POST['confirm_password'])) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        die('Passwords do not match.');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in users table
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $reset['email']);
    $stmt->execute();
    $stmt->close();

    // Delete the password reset token
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $reset['email']);
    $stmt->execute();
    $stmt->close();

    header("Location: ../login.php?reset=success");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<!-- [rest of your HTML code from paste.txt] -->