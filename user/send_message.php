<?php
require_once('../auth/db.php');
session_start();

$task_id = $_POST['task_id'];
$content = trim($_POST['content']);
$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("INSERT INTO messages (task_id, sender_id, content, message_type, created_at) VALUES (?, ?, ?, 'chat', NOW())");
$stmt->bind_param("sss", $task_id, $user_id, $content);
$success = $stmt->execute();
$stmt->close();

header('Content-Type: application/json');
echo json_encode(['success' => $success]); 