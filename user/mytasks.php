<?php
session_start();
require_once('../auth/db.php');

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Fetch current user info for sidebar
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

// Handle form submissions
// In the task creation section
if (isset($_POST['create_task'])) {
    // Get all form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $pay = isset($_POST['pay']) ? floatval($_POST['pay']) : null;
    $urgency = $_POST['urgency'];
    $task_type = $_POST['task_type'];
    $safezone_id = ($task_type == 'safezone') ? $_POST['safezone_id'] : null;
    $address = ($task_type == 'home') ? trim($_POST['address']) : null;
    $magic_word = generateMagicWord();
    $status = 'pending'; // Default status
    
    // Remove id from the query and parameter list
    $stmt = $conn->prepare("INSERT INTO tasks 
        (contractor_id, title, description, category, pay, urgency, task_type, safezone_id, status, address, magic_word) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $bind_result = $stmt->bind_param(
        "ssssdssssss",
        $user_id,
        $title,
        $description,
        $category,
        $pay,
        $urgency,
        $task_type,
        $safezone_id,
        $status,
        $address,
        $magic_word
    );
    
    if ($bind_result === false) {
        die("Bind failed: " . $stmt->error);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Task created successfully! Magic word: $magic_word";
    } else {
        $_SESSION['error_message'] = "Error creating task: " . $stmt->error;
    }
    
    $stmt->close();
    header('Location: mytasks.php');
    exit();
}

    
    // Handle task status updates
    if (isset($_POST['update_status'])) {
        $task_id = $_POST['task_id'];
        $new_status = $_POST['new_status'];

        // If trying to mark as completed, check if the task is accepted
        if ($new_status === 'completed') {
            $stmt = $conn->prepare("SELECT status FROM tasks WHERE id = ? AND contractor_id = ?");
            $stmt->bind_param("ss", $task_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($current_status);
            $stmt->fetch();
            $stmt->close();

            if ($current_status !== 'accepted') {
                $_SESSION['error_message'] = "Task must be accepted before it can be marked as completed.";
                header('Location: mytasks.php');
                exit();
            }
        }

        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND contractor_id = ?");
        $stmt->bind_param("sss", $new_status, $task_id, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Task status updated!";
        } else {
            $_SESSION['error_message'] = "Error updating task status: " . $stmt->error;
        }
        $stmt->close();

        header('Location: mytasks.php');
        exit();
    }

// Handle application status update (accept/reject)
if (isset($_POST['application_id']) && isset($_POST['application_action'])) {
    $application_id = $_POST['application_id'];
    $action = $_POST['application_action']; // 'accept' or 'reject'
    $new_status = ($action === 'accept') ? 'accepted' : 'rejected';

    // Get the task_id for this application
    $stmt = $conn->prepare("SELECT task_id FROM task_applications WHERE id = ?");
    $stmt->bind_param("s", $application_id);
    $stmt->execute();
    $stmt->bind_result($task_id);
    $stmt->fetch();
    $stmt->close();

    if ($new_status === 'accepted') {
        // Check if another application is already accepted for this task
        $stmt = $conn->prepare("SELECT COUNT(*) FROM task_applications WHERE task_id = ? AND application_status = 'accepted'");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $stmt->bind_result($accepted_count);
        $stmt->fetch();
        $stmt->close();

        if ($accepted_count > 0) {
            $_SESSION['error_message'] = "Only one helper can be accepted for this task.";
            header('Location: mytasks.php');
            exit();
        }

        // Accept this application
        $stmt = $conn->prepare("UPDATE task_applications SET application_status = 'accepted' WHERE id = ?");
        $stmt->bind_param("s", $application_id);
        $stmt->execute();
        $stmt->close();

        // Reject all other applications for this task
        $stmt = $conn->prepare("UPDATE task_applications SET application_status = 'rejected' WHERE task_id = ? AND id != ?");
        $stmt->bind_param("ss", $task_id, $application_id);
        $stmt->execute();
        $stmt->close();

        // Update the task's status
        $stmt = $conn->prepare("UPDATE tasks SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("s", $task_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Application has been accepted. All other applications have been rejected.";
    } else {
        // Just reject this application
        $stmt = $conn->prepare("UPDATE task_applications SET application_status = 'rejected' WHERE id = ?");
        $stmt->bind_param("s", $application_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Application has been rejected.";
    }

    header('Location: mytasks.php');
    exit();
}

// Function to generate magic word
function generateMagicWord() {
    $words = ['apple', 'banana', 'cherry', 'dragon', 'elephant', 'flamingo', 'giraffe', 'honey', 'igloo', 'jupiter'];
    $numbers = rand(100, 999);
    return $words[array_rand($words)] . $numbers;
}

// Get user's posted tasks
$posted_tasks = [];
$stmt = $conn->prepare("SELECT * FROM tasks WHERE contractor_id = ? ORDER BY created_at DESC");
if (!$stmt) {
    die("Prepare failed for posted_tasks: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $posted_tasks[] = $row;
}
$stmt->close();

// Get tasks user has applied to
$applied_tasks = [];
$stmt = $conn->prepare("SELECT t.*, ta.status as application_status, t.magic_word
                       FROM tasks t 
                       JOIN task_applications ta ON t.id = ta.task_id 
                       WHERE ta.helper_id = ?");
if (!$stmt) {
    die("Prepare failed for applied_tasks: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $applied_tasks[] = $row;
}
$stmt->close();

// Get safe zones for dropdown
$safezones = [];
$stmt = $conn->prepare("SELECT * FROM safezones WHERE is_approved = 1");
if (!$stmt) {
    die("Prepare failed for safezones: (" . $conn->errno . ") " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $safezones[] = $row;
}
$stmt->close();

// Fetch applications for each posted task
$task_applications = [];
foreach ($posted_tasks as $task) {
    $task_id = $task['id'];
    $stmt = $conn->prepare("SELECT ta.*, u.first_name, u.last_name, u.profile_picture FROM task_applications ta JOIN users u ON ta.helper_id = u.id WHERE ta.task_id = ?");
    $stmt->bind_param("s", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task_applications[$task_id] = [];
    while ($row = $result->fetch_assoc()) {
        $task_applications[$task_id][] = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['ratee_id'], $_POST['task_id'])) {
    $rater_id = $user_id;
    $ratee_id = $_POST['ratee_id'];
    $task_id = $_POST['task_id'];
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback'] ?? '');

    // Prevent duplicate ratings for the same task and user pair
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE rater_id = ? AND ratee_id = ? AND task_id = ?");
    $stmt->bind_param("sss", $rater_id, $ratee_id, $task_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO ratings (id, task_id, rater_id, ratee_id, rating, feedback, created_at) VALUES (UUID(), ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssis", $task_id, $rater_id, $ratee_id, $rating, $feedback);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thank you for your rating!";
        } else {
            $_SESSION['error_message'] = "Error saving rating: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "You have already rated this user for this task.";
    }
    header('Location: mytasks.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | HatidGawa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../css/gabay.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="icon" href="assets/images/logo.svg">
</head>
<body>
    <!-- App Container -->
 <!-- App Container -->
 <div class="app-container">
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
                    <h5 id="sidebar-user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <p class="text-black">Community Member</p>
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
                    <a href="../auth/logout.php" class="sidebar-nav-link" id="">
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
            <!-- Header (Same as dashboard) -->
            
            <!-- My Tasks Content -->
            <div class="dashboard-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title mb-0">My Tasks</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="fas fa-plus me-2"></i>Post New Task
                    </button>
                </div>
                
                <!-- Display messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <!-- Task Tabs with Enhanced Mobile Responsiveness -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills nav-fill mb-4 shadow-sm rounded overflow-hidden" id="myTasksTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3" id="posted-tab" data-bs-toggle="tab" data-bs-target="#posted" type="button" role="tab">
                        <i class="fas fa-clipboard-list me-2 d-none d-sm-inline-block"></i>Posted Tasks 
                        <span class="badge bg-primary ms-1 rounded-pill"><?= count($posted_tasks) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3" id="applied-tab" data-bs-toggle="tab" data-bs-target="#applied" type="button" role="tab">
                        <i class="fas fa-hand-paper me-2 d-none d-sm-inline-block"></i>Applied Tasks 
                        <span class="badge bg-primary ms-1 rounded-pill"><?= count($applied_tasks) ?></span>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTasksTabContent">
                <!-- Posted Tasks Tab -->
                <div class="tab-pane fade show active" id="posted" role="tabpanel">
                    <?php if (empty($posted_tasks)): ?>
                        <div class="card shadow-sm border-0 rounded-lg">
                            <div class="card-body text-center py-5">
                                <div class="empty-state-icon bg-primary-light rounded-circle p-4 mx-auto mb-4" style="width: fit-content;">
                                    <i class="fas fa-clipboard-list fa-3x text-primary"></i>
                                </div>
                                <h5 class="mb-3">No tasks posted yet</h5>
                                <p class="text-muted mb-4">Get started by posting your first task</p>
                                <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                                    <i class="fas fa-plus me-2"></i>Post a Task
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 rounded-lg">
                            <div class="card-body p-0 p-sm-3">
                                <!-- Mobile View for Posted Tasks -->
                                <div class="d-block d-lg-none">
                                    <?php foreach ($posted_tasks as $task): ?>
                                        <div class="task-card mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="task-icon bg-primary-light text-primary p-2 rounded-circle me-3">
                                                        <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                                    </div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                </div>
                                                <span class="badge badge-<?= getStatusBadge($task['status']) ?> ms-2">
                                                    <?= ucfirst($task['status']) ?>
                                                </span>
                                            </div>
                                            <div class="task-details mb-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Category:</small>
                                                        <span><?= ucfirst($task['category']) ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Budget:</small>
                                                        <span><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></span>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Location:</small>
                                                        <span><?= ($task['task_type'] == 'home') ? 'Home' : 'Safe Zone' ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Created:</small>
                                                        <span><?= date('M d, Y', strtotime($task['created_at'])) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="task-actions d-flex justify-content-end gap-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#taskDetailsModal"
                                                    data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'>
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" 
                                                        data-bs-target="#editTaskModal" data-task-id="<?= $task['id'] ?>">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <?php if ($task['status'] == 'pending' || $task['status'] == 'accepted'): ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" id="statusDropdown<?= $task['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusDropdown<?= $task['id'] ?>">
                                                            <li>
                                                                <form method="POST" action="mytasks.php">
                                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                                    <input type="hidden" name="new_status" value="completed">
                                                                    <button type="submit" name="update_status" class="dropdown-item text-success">
                                                                        <i class="fas fa-check me-2"></i> Mark as Completed
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" action="mytasks.php">
                                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                                    <input type="hidden" name="new_status" value="cancelled">
                                                                    <button type="submit" name="update_status" class="dropdown-item text-danger">
                                                                        <i class="fas fa-times me-2"></i> Cancel Task
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($task_applications[$task['id']])): ?>
                                                <div class="applications mt-3 pt-3 border-top">
                                                    <p class="mb-2"><strong><i class="fas fa-users me-2"></i>Applications (<?= count($task_applications[$task['id']]) ?>)</strong></p>
                                                    <div class="accordion" id="applicationAccordion<?= $task['id'] ?>">
                                                        <?php foreach ($task_applications[$task['id']] as $index => $app): ?>
                                                            <div class="accordion-item border mb-2">
                                                                <h2 class="accordion-header" id="heading<?= $task['id'] ?>_<?= $index ?>">
                                                                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $task['id'] ?>_<?= $index ?>" aria-expanded="false">
                                                                        <div class="d-flex align-items-center w-100">
                                                                            <img src="<?= !empty($app['profile_picture']) ? htmlspecialchars($app['profile_picture']) : '../assets/images/default-avatar.png' ?>"
                                                                                alt="Profile" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
                                                                            <span><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></span>
                                                                            <span class="badge bg-info ms-auto"><?= ucfirst($app['application_status']) ?></span>
                                                                        </div>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse<?= $task['id'] ?>_<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $task['id'] ?>_<?= $index ?>" data-bs-parent="#applicationAccordion<?= $task['id'] ?>">
                                                                    <div class="accordion-body py-3">
                                                                        <div class="mb-2">
                                                                            <small class="text-muted">Applied:</small>
                                                                            <span><?= isset($app['applied_at']) ? date('M d, Y H:i', strtotime($app['applied_at'])) : 'N/A' ?></span>
                                                                        </div>
                                                                        <?php if (!empty($app['application_message'])): ?>
                                                                            <div class="mb-3">
                                                                                <small class="text-muted">Message:</small>
                                                                                <p class="mb-0"><em>"<?= htmlspecialchars($app['application_message']) ?>"</em></p>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if ($app['application_status'] == 'accepted'): ?>
                                                                            <div class="alert alert-success mb-3 py-2">
                                                                                <small>Magic Word: <strong><?= htmlspecialchars($task['magic_word']) ?></strong></small>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <a href="profile.php?id=<?= urlencode($app['helper_id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                                <i class="fas fa-user me-1"></i> View Profile
                                                                            </a>
                                                                            <?php if ($app['application_status'] == 'pending'): ?>
                                                                                <div>
                                                                                    <form method="POST" class="d-inline-flex gap-2">
                                                                                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                                                        <button type="submit" name="application_action" value="accept" class="btn btn-success btn-sm">Accept</button>
                                                                                        <button type="submit" name="application_action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                                                                    </form>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Desktop View for Posted Tasks -->
                                <div class="table-responsive d-none d-lg-block">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th></th>
                                                <th>Task</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Budget</th>
                                                <th>Created</th>
                                                <th>Magic Word</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($posted_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                    <?php if (!empty($task_applications[$task['id']])): ?>
                                                        <button 
                                                            class="btn btn-sm btn-outline-success position-relative rounded-pill px-3 py-1" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#apps<?= $task['id'] ?>" 
                                                            aria-expanded="false" 
                                                            aria-controls="apps<?= $task['id'] ?>"
                                                            title="View applications"
                                                            style="transition: all 0.2s ease;">
                                                            <span class="d-flex align-items-center">
                                                                <i class="fas fa-users me-1"></i>
                                                                Applications
                                                                <span class="badge bg-success ms-2"><?= count($task_applications[$task['id']]) ?></span>
                                                                <i class="fas fa-chevron-down ms-2 small" style="transition: transform 0.2s ease;"></i>
                                                            </span>
                                                        </button>
                                                    <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="task-icon bg-primary-light text-primary rounded-circle p-2 me-3">
                                                                <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                                <span class="text-sm text-muted">
                                                                    <?= ($task['task_type'] == 'home') ? htmlspecialchars($task['address']) : 'Safe Zone' ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= ucfirst($task['category']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= getStatusBadge($task['status']) ?>">
                                                            <?= ucfirst($task['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></td>
                                                    <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                                                    <td>
                                                        <?php if (!empty($task['magic_word']) && ($task['status'] == 'accepted' || $task['status'] == 'completed')): ?>
                                                            <span class="badge bg-success"><?= htmlspecialchars($task['magic_word']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2 justify-content-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#taskDetailsModal"
                                                                data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'>
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" 
                                                                    data-bs-target="#editTaskModal" data-task-id="<?= $task['id'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if ($task['status'] == 'pending' || $task['status'] == 'accepted'): ?>
                                                                <form method="POST" action="mytasks.php" class="d-inline">
                                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                                    <input type="hidden" name="new_status" value="completed">
                                                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-success" title="Mark as Completed">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="mytasks.php" class="d-inline">
                                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                                    <input type="hidden" name="new_status" value="cancelled">
                                                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php if (!empty($task_applications[$task['id']])): ?>
                                                    <tr class="collapse" id="apps<?= $task['id'] ?>">
                                                        <td colspan="7" class="p-0">
                                                            <div class="px-3 py-2">
                                                                <p class="mb-2"><strong><i class="fas fa-users me-2"></i>Applications (<?= count($task_applications[$task['id']]) ?>)</strong></p>
                                                                <div class="list-group">
                                                                    <?php foreach ($task_applications[$task['id']] as $app): ?>
                                                                        <div class="list-group-item border rounded mb-2">
                                                                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                                                                                <div class="d-flex align-items-center flex-grow-1 gap-3">
                                                                                    <img src="<?= !empty($app['profile_picture']) ? htmlspecialchars($app['profile_picture']) : '../assets/images/default-avatar.png' ?>"
                                                                                        alt="Profile" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                                                                                    <div>
                                                                                        <strong><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></strong>
                                                                                                                                                                                <?php
                                                                                        // Show "Rate Helper" button if task is completed and not yet rated by contractor
                                                                                        if ($task['status'] == 'completed' && !hasGivenRating($user_id, $app['helper_id'], $task['id'])): ?>
                                                                                            <button class="btn btn-warning btn-sm"
                                                                                                data-bs-toggle="modal"
                                                                                                data-bs-target="#ratingModal"
                                                                                                data-ratee-id="<?= $app['helper_id'] ?>"
                                                                                                data-task-id="<?= $task['id'] ?>">
                                                                                                <i class="fas fa-star"></i> Rate Service
                                                                                            </button>
                                                                                        <?php endif; ?>
                                                                                        <div class="text-muted small">
                                                                                            Applied: <?= isset($app['applied_at']) ? date('M d, Y H:i', strtotime($app['applied_at'])) : 'N/A' ?>
                                                                                        </div>
                                                                                        <?php if (!empty($app['application_message'])): ?>
                                                                                            <div class="mt-1"><em>"<?= htmlspecialchars($app['application_message']) ?>"</em></div>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex align-items-center gap-2 ms-auto">
                                                                                    <span class="badge bg-info"><?= ucfirst($app['application_status']) ?></span>
                                                                                    <?php if (!empty($app['magic_word']) && $app['application_status'] == 'accepted'): ?>
                                                                                        <span class="badge bg-success">Magic Word: <?= htmlspecialchars($app['magic_word']) ?></span>
                                                                                    <?php endif; ?>
                                                                                    <a href="profile.php?id=<?= urlencode($app['helper_id']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                                                                        <i class="fas fa-user me-1"></i> View Profile
                                                                                    </a>
                                                                                    <?php if ($app['application_status'] == 'pending'): ?>
                                                                                        <form method="POST" class="d-inline-flex gap-2">
                                                                                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                                                                            <button type="submit" name="application_action" value="accept" class="btn btn-success btn-sm">Accept</button>
                                                                                            <button type="submit" name="application_action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                                                                        </form>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Applied Tasks Tab -->
                <div class="tab-pane fade" id="applied" role="tabpanel">
                    <?php if (empty($applied_tasks)): ?>
                        <div class="card shadow-sm border-0 rounded-lg">
                            <div class="card-body text-center py-5">
                                <div class="empty-state-icon bg-primary-light rounded-circle p-4 mx-auto mb-4" style="width: fit-content;">
                                    <i class="fas fa-hand-paper fa-3x text-primary"></i>
                                </div>
                                <h5 class="mb-3">No applications yet</h5>
                                <p class="text-muted mb-4">Browse tasks and apply to help others</p>
                                <a href="tasks.php" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-search me-2"></i>Browse Tasks
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 rounded-lg">
                            <div class="card-body p-0 p-sm-3">
                                <!-- Mobile View for Applied Tasks -->
                                <div class="d-block d-lg-none">
                                    <?php foreach ($applied_tasks as $task): ?>
                                        <div class="task-card mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="task-icon bg-primary-light text-primary p-2 rounded-circle me-3">
                                                        <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                                    </div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                </div>
                                                <span class="badge bg-info ms-2">
                                                    <?= ucfirst($task['application_status']) ?>
                                                </span>
                                            </div>
                                            <div class="task-details mb-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Category:</small>
                                                        <span><?= ucfirst($task['category']) ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Budget:</small>
                                                        <span><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></span>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Location:</small>
                                                        <span><?= ($task['task_type'] == 'home') ? 'Home' : 'Safe Zone' ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Applied:</small>
                                                        <span><?= date('M d, Y', strtotime($task['created_at'])) ?></span>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($task['application_status'] == 'accepted'): ?>
                                                    <div class="alert alert-success mt-3 py-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-key me-2"></i>
                                                            <div>
                                                                <small class="d-block">Magic Word:</small>
                                                                <strong><?= htmlspecialchars($task['magic_word']) ?></strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="task-actions d-flex justify-content-end gap-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#taskDetailsModal"
                                                    data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'>
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </button>
                                                <?php if ($task['application_status'] == 'applied'): ?>
                                                    <form method="POST" action="cancel_application.php">
                                                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-times me-1"></i> Cancel Application
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Desktop View for Applied Tasks -->
                                <div class="table-responsive d-none d-lg-block">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Task</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Budget</th>
                                                <th>Applied</th>
                                                <th>Magic Word</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($applied_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="task-icon bg-primary-light text-primary rounded-circle p-2 me-3">
                                                                <i class="fas fa-<?= getTaskIcon($task['category']) ?>"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                                <span class="text-sm text-muted">
                                                                    <?= ($task['task_type'] == 'home') ? htmlspecialchars($task['address']) : 'Safe Zone' ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= ucfirst($task['category']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= getStatusBadge($task['status']) ?>">
                                                            <?= ucfirst($task['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></td>
                                                    <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                                                    <td>
                                                        <?php if (!empty($task['magic_word']) && ($task['status'] == 'accepted' || $task['status'] == 'completed')): ?>
                                                            <span class="badge bg-success"><?= htmlspecialchars($task['magic_word']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2 justify-content-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#taskDetailsModal"
                                                                data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'>
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if ($task['application_status'] == 'applied'): ?>
                                                                <form method="POST" action="cancel_application.php">
                                                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add custom CSS for enhanced styling -->
<style>
    /* Custom colors to maintain the current color scheme */
    .bg-primary-light {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    /* Task card styles */
    .task-card {
        transition: all 0.2s ease;
    }
    
    .task-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Custom tab styling */
    .nav-pills .nav-link {
        border-radius: 0;
        color: #6c757d;
        font-weight:
    
/* Custom tab styling (continued) */
    .nav-pills .nav-link {
        border-radius: 0;
        color: #6c757d;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .nav-pills .nav-link.active {
        background-color: var(--bs-primary);
        color: white;
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        background-color: rgba(13, 110, 253, 0.05);
        color: var(--bs-primary);
    }
    
    /* Status badge styling */
    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-accepted {
        background-color: #17a2b8;
        color: #fff;
    }
    
    .badge-completed {
        background-color: #28a745;
        color: #fff;
    }
    
    .badge-cancelled {
        background-color: #dc3545;
        color: #fff;
    }
    
    /* Empty state styling */
    .empty-state-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Application accordion styling */
    .accordion-button {
        padding: 0.75rem 1rem;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.05);
        color: var(--bs-primary);
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(13, 110, 253, 0.25);
    }
    
    /* Enhance table responsiveness */
    @media (max-width: 991.98px) {
        .table-responsive {
            border: 0;
            overflow-x: hidden;
        }
    }
    
    /* Custom button styling */
    .btn-outline-primary, .btn-primary {
        border-width: 2px;
    }
    
    .btn-outline-primary:hover, .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    /* Task icon styling */
    .task-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
    
    /* Fix for small screens */
    @media (max-width: 575.98px) {
        .task-actions {
            flex-wrap: wrap;
        }
        
        .task-actions .btn {
            flex: 1;
            min-width: 100px;
            margin-top: 0.5rem;
        }
    }

    .btn-link[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.1s;
    }
    .btn-link .fa-chevron-down {
        transition: transform 0.1s;
    }
</style>

<!-- Add improved JavaScript for better interactivity -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle task details modal
        const taskDetailsModal = document.getElementById('taskDetailsModal');
        if (taskDetailsModal) {
            taskDetailsModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const taskData = JSON.parse(button.getAttribute('data-task'));
                
                // Update modal content with task details
                const modalTitle = this.querySelector('.modal-title');
                const modalBody = this.querySelector('.modal-body');
                
                modalTitle.textContent = taskData.title;
                
                // Build the modal content with all task details
let content = `
    <div class="task-details">
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="task-icon bg-primary-light text-primary rounded-circle p-3 me-3">
                    <i class="fas fa-${getTaskIconJS(taskData.category)} fa-lg"></i>
                </div>
                <div>
                    <h5 class="mb-1">${taskData.title}</h5>
                    <span class="badge badge-${getStatusBadgeJS(taskData.status)}">
                        ${capitalize(taskData.status)}
                    </span>
                </div>
            </div>
            <p class="mb-0">${taskData.description}</p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted">Category</label>
                    <div class="fw-medium">${capitalize(taskData.category)}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Location Type</label>
                    <div class="fw-medium">${taskData.task_type === 'home' ? 'Home' : 'Safe Zone'}</div>
                </div>
                ${taskData.task_type === 'home' ? `
                <div class="mb-3">
                    <label class="form-label text-muted">Address</label>
                    <div class="fw-medium">${taskData.address}</div>
                </div>` : ''}
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted">Budget</label>
                    <div class="fw-medium">${taskData.pay ? '₱' + parseFloat(taskData.pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'None'}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Created</label>
                    <div class="fw-medium">${formatDate(taskData.created_at)}</div>
                </div>
                ${(taskData.magic_word && (taskData.status === 'accepted' || taskData.status === 'completed')) ? `
                <div class="mb-3">
                    <label class="form-label text-muted">Magic Word</label>
                    <div class="fw-medium badge bg-success">${taskData.magic_word}</div>
                </div>` : ''}
            </div>
        </div>
    </div>
`;
                
                modalBody.innerHTML = content;
            });
        }
        
        // Helper function to get task icon based on category
        function getTaskIconJS(category) {
            const icons = {
                'cleaning': 'broom',
                'delivery': 'truck',
                'maintenance': 'tools',
                'technology': 'laptop',
                'tutoring': 'book',
                'other': 'tasks'
                // Add more categories as needed
            };
            
            return icons[category.toLowerCase()] || 'tasks';
        }
        
        // Helper function to get status badge class
        function getStatusBadgeJS(status) {
            const badges = {
                'pending': 'pending',
                'accepted': 'accepted',
                'completed': 'completed',
                'cancelled': 'cancelled'
            };
            
            return badges[status.toLowerCase()] || 'pending';
        }
        
        // Helper function to capitalize first letter
        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
        
        // Helper function to format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
        // Add smooth transitions to tab switching
        const tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabEl.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const target = document.querySelector(e.target.getAttribute('data-bs-target'));
                if (target) {
                    target.classList.add('show');
                    setTimeout(() => {
                        target.classList.add('active');
                    }, 50);
                }
            });
            
            tab.addEventListener('hide.bs.tab', function(e) {
                const target = document.querySelector(e.target.getAttribute('data-bs-target'));
                if (target) {
                    target.classList.remove('active');
                    setTimeout(() => {
                        target.classList.remove('show');
                    }, 300);
                }
            });
        });
        
        // Add pulse animation to notifications
        const badges = document.querySelectorAll('.badge');
        badges.forEach(badge => {
            if (parseInt(badge.textContent) > 0) {
                badge.classList.add('pulse');
            }
        });

        // Show safezone location description on select
        const safezoneSelect = document.getElementById('safezoneSelect');
        const safezoneDescription = document.getElementById('safezoneDescription');
        if (safezoneSelect && safezoneDescription) {
            function updateSafezoneDescription() {
                const selected = safezoneSelect.options[safezoneSelect.selectedIndex];
                const desc = selected.getAttribute('data-description') || '';
                safezoneDescription.textContent = desc ? 'Description: ' + desc : '';
            }
            safezoneSelect.addEventListener('change', updateSafezoneDescription);
            updateSafezoneDescription(); // Show on page load
        }
    });
</script>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="taskDetailsModalLabel">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Content will be dynamically populated via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="mytasks.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="delivery">Delivery</option>
                                    <option value="repair">Repair</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="tutoring">Tutoring</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Budget (optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="pay" class="form-control" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Urgency</label>
                                <select name="urgency" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Task Type</label>
                                <select name="task_type" id="taskTypeSelect" class="form-select" required>
                                    <option value="home">Home Service</option>
                                    <option value="safezone">Safe Zone</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="addressField">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" 
                                id="userAddressInput"
                                value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3 d-none" id="safezoneField">
                            <label class="form-label">Safe Zone</label>
                            <select name="safezone_id" class="form-select" id="safezoneSelect">
                                <?php foreach ($safezones as $zone): ?>
                                    <option 
                                        value="<?= $zone['id'] ?>" 
                                        data-description="<?= htmlspecialchars($zone['location_description']) ?>">
                                        <?= htmlspecialchars($zone['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="safezoneDescription" class="mt-2 text-muted small"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editTaskForm" method="POST" action="update_task.php">
                    <input type="hidden" id="editTaskId" name="task_id">
                    <!-- Form fields will be populated via JavaScript -->
                    <div class="mb-3">
                        <label for="editTaskTitle" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="editTaskTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTaskDescription" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editTaskCategory" class="form-label">Category</label>
                            <select class="form-select" id="editTaskCategory" name="category" required>
                                <option value="cleaning">Cleaning</option>
                                <option value="delivery">Delivery</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="technology">Technology</option>
                                <option value="tutoring">Tutoring</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editTaskBudget" class="form-label">Budget (₱)</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="editTaskBudget" name="pay" min="0" step="0.01" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Location Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="task_type" id="editTaskTypeHome" value="home">
                            <label class="form-check-label" for="editTaskTypeHome">Home</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="task_type" id="editTaskTypeSafeZone" value="safe_zone">
                            <label class="form-check-label" for="editTaskTypeSafeZone">Safe Zone</label>
                        </div>
                    </div>
                    <div class="mb-3" id="editAddressField">
                        <label for="editTaskAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editTaskAddress" name="address">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add more JavaScript to enhance functionality -->
<script>
    // Additional JavaScript for form handling
    document.addEventListener('DOMContentLoaded', function() {
        // Task type toggle for address field
        const taskTypeHomeRadio = document.getElementById('taskTypeHome');
        const taskTypeSafeZoneRadio = document.getElementById('taskTypeSafeZone');
        const addressField = document.getElementById('addressField');
        
        if (taskTypeHomeRadio && taskTypeSafeZoneRadio && addressField) {
            taskTypeHomeRadio.addEventListener('change', function() {
                addressField.style.display = 'block';
            });
            
            taskTypeSafeZoneRadio.addEventListener('change', function() {
                addressField.style.display = 'none';
            });
        }
        
        // Same for edit form
        const editTaskTypeHomeRadio = document.getElementById('editTaskTypeHome');
        const editTaskTypeSafeZoneRadio = document.getElementById('editTaskTypeSafeZone');
        const editAddressField = document.getElementById('editAddressField');
        
        if (editTaskTypeHomeRadio && editTaskTypeSafeZoneRadio && editAddressField) {
            editTaskTypeHomeRadio.addEventListener('change', function() {
                editAddressField.style.display = 'block';
            });
            
            editTaskTypeSafeZoneRadio.addEventListener('change', function() {
                editAddressField.style.display = 'none';
            });
        }
        
        // Handle edit task modal population
        const editTaskModal = document.getElementById('editTaskModal');
        if (editTaskModal) {
            editTaskModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const taskId = button.getAttribute('data-task-id');
                
                // You'd typically fetch the task data from the server here
                // For this example, we'll simulate it with data already loaded in the DOM
                const taskElements = document.querySelectorAll('[data-task]');
                let taskData = null;
                
                taskElements.forEach(element => {
                    const data = JSON.parse(element.getAttribute('data-task'));
                    if (data.id == taskId) {
                        taskData = data;
                    }
                });
                
                if (taskData) {
                    // Populate the form
                    document.getElementById('editTaskId').value = taskData.id;
                    document.getElementById('editTaskTitle').value = taskData.title;
                    document.getElementById('editTaskDescription').value = taskData.description;
                    document.getElementById('editTaskCategory').value = taskData.category;
                    document.getElementById('editTaskBudget').value = taskData.pay || '';
                    
                    if (taskData.task_type === 'home') {
                        document.getElementById('editTaskTypeHome').checked = true;
                        document.getElementById('editAddressField').style.display = 'block';
                    } else {
                        document.getElementById('editTaskTypeSafeZone').checked = true;
                        document.getElementById('editAddressField').style.display = 'none';
                    }
                    
                    document.getElementById('editTaskAddress').value = taskData.address || '';
                    document.getElementById('editTaskMagicWord').value = taskData.magic_word || '';
                }
            });
        }
        
        // Add form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Add animation to new tasks
        const newTaskElements = document.querySelectorAll('tr, .task-card');
        newTaskElements.forEach(element => {
            element.classList.add('animate__animated', 'animate__fadeIn');
        });
    });
</script>

<!-- Add additional CSS for animations -->
<style>
    /* Animation for new elements */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate__fadeIn {
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Pulse animation for notifications */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }
    
    .badge.pulse {
        animation: pulse 2s infinite;
    }
    
    /* Responsive improvements */
    @media (max-width: 767.98px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .nav-pills .nav-link {
            font-size: 0.9rem;
            padding: 0.5rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle address/safezone fields based on task type
        document.getElementById('taskTypeSelect').addEventListener('change', function() {
            const taskType = this.value;
            const addressField = document.getElementById('addressField');
            const safezoneField = document.getElementById('safezoneField');
            const userAddress = <?= json_encode($user['address'] ?? '') ?>;
            const addressInput = document.getElementById('userAddressInput');
            
            if (taskType === 'safezone') {
                addressField.classList.add('d-none');
                safezoneField.classList.remove('d-none');
                addressInput.value = '';
                document.querySelector('[name="address"]').required = false;
                document.querySelector('[name="safezone_id"]').required = true;
            } else {
                addressField.classList.remove('d-none');
                safezoneField.classList.add('d-none');
                addressInput.value = userAddress;
                document.querySelector('[name="address"]').required = true;
                document.querySelector('[name="safezone_id"]').required = false;
            }
        });

        // On modal show, ensure address is set if Home Service is selected
        document.getElementById('createTaskModal').addEventListener('show.bs.modal', function() {
            const taskType = document.getElementById('taskTypeSelect').value;
            const userAddress = <?= json_encode($user['address'] ?? '') ?>;
            const addressInput = document.getElementById('userAddressInput');
            if (taskType === 'home') {
                addressInput.value = userAddress;
            }
        });
    </script>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="ratingForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ratingModalLabel">Rate Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="ratee_id" id="rateeIdInput">
          <input type="hidden" name="task_id" id="taskIdInput">
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <select class="form-select" name="rating" required>
              <option value="">Select rating</option>
              <option value="5">5 - Excellent</option>
              <option value="4">4 - Good</option>
              <option value="3">3 - Average</option>
              <option value="2">2 - Poor</option>
              <option value="1">1 - Terrible</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Feedback (optional)</label>
            <textarea class="form-control" name="feedback" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Rating</button>
        </div>
      </div>
    </form>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ratingModal = document.getElementById('ratingModal');
    if (ratingModal) {
        ratingModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var rateeId = button.getAttribute('data-ratee-id');
            var taskId = button.getAttribute('data-task-id');
            document.getElementById('rateeIdInput').value = rateeId;
            document.getElementById('taskIdInput').value = taskId;
        });
    }
});
</script>
</body>
</html>

<?php
// Helper functions
function getTaskIcon($category) {
    switch ($category) {
        case 'delivery': return 'shopping-basket';
        case 'repair': return 'tools';
        case 'cleaning': return 'broom';
        case 'tutoring': return 'book';
        default: return 'clipboard-list';
    }
}

function getStatusBadge($status) {
    switch ($status) {
        case 'completed': return 'success';
        case 'accepted': return 'primary';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        case 'applied': return 'info';
        case 'rejected': return 'secondary';
        default: return 'light';
    }
}

function hasGivenRating($rater_id, $ratee_id, $task_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE rater_id = ? AND ratee_id = ? AND task_id = ?");
    $stmt->bind_param("sss", $rater_id, $ratee_id, $task_id);
    $stmt->execute();
    $stmt->store_result();
    $hasRated = $stmt->num_rows > 0;
    $stmt->close();
    return $hasRated;
}
?>