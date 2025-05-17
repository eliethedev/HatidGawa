<?php
// Connect to the database
require_once('db.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);

    // Validate that all fields are filled
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($address)) {
        die("All fields are required.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Validate phone format (basic check)
    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        die("Invalid phone number format.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($check_email === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        die("Email is already registered.");
    }
    $check_email->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- Barangay/Valid ID Upload & Validation ---
    if (!isset($_FILES['barangay_id']) || $_FILES['barangay_id']['error'] !== UPLOAD_ERR_OK) {
        die("Barangay/Valid ID is required and must be uploaded successfully.");
    }
    if ($_FILES['barangay_id']['size'] > 5 * 1024 * 1024) {
        die("Barangay/Valid ID file is too large. Maximum allowed size is 5MB.");
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['barangay_id']['tmp_name']);
    finfo_close($finfo);
    $allowed_mimes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'
    ];
    if (!in_array($mime, $allowed_mimes)) {
        die("Invalid file type for Barangay/Valid ID. Only JPG, PNG, GIF, WEBP, or PDF allowed.");
    }
    if (filesize($_FILES['barangay_id']['tmp_name']) < 10 * 1024) {
        die("Barangay/Valid ID file is too small or invalid.");
    }
    $ext = pathinfo($_FILES['barangay_id']['name'], PATHINFO_EXTENSION);
    $target_dir = '../uploads/barangay_ids/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $filename = uniqid('barangay_id_') . '.' . $ext;
    $target_file = $target_dir . $filename;
    if (!move_uploaded_file($_FILES['barangay_id']['tmp_name'], $target_file)) {
        die("Failed to upload Barangay/Valid ID. Please try again.");
    }
    $barangay_id_path = 'uploads/barangay_ids/' . $filename; // Save relative path

    // Insert user data into the database
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, address, barangay_id_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $phone, $address, $barangay_id_path);

    if ($stmt->execute()) {
        echo "Registration successful!";
        // Optionally, start a session and log the user in
        session_start();
        $_SESSION['user_id'] = $conn->insert_id; // Store user ID in session
        $_SESSION['email'] = $email;
        
        // Redirect to the login page or user dashboard
        header("Location: ../login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
