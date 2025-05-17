<?php
session_start();
require 'db.php'; // Your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['task_title']);
    $category = mysqli_real_escape_string($conn, $_POST['task_category']);
    $price = (int)$_POST['task_price'];
    $description = mysqli_real_escape_string($conn, $_POST['task_description']);
    $requirements = mysqli_real_escape_string($conn, $_POST['task_requirements']);
    $location = mysqli_real_escape_string($conn, $_POST['task_location']);
    $due_date = mysqli_real_escape_string($conn, $_POST['task_due_date']);
    $estimated_time = mysqli_real_escape_string($conn, $_POST['task_estimated_time']);
    $poster_id = $_SESSION['user_id']; // Assuming you store logged-in user ID in session

    $sql = "INSERT INTO tasks (title, category, price, description, requirements, location, due_date, estimated_time, poster_id, status, date_posted) 
            VALUES ('$title', '$category', '$price', '$description', '$requirements', '$location', '$due_date', '$estimated_time', '$poster_id', 'Open', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true, "message" => "Task posted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error posting task."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
