<?php
require_once('../auth/db.php');
session_start();

$task_id = $_GET['task_id'];
$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT m.*, u.first_name, u.last_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.task_id = ? ORDER BY m.created_at ASC");
$stmt->bind_param("s", $task_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'content' => $row['content'],
        'created_at' => $row['created_at'],
        'sender_name' => $row['first_name'] . ' ' . $row['last_name'],
        'sender_id' => $row['sender_id']
    ];
}
header('Content-Type: application/json');
echo json_encode($messages); 