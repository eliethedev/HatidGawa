<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form inputs
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    // Prepare SQL statement to check if email exists in the database
    $stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Debugging error
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $first_name, $last_name, $hashed_password);
    $stmt->fetch();

    // If email exists and password matches
    if ($hashed_password && password_verify($password, $hashed_password)) {
        // Set session variables for the user
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user'] = [
            'id' => $id,
            'full_name' => $first_name . ' ' . $last_name,
            'email' => $email
        ];

        // Redirect to the dashboard
        header("Location: ../user/dashboard.php");
        exit();
    } else {
        // Invalid login
        echo "<p class='error'>Invalid email or password.</p>";
    }
}
?>

