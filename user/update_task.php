<?php
require_once "../auth/db.php";

// Get POST data
$task_id = isset($_POST['task_id']) ? $_POST['task_id'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$pay = isset($_POST['pay']) && $_POST['pay'] !== '' ? floatval($_POST['pay']) : null;
$task_type = isset($_POST['task_type']) ? $_POST['task_type'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : null;
$safezone_id = isset($_POST['safezone_id']) ? $_POST['safezone_id'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : 'pending';

// Validate input
if (empty($task_id) || empty($title) || empty($category) || empty($task_type)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Prepare the update statement
$stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, category = ?, pay = ?, task_type = ?, address = ?, safezone_id = ?, status = ? WHERE id = ?");
$stmt->bind_param(
    "sssssssss",
    $title,
    $description,
    $category,
    $pay,
    $task_type,
    $address,
    $safezone_id,
    $status,
    $task_id
);

if ($stmt->execute()) {
    header("Location: mytasks.php");
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update task.']);
}

$stmt->close();
$conn->close();
?>