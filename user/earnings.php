    <?php
    require_once('../auth/db.php');
    session_start();

    if (!isset($_SESSION['user']['id'])) {
        header('Location: ../login.php');
        exit();
    }
    $user_id = $_SESSION['user']['id'];

// Fetch user info for sidebar
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

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

// Fetch total balance (sum of pay from completed tasks where user is the accepted helper)
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(t.pay), 0) as total_earnings
    FROM tasks t
    JOIN task_applications ta ON t.id = ta.task_id
    WHERE ta.helper_id = ? 
    AND t.status = 'completed' 
    AND ta.status = 'accepted'
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_earned = $row['total_earnings'];
$stmt->close();

// Fetch earnings history with task info directly from tasks table
$stmt = $conn->prepare("
    SELECT 
        t.id,
        t.title,
        t.pay,
        t.status,
        t.created_at,
        ta.status as application_status
    FROM tasks t
    JOIN task_applications ta ON t.id = ta.task_id
    WHERE ta.helper_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$earnings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


   
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/gabay.css">
    <link rel="icon" href="assets/images/logo.svg">
    <style>
    .status-badge {
        display: inline-block;
        padding: 0.35em 0.75em;
        border-radius: 0.5em;
        font-size: 0.95em;
        font-weight: 500;
        color: #fff;
    }
    .status-badge[data-status="completed"] {
        background: #28a745;
    }
    .status-badge[data-status="pending"] {
        background: #ffc107;
        color: #212529;
    }
    .status-badge[data-status="cancelled"],
    .status-badge[data-status="rejected"] {
        background: #dc3545;
    }
    .status-badge[data-status="accepted"] {
        background: #007bff;
    }
    .status-badge[data-status="applied"] {
        background: #17a2b8;
    }
    .status-badge[data-status="secondary"] {
        background: #6c757d;
    }
    </style>
</head>
<body>


    <!-- App Container -->
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="index.php1" class="sidebar-brand">
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
                <p class="text-primary">Community Member</p>
            </div>
        </div>
            
            <ul class="sidebar-nav">
                <li class="sidebar-nav-item active">
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
                <li class="sidebar-nav-item">
                    <a href="mytasks.php" class="sidebar-nav-link" id="my-tasks-link">
                        <i class="fas fa-clipboard-list"></i>
                        <span>My Tasks</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="profile.php" class="sidebar-nav-link">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
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
            <!-- Header -->
            <header class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="sidebar-toggle d-md-none me-3">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title mb-0">Dashboard</h1>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="dropdown me-3">
                            <button class="btn btn-icon btn-ghost notification-badge" id="header-notification-badge">
                                <i class="fas fa-bell"></i>
                                <span class="badge badge-sm badge-danger">3</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="p-3 border-bottom">
                                    <h5 class="m-0">Notifications</h5>
                                </div>
                                <div id="header-notification-list">
                                    <!-- Notifications will be populated here -->
                                    <a href="#" class="dropdown-item notification-item unread">
                                        <div class="notification-icon bg-primary">
                                            <i class="fas fa-clipboard-check"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="mb-1">Your task application was accepted!</p>
                                            <span class="text-sm text-gray">2 hours ago</span>
                                        </div>
                                    </a>
                                    <a href="#" class="dropdown-item notification-item unread">
                                        <div class="notification-icon bg-success">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="mb-1">You received a new 5-star rating!</p>
                                            <span class="text-sm text-gray">Yesterday</span>
                                        </div>
                                    </a>
                                    <a href="#" class="dropdown-item notification-item unread">
                                        <div class="notification-icon bg-info">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p class="mb-1">New message from Juan Dela Cruz</p>
                                            <span class="text-sm text-gray">Yesterday</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="p-2 text-center border-top">
                                    <a href="#" class="btn btn-sm btn-outline w-100">View All</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item" id="header-logout-btn">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Log Out</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>


    <!-- Earnings History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Earnings History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Task</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($earnings)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No earnings yet.</td>
                                    </tr>
                                <?php else: foreach ($earnings as $earning): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($earning['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($earning['title'] ?? 'Task') ?></td>
                                        <td>₱<?= isset($earning['pay']) ? number_format($earning['pay'], 2) : '0.00' ?></td>
                                        <td>
                                            <span class="status-badge" data-status="<?= htmlspecialchars(strtolower($earning['status'])) ?>">
                                                <?= ucfirst($earning['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Modal (Demo) -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">Withdraw Earnings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your current balance is <strong>₱<?= number_format($balance, 2) ?></strong>.</p>
                <div class="mb-3">
                    <label for="withdraw-amount" class="form-label">Amount to Withdraw</label>
                    <input type="number" class="form-control" id="withdraw-amount" name="withdraw-amount" min="1" max="<?= $balance ?>" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label for="withdraw-method" class="form-label">Withdrawal Method</label>
                    <select class="form-select" id="withdraw-method" name="withdraw-method" required>
                        <option value="" selected disabled>Select method</option>
                        <option value="gcash">GCash</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="withdraw-details" class="form-label">Account Details</label>
                    <input type="text" class="form-control" id="withdraw-details" name="withdraw-details" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Request Withdrawal</button>
            </div>
        </form>
    </div>
</div>
        </div>
    </div>
    </div>
        <div id="chatbot-toggle" class="chatbot-toggle">
    <div class="chatbot-toggle-avatar">
      <img src="../js/images/gabay.png" class="chatbot-avatar">
    </div>
    <div class="chatbot-toggle-pulse"></div>
  </div>
  
  <!-- Chatbot Container -->
  <div id="chatbot-container" class="chatbot-container hidden">
    <div class="chatbot-header">
      <div class="chatbot-header-info">
        <div class="chatbot-avatar-small-container">
          <img src="../js/images/gabay.png" class="chatbot-avatar-small">
        </div>
        <div class="chatbot-header-text">
          <span class="chatbot-title">Gabay AI Support</span>
          <span class="chatbot-status">Online</span>
        </div>
      </div>
      <div class="chatbot-header-actions">
        <button id="chatbot-clear" class="chatbot-action-button" title="Clear conversation">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
        </button>
        <button id="chatbot-close" class="chatbot-action-button" title="Close chat">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>
    </div>
    
    <div class="chatbot-messages" id="chatbot-messages">
      <div class="message-container">
        <div class="chatbot-message chatbot-message-received">
          <div class="message-content">
            Kumusta! 👋 Ako si Gabay, nandito ako para tumulong. Anong maitutulong ko sa'yo ngayon?
          </div>
          <div class="message-time">12:00 PM</div>
        </div>
      </div>
    </div>
    
    <div id="quick-topics" class="quick-topics">
      <div class="quick-topics-title">Madalas na Tanong:</div>
      <div class="quick-topics-container">
        <button class="quick-topic-button" data-topic="track">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Track Order
        </button>
        <button class="quick-topic-button" data-topic="how-to-use">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
          How to Use
        </button>

        <button class="quick-topic-button" data-topic="contact">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          Contact Support
        </button>
        <button class="quick-topic-button" data-topic="issue">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          Report Issue
        </button>
      </div>
    </div>
    
    <div id="typing-indicator" class="typing-indicator hidden">
      <div class="typing-bubble">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
      <div class="typing-text">Si Gabay ay nagta-type...</div>
    </div>
    
    <div class="chatbot-input-area">
      <div class="chatbot-input-container">
        <input type="text" id="chatbot-input" placeholder="Type your message...">
      </div>
      <button id="chatbot-send" class="chatbot-send-button" title="Send message">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      </button>
    </div>
  </div>

    <script src="../js/gabay.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>