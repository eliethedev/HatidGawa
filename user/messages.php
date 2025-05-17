<?php
session_start();
require_once('../auth/db.php');

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
// Fetch message count for the current user
$message_count = 0;
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT m.id) FROM messages m
    JOIN tasks t ON m.task_id = t.id
    LEFT JOIN task_applications ta ON t.id = ta.task_id
    WHERE t.contractor_id = ? OR ta.helper_id = ?
");
if (!$stmt) {
    die("Prepare failed for messages count: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($message_count);
$stmt->fetch();
$stmt->close();


// Fetch current user info for sidebar and profile details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch tasks where user is contractor or helper (for dropdown)
$tasks = [];
$stmt = $conn->prepare("
    SELECT t.id, t.title
    FROM tasks t
    WHERE t.contractor_id = ?
    UNION
    SELECT t.id, t.title
    FROM tasks t
    JOIN task_applications ta ON t.id = ta.task_id
    WHERE ta.helper_id = ?
");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
$stmt->close();

// Determine selected task
$selected_task_id = isset($_GET['task_id']) ? $_GET['task_id'] : (count($tasks) ? $tasks[0]['id'] : null);

// Fetch messages for selected task
$messages = [];
if ($selected_task_id) {
    $stmt = $conn->prepare("
        SELECT m.*, u.first_name, u.last_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.task_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param("s", $selected_task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && isset($_POST['task_id']) && isset($_POST['content'])) {
    $task_id = $_POST['task_id'];
    $content = trim($_POST['content']);
    if ($content !== '') {
        $stmt = $conn->prepare("INSERT INTO messages (task_id, sender_id, content, message_type, created_at) VALUES (?, ?, ?, 'chat', NOW())");
        $stmt->bind_param("sss", $task_id, $user_id, $content);
        $stmt->execute();
        $stmt->close();
        header("Location: messages.php?task_id=" . urlencode($task_id));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Messages | HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/gabay.css">
    <link rel="icon" href="assets/images/logo.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #1877f2;
            --message-bg-sent: #0084ff;
            --message-bg-received: #e4e6eb;
            --chat-header-height: 60px;
            --chat-footer-height: 80px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #EAE0D5;
        }
        
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        /* Main content area */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 0;
            background: #EAE0D5;
        }
        
        /* Chat container */
        .chat-container {
            max-width: 1200px;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            overflow: hidden;
            display: flex;
            background: #EAE0D5;
            height: calc(100vh - 40px);
        }
        
        /* Conversation list */
        .conversation-list {
            width: 350px;
            border-right: 1px solid #e4e6eb;
            overflow-y: auto;
            background-color: #EAE0D5;
        }
        
        .conversation-header {
            padding: 15px;
            border-bottom: 1px solid #e4e6eb;
            background-color: #f0f2f5;
        }
        
        .conversation-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e4e6eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .conversation-item:hover {
            background-color: #f5f5f5;
        }
        
        .conversation-item.active {
            background-color:rgb(242, 241, 241);
        }
        
        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }
        
        .conversation-info {
            flex: 1;
        }
        
        .conversation-name {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .conversation-preview {
            font-size: 14px;
            color: #65676b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time {
            font-size: 12px;
            color: #65676b;
        }
        
        /* Chat area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #e4e6eb;
            display: flex;
            align-items: center;
            background-color: #f0f2f5;
            height: var(--chat-header-height);
        }
        
        .chat-user {
            display: flex;
            align-items: center;
        }
        
        .chat-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .chat-user-info {
            flex: 1;
        }
        
        .chat-user-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .chat-user-status {
            font-size: 12px;
            color: #65676b;
        }
        
        .chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNlNWU2ZWEiIG9wYWNpdHk9IjAuNCI+PHBhdGggZD0iTTIwIDM4LjVBMi41IDIuNSAwIDAgMSAxNy41IDM2aC0xNUEyLjUgMi41IDAgMCAxIDAgMzMuNXYtMjhBMi41IDIuNSAwIDAgMSAyLjUgM2gxNUEyLjUgMi41IDAgMCAxIDIwIDUuNXYzM3pNMi41IDRBMi41IDIuNSAwIDAgMCAwIDYuNXYyOEEyLjUgMi41IDAgMCAwIDIuNSAzN2gxNUEyLjUgMi41IDAgMCAwIDIwIDM0LjV2LTI5QTIuNSAyLjUgMi41IDAgMCAwIDE3LjUgM2gtMTV6Ii8+PC9nPjwvZz48L3N2Zz4=');
            background-repeat: repeat;
        }
        
        .chat-footer {
            padding: 15px;
            border-top: 1px solid #e4e6eb;
            background-color: #f0f2f5;
            height: var(--chat-footer-height);
        }
        
        /* Message bubbles */
        .message-container {
            display: flex;
            margin-bottom: 15px;
            flex-direction: column;
        }
        
        .message-container.sent {
            align-items: flex-end;
        }
        
        .message-container.received {
            align-items: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            word-break: break-word;
            line-height: 1.4;
            position: relative;
        }
        
        .sent .message-bubble {
            background-color: var(--message-bg-sent);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .received .message-bubble {
            background-color: var(--message-bg-received);
            color: #050505;
            border-bottom-left-radius: 4px;
        }
        
        .message-meta {
            font-size: 0.75rem;
            color: #65676b;
            margin-bottom: 5px;
        }
        
        .sent .message-meta {
            text-align: right;
        }
        
        .received .message-meta {
            text-align: left;
        }
        
        /* Message input */
        .message-input-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            outline: none;
            resize: none;
        }
        
        .message-input:focus {
            border-color: var(--primary-color);
        }
        
        .send-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            
            .conversation-list {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e4e6eb;
                max-height: 300px;
            }
            
            .chat-area {
                height: 500px;
            }
        }
        
        /* User profile in chat header */
        .user-profile-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-profile-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #fff;
            min-width: 250px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1;
            padding: 15px;
        }
        
        .user-profile-dropdown:hover .user-profile-content {
            display: block;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .profile-info h4 {
            margin: 0 0 5px 0;
        }
        
        .profile-info p {
            margin: 0;
            color: #65676b;
            font-size: 14px;
        }
        
        .profile-details {
            border-top: 1px solid #e4e6eb;
            padding-top: 15px;
        }
        
        .profile-detail-item {
            margin-bottom: 10px;
        }
        
        .profile-detail-item label {
            display: block;
            font-size: 12px;
            color: #65676b;
            margin-bottom: 3px;
        }
        
        .profile-detail-item p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">
                <i class="fas fa-hands-helping"></i>
                <span>HatidGawa</span>
            </a>
            <button class="sidebar-close-btn d-md-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidebar-user">
            <div class="avatar">
                <img src="<?= !empty($user['profile_picture']) 
                    ? (strpos($user['profile_picture'], 'http') === 0 
                        ? htmlspecialchars($user['profile_picture']) 
                        : '../' . htmlspecialchars($user['profile_picture'])) 
                    : '../assets/images/default-avatar.png' ?>" alt="User">
            </div>
            <div class="user-info">
                <h5 id="username"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <p class="text-black">Community Member</p>
            </div>
        </div>
        
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="dashboard.php" class="sidebar-nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="tasks-user.php" class="sidebar-nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>Browse Tasks</span>
                </a>
            </li>
            <li class="sidebar-nav-item active">
                <a href="mytasks.php" class="sidebar-nav-link">
                    <i class="fas fa-clipboard-list"></i>
                    <span>My Tasks</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="profile.php" class="sidebar-nav-link">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <li class="sidebar-nav-item">
                    <a href="messages.php" class="sidebar-nav-link">
                        <i class="fas fa-comment-alt"></i>
                        <span>Messages</span>
                        <?php if ($message_count > 0): ?>
                            <span class="badge badge-primary"><?= $message_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <li class="sidebar-nav-item">
                <a href="earnings.php" class="sidebar-nav-link">
                    <i class="fas fa-wallet"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="sidebar-nav-item">
                <a href="#" class="sidebar-nav-link" id="sidebar-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-shield-alt text-success"></i>
                    <span class="text-sm">Verified Member</span>
                </div>
                <div class="rating">
                    <i class="fas fa-star text-warning"></i>
                    <span>4.8</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="chat-container">
            <!-- Conversation List -->
            <div class="conversation-list">
                <div class="conversation-header">
                    <h5 class="mb-0">Messages</h5>
                </div>
                <?php foreach ($tasks as $task): ?>
                    <a href="messages.php?task_id=<?= htmlspecialchars($task['id']) ?>" class="conversation-item <?= $selected_task_id == $task['id'] ? 'active' : '' ?>">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($task['title']) ?>&background=random" class="conversation-avatar">
                        <div class="conversation-info">
                            <div class="conversation-name"><?= htmlspecialchars($task['title']) ?></div>
                            <div class="conversation-preview">
                                <?php 
                                // Get last message for preview
                                $last_msg = '';
                                if ($selected_task_id == $task['id'] && !empty($messages)) {
                                    $last_msg = end($messages)['content'];
                                    if (strlen($last_msg) > 30) {
                                        $last_msg = substr($last_msg, 0, 30) . '...';
                                    }
                                }
                                echo htmlspecialchars($last_msg);
                                ?>
                            </div>
                        </div>
                        <?php if ($selected_task_id == $task['id'] && !empty($messages)): ?>
                            <div class="conversation-time">
                                <?= date('h:i A', strtotime(end($messages)['created_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Chat Area -->
            <div class="chat-area">
                <?php if ($selected_task_id): ?>
                    <?php 
                    // Get the other user's info (either contractor or helper)
                    $other_user = null;
                    $stmt = $conn->prepare("
                        SELECT u.id, u.first_name, u.last_name, u.profile_picture
                        FROM users u
                        JOIN (
                            SELECT contractor_id AS other_id FROM tasks WHERE id = ? AND contractor_id != ?
                            UNION
                            SELECT ta.helper_id AS other_id FROM task_applications ta WHERE ta.task_id = ? AND ta.helper_id != ?
                        ) x ON u.id = x.other_id
                        LIMIT 1
                    ");
                    $stmt->bind_param("ssss", $selected_task_id, $user_id, $selected_task_id, $user_id);
                    $stmt->execute();
                    $other_user = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    ?>
                    
                    <div class="chat-header">
                        <div class="user-profile-dropdown">
                            <div class="chat-user">
                                <img src="<?= $other_user && !empty($other_user['profile_picture']) 
                                    ? (strpos($other_user['profile_picture'], 'http') === 0 
                                        ? htmlspecialchars($other_user['profile_picture']) 
                                        : '../' . htmlspecialchars($other_user['profile_picture'])) 
                                    : '../assets/images/default-avatar.png' ?>" class="chat-user-avatar">
                                <div class="chat-user-info">
                                    <div class="chat-user-name">
                                        <?= $other_user ? htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']) : 'Unknown User' ?>
                                    </div>
                                    <div class="chat-user-status">Online</div>
                                </div>
                            </div>
                            
                            <?php if ($other_user): ?>
                                <div class="user-profile-content">
                                    <div class="profile-header">
                                        <img src="<?= !empty($other_user['profile_picture']) 
                                            ? (strpos($other_user['profile_picture'], 'http') === 0 
                                                ? htmlspecialchars($other_user['profile_picture']) 
                                                : '../' . htmlspecialchars($other_user['profile_picture'])) 
                                            : '../assets/images/default-avatar.png' ?>" class="profile-avatar">
                                        <div class="profile-info">
                                            <h4><?= htmlspecialchars($other_user['first_name'] . ' ' . $other_user['last_name']) ?></h4>
                                            <p>Community Member</p>
                                        </div>
                                    </div>
                                    <div class="profile-details">
                                        <div class="profile-detail-item">
                                            <label>Email</label>
                                            <p><?= htmlspecialchars($user['email'] ?? 'Not provided') ?></p>
                                        </div>
                                        <div class="profile-detail-item">
                                            <label>Phone</label>
                                            <p><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></p>
                                        </div>
                                        <div class="profile-detail-item">
                                            <label>Member Since</label>
                                            <p><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="chat-body" id="chatBody">
                        <?php if (empty($messages)): ?>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="text-center text-muted">
                                    <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                    <p>No messages yet for this task</p>
                                    <p>Start the conversation now!</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-container <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                    <div class="message-meta">
                                        <?= $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?>
                                        <span class="ms-2"><?= htmlspecialchars(date('M d, Y h:i A', strtotime($msg['created_at']))) ?></span>
                                    </div>
                                    <div class="message-bubble">
                                        <?= nl2br(htmlspecialchars($msg['content'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-footer">
                        <form method="post" class="message-input-container">
                            <input type="hidden" name="task_id" value="<?= htmlspecialchars($selected_task_id) ?>">
                            <textarea name="content" class="message-input" placeholder="Type your message..." rows="1" required></textarea>
                            <button type="submit" name="send_message" class="send-button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-center align-items-center h-100 w-100">
                        <div class="text-center text-muted">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <h4>No task selected</h4>
                            <p>Select a task from the list to view messages</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll to bottom of chat
        window.onload = function() {
            var chatBody = document.getElementById('chatBody');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
            
            // Auto-resize textarea
            const textarea = document.querySelector('.message-input');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        }
        
        // Keep scroll at bottom when new messages are added
        function observeChat() {
            const chatBody = document.getElementById('chatBody');
            if (chatBody) {
                const observer = new MutationObserver(function() {
                    chatBody.scrollTop = chatBody.scrollHeight;
                });
                
                observer.observe(chatBody, {
                    childList: true,
                    subtree: true
                }); 
            }
        }
        
        document.addEventListener('DOMContentLoaded', observeChat);
    </script>
</body>
</html>