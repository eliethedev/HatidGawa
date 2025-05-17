<?php
// task_details.php

require_once "../auth/db.php";
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if 'id' is set
if (isset($_GET['id'])) {
    $task_id = intval($_GET['id']); // Secure the ID to be an integer

    // Query to get task details
    $query = "SELECT * FROM tasks WHERE id = $task_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $task = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger text-center mt-5'>Task not found.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-warning text-center mt-5'>No task ID specified.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Task Details</h3>
        </div>
        <div class="card-body">
            <p><strong>Title:</strong> <?= htmlspecialchars($task['title']) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($task['description'])) ?></p>
            <p><strong>Status:</strong> <span class="badge bg-success"><?= htmlspecialchars($task['status']) ?></span></p>
            <p><strong>Created At:</strong> <?= htmlspecialchars($task['created_at']) ?></p>

            <a href="tasks_list.php" class="btn btn-secondary mt-3">Back to Task List</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
