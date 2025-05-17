<?php
require_once('../auth/db.php');
session_start();

if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user']['id'];

// Fetch unread messages count for the current user
$unread_messages_count = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM messages m
    JOIN tasks t ON m.task_id = t.id
    LEFT JOIN task_applications ta ON t.id = ta.task_id
    WHERE t.contractor_id = ? OR ta.helper_id = ?
");
if (!$stmt) {
    die("Prepare failed for messages count: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($unread_messages_count);
$stmt->fetch();
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

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Tasks completed (as helper or poster)    
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM (
        SELECT t.id FROM tasks t WHERE t.contractor_id = ? AND t.status = 'completed'
        UNION ALL
        SELECT t.id FROM tasks t
        JOIN task_applications ta ON t.id = ta.task_id
        WHERE ta.helper_id = ? AND t.status = 'completed' AND ta.status = 'accepted'
    ) as completed_tasks
");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($tasks_completed);
$stmt->fetch();
$stmt->close();

// Active tasks (as poster or helper)
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM (
        SELECT t.id FROM tasks t WHERE t.contractor_id = ? AND t.status IN ('pending','accepted')
        UNION ALL
        SELECT t.id FROM tasks t
        JOIN task_applications ta ON t.id = ta.task_id
        WHERE ta.helper_id = ? AND t.status IN ('pending','accepted') AND ta.status = 'accepted'
    ) as active_tasks
");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($active_tasks);
$stmt->fetch();
$stmt->close();

// Earnings (as helper)
$stmt = $conn->prepare("SELECT SUM(amount) FROM tasker_earnings WHERE helper_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($earnings);
$stmt->fetch();
$stmt->close();
$earnings = $earnings ? $earnings : 0;

// Average rating
$stmt = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE ratee_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($avg_rating);
$stmt->fetch();
$stmt->close();
$avg_rating = $avg_rating ? round($avg_rating, 2) : 0;

// My Tasks (posted and applied)
$my_tasks = [];
$stmt = $conn->prepare("
    SELECT t.*, 'Posted' as role FROM tasks t WHERE t.contractor_id = ?
    UNION ALL
    SELECT t.*, 'Applied' as role FROM tasks t
    JOIN task_applications ta ON t.id = ta.task_id
    WHERE ta.helper_id = ?
");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $my_tasks[] = $row;
}
$stmt->close();

// Recent Activity (last 5: tasks, ratings, earnings)
$recent_activity = [];
// Completed tasks
$stmt = $conn->prepare("SELECT 'Task Completed' as type, title, created_at FROM tasks WHERE (contractor_id = ? OR id IN (SELECT task_id FROM task_applications WHERE helper_id = ?)) AND status = 'completed' ORDER BY created_at DESC LIMIT 2");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $recent_activity[] = [
    'type' => $row['type'],
    'desc' => 'You completed "' . $row['title'] . '" task',
    'date' => $row['created_at'],
    'icon' => 'clipboard-check',
    'color' => 'primary'
];
$stmt->close();
// Ratings
$stmt = $conn->prepare("SELECT r.rating, r.created_at, u.first_name, u.last_name FROM ratings r JOIN users u ON r.rater_id = u.id WHERE r.ratee_id = ? ORDER BY r.created_at DESC LIMIT 1");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $recent_activity[] = [
    'type' => 'New Rating',
    'desc' => 'You received a ' . $row['rating'] . '-star rating from ' . $row['first_name'] . ' ' . $row['last_name'],
    'date' => $row['created_at'],
    'icon' => 'star',
    'color' => 'success'
];
$stmt->close();
// Earnings
$stmt = $conn->prepare("SELECT amount, earned_at FROM tasker_earnings WHERE helper_id = ? ORDER BY earned_at DESC LIMIT 1");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $recent_activity[] = [
    'type' => 'Payment Received',
    'desc' => 'You received ₱' . number_format($row['amount'], 2) . ' for completing a task',
    'date' => $row['earned_at'],
    'icon' => 'wallet',
    'color' => 'danger'
];
$stmt->close();
// Sort by date desc
usort($recent_activity, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$recent_activity = array_slice($recent_activity, 0, 5);

// Top Helpers in Your Area (top 3 by rating, not self)
$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.profile_picture, AVG(r.rating) as avg_rating, u.skills FROM users u LEFT JOIN ratings r ON u.id = r.ratee_id WHERE u.id != ? GROUP BY u.id ORDER BY avg_rating DESC LIMIT 3");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$top_helpers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recommended Tasks (not posted or applied by user, open status)
$stmt = $conn->prepare("
    SELECT t.* FROM tasks t
    WHERE t.status = 'pending'
    AND t.contractor_id != ?
    AND t.id NOT IN (SELECT task_id FROM task_applications WHERE helper_id = ?)
    ORDER BY t.created_at DESC LIMIT 3
");
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$recommended_tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'accepted': return 'primary';
        case 'pending': return 'info';
        case 'cancelled': return 'danger';
        case 'applied': return 'info';
        case 'rejected': return 'secondary';
        default: return 'light';
    }
}
function getTaskIcon($category) {
    switch (strtolower($category)) {
        case 'delivery': return 'shopping-basket';
        case 'repair': return 'tools';
        case 'cleaning': return 'broom';
        case 'tutoring': return 'book';
        case 'technology': return 'laptop';
        case 'maintenance': return 'tools';
        default: return 'clipboard-list';
    }
}
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
</head>
<body>


    <!-- App Container -->
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 410px;">
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
                    <h5 id="sidebar-user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
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
                    <a href="../auth/logout.php" class="sidebar-nav-link" id="sidebar-logout-btn">
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
                        
                        
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="stat-title">Tasks Completed</h6>
                                        <h3 class="stat-value"><?= $tasks_completed ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary-light text-primary">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="stat-title">Active Tasks</h6>
                                        <h3 class="stat-value"><?= $active_tasks ?></h3>
                                    </div>
                                    <div class="stat-icon bg-warning-light text-warning">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="stat-title">Rating</h6>
                                        <h3 class="stat-value"><?= $avg_rating ?></h3>
                                    </div>
                                    <div class="stat-icon bg-info-light text-info">
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Task Overview -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">My Tasks</h5>
                                <div class="card-actions">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline active">All</button>
                                        <button class="btn btn-sm btn-outline">Posted</button>
                                        <button class="btn btn-sm btn-outline">Applied</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Status</th>
                                                <th>Due Date</th>
                                                <th>Budget</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($my_tasks)): ?>
                                                <tr><td colspan="5" class="text-center text-muted">No tasks yet.</td></tr>
                                            <?php else: foreach ($my_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="task-icon bg-primary-light text-primary me-3">
                                                                <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                                <span class="text-sm text-gray"><?= htmlspecialchars($task['address']) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge badge-<?= getStatusBadge($task['status']) ?>" style="color: black;"><?= ucfirst($task['status']) ?></span></td>
                                                    <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                                                    <td><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <a href="mytasks.php" class="btn btn-sm btn-icon btn-ghost" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="messages.php?task_id=<?= $task['id'] ?>" class="btn btn-sm btn-icon btn-ghost" title="Message">
                                                                <i class="fas fa-comment-alt"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-outline">View All Tasks</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="activity-timeline">
                                    <?php if (empty($recent_activity)): ?>
                                        <div class="p-4 text-center text-muted">No recent activity.</div>
                                    <?php else: foreach ($recent_activity as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon bg-<?= $activity['color'] ?>">
                                                <i class="fas fa-<?= $activity['icon'] ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <h6 class="mb-1"><?= htmlspecialchars($activity['type']) ?></h6>
                                                <p class="mb-1"><?= htmlspecialchars($activity['desc']) ?></p>
                                                <span class="text-sm text-gray"><?= date('M d, Y', strtotime($activity['date'])) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-outline">View All Activity</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Community & Recommendations -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Helpers in Your Area</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="user-list">
                                    <?php foreach ($top_helpers as $helper): ?>
                                    <li class="user-list-item">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                <img src="<?php
                                                    if (!empty($helper['profile_picture'])) {
                                                        echo (strpos($helper['profile_picture'], 'http') === 0)
                                                            ? htmlspecialchars($helper['profile_picture'])
                                                            : '../' . htmlspecialchars($helper['profile_picture']);
                                                    } else {
                                                        echo 'https://randomuser.me/api/portraits/lego/1.jpg';
                                                    }
                                                ?>" alt="User">
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($helper['first_name'] . ' ' . $helper['last_name']) ?></h6>
                                                <div class="d-flex align-items-center">
                                                    <div class="rating me-2">
                                                        <i class="fas fa-star text-warning"></i>
                                                        <span><?= round($helper['avg_rating'], 1) ?></span>
                                                    </div>
                                                    <span class="text-sm text-gray"><?= htmlspecialchars($helper['skills']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="profile.php?id=<?= $helper['id'] ?>" class="btn btn-sm btn-outline">View Profile</a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-outline">View All Helpers</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recommended Tasks for You</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="task-list">
                                    <?php foreach ($recommended_tasks as $task): ?>
                                    <li class="task-list-item">
                                        <div class="d-flex align-items-center">
                                            <div class="task-icon bg-primary-light text-primary me-3">
                                                <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-primary me-2">₱<?= number_format($task['pay'], 2) ?></span>
                                                    <span class="text-sm text-gray"><?= htmlspecialchars($task['address']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="tasks-user.php?apply=<?= $task['id'] ?>" class="btn btn-sm btn-primary">Apply</a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer text-center">
                                <a href="tasks-user.php" class="btn btn-outline">Browse All Tasks</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Safety Tips -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="safety-icon me-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h4 class="mb-0">Safety Tips</h4>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="safety-tip">
                                            <h6><i class="fas fa-check-circle me-2"></i> Meet at Safe Zones</h6>
                                            <p class="mb-0" style="color: black;">Always meet at designated safe zones like barangay halls or community centers.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="safety-tip">
                                            <h6><i class="fas fa-check-circle me-2"></i> Verify Identities</h6>
                                            <p class="mb-0" style="color: black;">Check profiles, ratings, and reviews before accepting or applying for tasks.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="safety-tip">
                                            <h6><i class="fas fa-check-circle me-2"></i> Use In-App Communication</h6>
                                            <p class="mb-0" style="color: black;">Keep all communications within the platform for your safety and record-keeping.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="dashboard-footer">
                <div class="container-fluid">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <p class="mb-2 mb-md-0">&copy; 2023 HatidGawa. All rights reserved.</p>
                        <div class="d-flex gap-3">
                            <a href="#">Privacy Policy</a>
                            <a href="#">Terms of Service</a>
                            <a href="#">Help Center</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Emergency Alert Modal -->
    <div class="modal fade" id="emergency-modal" tabindex="-1" aria-labelledby="emergencyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title" id="emergencyModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Emergency Alert
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>If you're in an emergency situation or feel unsafe during a task, use this feature to alert our safety team and nearby authorities.</p>
                    <form id="emergency-form">
                        <div class="mb-3">
                            <label for="emergency-type" class="form-label">Emergency Type</label>
                            <select id="emergency-type" class="form-select" required>
                                <option value="" selected disabled>Select emergency type</option>
                                <option value="safety">Personal Safety Concern</option>
                                <option value="medical">Medical Emergency</option>
                                <option value="harassment">Harassment</option>
                                <option value="other">Other Emergency</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="emergency-details" class="form-label">Details (Optional)</label>
                            <textarea id="emergency-details" class="form-control" rows="3" placeholder="Provide any details that might help responders"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" id="share-location" class="form-check-input" checked>
                            <label for="share-location" class="form-check-label">
                                Share my current location
                            </label>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Our safety team will contact you immediately. If it's a life-threatening emergency, please also call 911 directly.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="emergency-form" class="btn btn-danger" id="send-emergency-btn">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Send Emergency Alert
                    </button>
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
    
    <!-- Emergency Button -->
    <button id="emergency-btn" class="btn btn-danger btn-icon position-fixed">
        <i class="fas fa-exclamation-triangle"></i>
    </button>

    <script src="js/main.js"></script>
    <script>
    // Sidebar Toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebarCloseBtn = document.querySelector('.sidebar-close-btn');
    if (sidebarToggle && sidebarCloseBtn) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('show');
        });

        sidebarCloseBtn.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
        });
    }

    // Emergency Modal Trigger
    const emergencyBtn = document.getElementById('emergency-btn');
    const emergencyModal = document.getElementById('emergency-modal');
    if (emergencyBtn && emergencyModal) {
        emergencyBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(emergencyModal);
            modal.show();
        });
    }

    // Emergency Form Handling
    const emergencyForm = document.getElementById('emergency-form');
    const emergencyType = document.getElementById('emergency-type');
    const sendBtn = document.getElementById('send-emergency-btn');

    if (emergencyForm && emergencyType && sendBtn) {
        // Enable/disable Send button based on emergency type selection
        emergencyType.addEventListener('change', function() {
            sendBtn.disabled = !emergencyType.value;
        });

        // Initial button state
        sendBtn.disabled = !emergencyType.value;

        // Handle form submit
        emergencyForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual form submission

            const type = emergencyType.value;
            const details = document.getElementById('emergency-details').value;
            const shareLocation = document.getElementById('share-location').checked;

            // You can send this data to your server using AJAX
            console.log({
                type,
                details,
                shareLocation
            });

            // Optional: Show a confirmation alert
            alert('Emergency alert sent successfully! Our team will contact you shortly.');

            // Close modal after submission
            const modalInstance = bootstrap.Modal.getInstance(emergencyModal);
            modalInstance.hide();

            // Reset form after sending
            emergencyForm.reset();
            sendBtn.disabled = true;
        });
    }
</script>

</body>
    </html>