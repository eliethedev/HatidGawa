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

// Handle task creation
if (isset($_POST['create_task'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $pay = isset($_POST['pay']) ? floatval($_POST['pay']) : null;
    $urgency = $_POST['urgency'];
    $task_type = $_POST['task_type'];
    $safezone_id = ($task_type == 'safezone') ? $_POST['safezone_id'] : null;
    $address = ($task_type == 'home') ? trim($_POST['address']) : null;
    $magic_word = generateMagicWord();
    $status = 'pending';

    $stmt = $conn->prepare("INSERT INTO tasks 
        (id, contractor_id, title, description, category, pay, urgency, task_type, safezone_id, status, address, magic_word) 
        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

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
$stmt = $conn->prepare("SELECT t.*, ta.status as application_status 
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

// Get safezones for dropdown
$safezones = [];
$stmt = $conn->prepare("SELECT id, name, location_description, barangay, is_approved, created_at, latitude, longitude FROM safezones WHERE is_approved = 1");
if (!$stmt) {
    die("Prepare failed for safezones: (" . $conn->errno . ") " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $safezones[] = $row;
}
$stmt->close();

// Fetch available open tasks
$tasks = [];
$sql = "SELECT t.id, t.contractor_id, t.title, t.description, t.pay AS price, t.category, t.address AS location, t.created_at, 
               t.task_type, t.safezone_id, t.magic_word,
               CONCAT(u.first_name, ' ', u.last_name) AS poster_name, u.profile_picture 
        FROM tasks t
        JOIN users u ON t.contractor_id = u.id
        WHERE t.status != 'completed' AND t.status != 'cancelled'
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
} else {
    die("Query failed for open tasks: (" . $conn->errno . ") " . $conn->error);
}

// Get all task_ids the user has applied for
$user_applied_task_ids = [];
$stmt = $conn->prepare("SELECT task_id, application_status FROM task_applications WHERE helper_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_applied_task_ids[$row['task_id']] = $row['application_status'];
}
$stmt->close();

// Accept a task (user applies to a task)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_task_id'])) {
    $task_id = $_POST['apply_task_id'];
    $application_message = isset($_POST['application_message']) ? trim($_POST['application_message']) : null;

    // Insert into task_applications (no message field)
    $stmt = $conn->prepare("INSERT INTO task_applications (task_id, helper_id, application_status, applied_at, status) VALUES (?, ?, 'pending', NOW(), 'active')");
    if ($stmt === false) {
        die("Prepare failed for applying task: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param('ss', $task_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Insert the message if provided
    if ($application_message) {
        $stmt = $conn->prepare("INSERT INTO messages (task_id, sender_id, content, message_type, created_at) VALUES (?, ?, ?, 'application', NOW())");
        if ($stmt === false) {
            die("Prepare failed for message: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param('sss', $task_id, $user_id, $application_message);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['success_message'] = 'Task application submitted successfully.';
    header('Location: mytasks.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - HatidGawa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/gabay.css">
    <link rel="icon" href="assets/images/logo.svg">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
    <!-- Loading Overlay -->
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
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-hands-helping"></i>
                    HatidGawa
                </a>
                
                <div class="navbar-actions" id="auth-nav">
                    <a href="dashboard.php" class="btn btn-primary">Go Back</a>
                </div>
                
                <div class="navbar-actions" id="user-nav" style="display: none;">
                    <div class="dropdown">
                        <button class="btn btn-ghost dropdown-toggle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm" id="user-avatar">
                                    <img src="https://randomuser.me/api/portraits/women/12.jpg" alt="User">
                                </div>
                                <span id="username"></span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </button>
                        <div class="dropdown-menu">
                            <a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="tasks-user.php" class="dropdown-item">
                                <i class="fas fa-tasks"></i>
                                <span>My Tasks</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="auth/logout.php" class="dropdown-item" id="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-icon btn-ghost notification-badge" id="notification-badge">
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="dropdown-menu">
                            <div class="p-3 border-bottom">
                                <h5 class="m-0">Notifications</h5>
                            </div>
                            <div id="notification-list">
                                <!-- Notifications will be populated here -->
                            </div>
                            <div class="p-2 text-center border-top">
                                <a href="dashboard.php" class="btn btn-sm btn-outline w-100">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <div class="mobile-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="tasks.php" class="nav-link active">Tasks</a>
                <a href="index.php#how-it-works" class="nav-link">How It Works</a>
                <div class="mt-4" id="mobile-auth-nav">
                    <a href="login.php" class="btn btn-outline w-100 mb-2">Log In</a>
                    <a href="register.php" class="btn btn-primary w-100">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Tasks Hero Section -->
    <section class="hero hero-sm  text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="hero-content">
                        <h1>Browse Tasks</h1>
                        <p>Find tasks that need help or post your own task to get assistance from the community.</p>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="fas fa-plus me-2"></i>Post New Task
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tasks Section -->
<!-- Tasks Section -->
<section class="py-6">
  <div class="container">
    <div class="row">

      <!-- Filter Sidebar -->
      <div class="col-lg-3 mb-4">
        <div class="card sticky-top" style="top: 10px;">
          <div class="card-header">
            <h4 class="card-title mb-0">Filters</h4>
          </div>
          <div class="card-body">
            <form method="GET" id="filter-form">
              <div class="form-group mb-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search tasks...">
              </div>

              <div class="form-group mb-3">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" class="form-control">
                  <option value="">All Categories</option>
                  <option value="delivery" <?php if (isset($_GET['category']) && $_GET['category'] == 'delivery') echo 'selected'; ?>>Delivery</option>
                  <option value="repairs" <?php if (isset($_GET['category']) && $_GET['category'] == 'repairs') echo 'selected'; ?>>Home Repairs</option>
                  <option value="tutoring" <?php if (isset($_GET['category']) && $_GET['category'] == 'tutoring') echo 'selected'; ?>>Tutoring</option>
                  <option value="cleaning" <?php if (isset($_GET['category']) && $_GET['category'] == 'cleaning') echo 'selected'; ?>>Cleaning</option>
                  <option value="errands" <?php if (isset($_GET['category']) && $_GET['category'] == 'errands') echo 'selected'; ?>>Errands</option>
                </select>
              </div>

              <div class="form-group mb-3">
                <label for="location" class="form-label">Location</label>
                <select name="location" id="location" class="form-control">
                  <option value="">All Locations</option>
                  <option value="Mandaluyong City" <?php if (isset($_GET['location']) && $_GET['location'] == 'Mandaluyong City') echo 'selected'; ?>>Mandaluyong City</option>
                  <option value="Makati City" <?php if (isset($_GET['location']) && $_GET['location'] == 'Makati City') echo 'selected'; ?>>Makati City</option>
                  <option value="Quezon City" <?php if (isset($_GET['location']) && $_GET['location'] == 'Quezon City') echo 'selected'; ?>>Quezon City</option>
                  <option value="Taguig City" <?php if (isset($_GET['location']) && $_GET['location'] == 'Taguig City') echo 'selected'; ?>>Taguig City</option>
                </select>
              </div>

              <div class="form-group mb-3">
                <label class="form-label">Price Range</label>
                <div class="d-flex align-items-center gap-2">
                  <input type="number" name="price_min" class="form-control" placeholder="Min" value="<?php echo isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : ''; ?>">
                  <span>-</span>
                  <input type="number" name="price_max" class="form-control" placeholder="Max" value="<?php echo isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : ''; ?>">
                </div>
              </div>

              <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="tasks.php" class="btn btn-outline">Reset</a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="mb-0">Available Tasks</h3>
            <p class="text-gray mb-0">Showing <span id="task-count"><?php echo count($tasks); ?></span> tasks</p>
          </div>
        </div>

        <div class="row" id="tasks-container">
          <?php
          // Filtering Logic
          $filtered_tasks = $tasks;

          if (!empty($_GET['search'])) {
              $search = strtolower(trim($_GET['search']));
              $filtered_tasks = array_filter($filtered_tasks, function($task) use ($search) {
                  return strpos(strtolower($task['title']), $search) !== false || strpos(strtolower($task['description']), $search) !== false;
              });
          }

          if (!empty($_GET['category'])) {
              $category = strtolower($_GET['category']);
              $filtered_tasks = array_filter($filtered_tasks, function($task) use ($category) {
                  return strtolower($task['category']) == $category;
              });
          }

          if (!empty($_GET['location'])) {
              $location = strtolower($_GET['location']);
              $filtered_tasks = array_filter($filtered_tasks, function($task) use ($location) {
                  return strtolower($task['location']) == $location;
              });
          }

          if (!empty($_GET['price_min']) || !empty($_GET['price_max'])) {
              $price_min = !empty($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
              $price_max = !empty($_GET['price_max']) ? floatval($_GET['price_max']) : PHP_INT_MAX;
              $filtered_tasks = array_filter($filtered_tasks, function($task) use ($price_min, $price_max) {
                  return $task['price'] >= $price_min && $task['price'] <= $price_max;
              });
          }
          ?>

          <?php if (!empty($filtered_tasks)): ?>
              <?php foreach ($filtered_tasks as $task): ?>
                  <div class="col-md-6 mb-4">
                      <div class="card h-100">
                          <div class="card-body">
                              <div class="d-flex justify-content-between align-items-start mb-3">
                                  <h3 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                  <span class="badge badge-primary">₱<?php echo number_format($task['price'], 2); ?></span>
                              </div>
                              <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                              <div class="task-meta mt-4">
                                  <div class="task-meta-item">
                                      <i class="fas fa-tag"></i>
                                      <span><?php echo htmlspecialchars($task['category']); ?></span>
                                  </div>
                                  <div class="task-meta-item">
                                      <i class="fas fa-map-marker-alt"></i>
                                      <span><?php echo htmlspecialchars($task['location']); ?></span>
                                  </div>
                                  <div class="task-meta-item">
                                      <i class="fas fa-calendar-alt"></i>
                                      <span><?php echo date('F j, Y', strtotime($task['created_at'])); ?></span>
                                  </div>
                              </div>
                              <?php if (isset($task['task_type']) && $task['task_type'] === 'safezone' && !empty($task['safezone_id'])): ?>
                                  <div class="mb-2" style="height:200px;">
                                      <div id="safezone-map-<?php echo htmlspecialchars($task['id']); ?>" style="height:100%;border-radius:8px;border:1px solid #ccc;"></div>
                                  </div>
                              <?php endif; ?>
                          </div>
                          <div class="card-footer d-flex justify-content-between align-items-center">
                              <div class="d-flex align-items-center">
                                  <div class="avatar avatar-sm me-2">
                                      <img src="<?php echo !empty($task['profile_image']) ? htmlspecialchars($task['profile_image']) : 'default-avatar.png'; ?>" alt="Task Poster" style="width:40px;height:40px;border-radius:50%;">
                                  </div>
                                  <span><?php echo htmlspecialchars($task['poster_name']); ?></span>
                              </div>
                              <?php if (isset($task['contractor_id']) && $task['contractor_id'] == $user_id): ?>
                                  <span class="badge bg-secondary">Your Task</span>
                              <?php elseif (isset($user_applied_task_ids[$task['id']])): ?>
                                  <?php if ($user_applied_task_ids[$task['id']] == 'pending'): ?>
                                      <span class="badge bg-info">Application Pending</span>
                                      <form method="POST" action="cancel_application.php" class="d-inline">
                                          <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                          <button type="submit" class="btn btn-sm btn-outline-danger ms-2">Cancel</button>
                                      </form>
                                  <?php elseif ($user_applied_task_ids[$task['id']] == 'accepted'): ?>
                                      <span class="badge bg-success">Accepted</span>
                                  <?php elseif ($user_applied_task_ids[$task['id']] == 'rejected'): ?>
                                      <span class="badge bg-danger">Rejected</span>
                                  <?php endif; ?>
                              <?php else: ?>
                                <button type="button"
    class="btn btn-primary btn-sm"
    onclick="showApplyModal('<?= htmlspecialchars($task['id']) ?>', <?= htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8') ?>)">
    Apply
</button> 
                                  
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          <?php else: ?>
              <div class="col-12">
                  <div class="alert alert-info">No tasks found.</div>
              </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>
            </div>
        </div>
    </div>
</section>

            <!-- My Tasks Content -->
            <div class="dashboard-content">

                
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
                
              
    <!-- Create Task Modal -->
    <div class="modal " id="createTaskModal" tabindex="-1" aria-hidden="true" >
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
                                value="<?= htmlspecialchars($user['address']) ?>">
                        </div>
                        
                        <div class="mb-3 d-none" id="safezoneField">
                            <label class="form-label">Safe Zone</label>
                            <select name="safezone_id" class="form-select">
                                <?php foreach ($safezones as $zone): ?>
                                    <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_task" class="btn btn-primary">Create Task</button>
                    </div>
                </form>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-5">
                    <div class="footer-brand">
                        <i class="fas fa-hands-helping"></i>
                        HatidGawa
                    </div>
                    <p class="text-gray-light mb-4">A community-powered task sharing platform that connects neighbors for everyday help.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-icon btn-ghost">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-heading">Platform</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="tasks.php">Browse Tasks</a></li>
                        <li><a href="index.php#how-it-works">How It Works</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 col-6 mb-4">
                    <h5 class="footer-heading">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Tips</a></li>
                        <li><a href="#">Community Guidelines</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="footer-heading">Stay Updated</h5>
                    <p class="text-gray-light mb-3">Subscribe to our newsletter for the latest updates and features.</p>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 HatidGawa. All rights reserved.</p>
            </div>
        </div>
    </footer>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Toggle address/safezone fields based on task type
        document.getElementById('taskTypeSelect').addEventListener('change', function() {
            const taskType = this.value;
            const addressField = document.getElementById('addressField');
            const safezoneField = document.getElementById('safezoneField');
            
            if (taskType === 'safezone') {
                addressField.classList.add('d-none');
                safezoneField.classList.remove('d-none');
                document.querySelector('[name="address"]').required = false;
                document.querySelector('[name="safezone_id"]').required = true;
            } else {
                addressField.classList.remove('d-none');
                safezoneField.classList.add('d-none');
                document.querySelector('[name="address"]').required = true;
                document.querySelector('[name="safezone_id"]').required = false;
            }
        });
    </script>
    <script>


        // Task Detail Modal
        document.querySelectorAll('.view-task-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const taskId = this.getAttribute('data-task-id');
                // In a real app, you would fetch task details from the server
                // For now, we'll just show the modal
                const modal = document.getElementById('task-detail-modal');
                modal.classList.add('show');
                modal.style.display = 'block';
            });
        });

        // Post Task Modal
        document.getElementById('post-task-btn').addEventListener('click', function() {
            const modal = document.getElementById('post-task-modal');
            modal.classList.add('show');
            modal.style.display = 'block';
        });

        // Close Modals
        document.querySelectorAll('.btn-close, [data-dismiss="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                modal.classList.remove('show');
                modal.style.display = 'none';
            });
        });

        // Submit Task Form
        document.getElementById('submit-task-btn').addEventListener('click', function() {
            alert('Task posted successfully! In a real app, this would save the task to the database.');
            const modal = document.getElementById('post-task-modal');
            modal.classList.remove('show');
            modal.style.display = 'none';
        });

        // Apply for Task
        document.getElementById('apply-task-btn').addEventListener('click', function() {
            alert('Application submitted! In a real app, this would notify the task poster.');
        });
        // View Task Details
function viewTaskDetails(taskId) {
    fetch(`/api/task_operations.php?task_id=${taskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            // Populate the modal with task data
            document.getElementById('task-detail-title').textContent = data.task.title;
            document.getElementById('task-detail-name').textContent = data.task.title;
            document.getElementById('task-detail-price').textContent = `₱${data.task.pay}`;
            document.getElementById('task-detail-category').textContent = data.task.category;
            document.getElementById('task-detail-location').textContent = data.task.address;
            document.getElementById('task-detail-description').textContent = data.task.description;
            document.getElementById('task-detail-meeting').textContent = data.task.meeting_location;
            document.getElementById('task-detail-status').textContent = data.task.status;
            document.getElementById('task-detail-date-posted').textContent = data.task.created_at;
            document.getElementById('task-detail-poster-name').textContent = data.contractor.name;
            document.getElementById('task-detail-poster-avatar').innerHTML = 
                `<img src="${data.contractor.photo}" alt="Task Poster">`;
            
            // Show the modal
            $('#task-detail-modal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load task details');
        });
}

</script>
<script>
document.getElementById('post-task-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('task_title', document.getElementById('task-title').value);
    formData.append('task_category', document.getElementById('task-category').value);
    formData.append('task_price', document.getElementById('task-price').value);
    formData.append('task_description', document.getElementById('task-description').value);
    formData.append('task_requirements', document.getElementById('task-requirements').value);
    formData.append('task_location', document.getElementById('task-location').value);
    formData.append('task_due_date', document.getElementById('task-due-date').value);
    formData.append('task_estimated_time', document.getElementById('task-estimated-time').value);

    fetch('submit_task.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Refresh to show the new task
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>
<!-- Apply Confirmation Modal -->
<div class="modal fade" id="applyTaskModal" tabindex="-1" aria-labelledby="applyTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="applyTaskForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="applyTaskModalLabel">Apply for Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="apply_task_id" id="apply_task_id">
          <!-- Task Details Section -->
          <div id="apply-task-details" class="mb-3">
            <!-- Task details will be injected here by JS -->
          </div>
          <div class="mb-3">
            <label for="application_message" class="form-label">Message to Contractor (optional)</label>
            <textarea class="form-control" name="application_message" id="application_message" rows="3"></textarea>
          </div>
          <p>Are you sure you want to apply for this task?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Confirm Apply</button>
        </div>
      </div>
    </form>
  </div>
</div> 



<script>
let currentTaskForPdf = null;

function showApplyModal(taskId, taskData) {
    document.getElementById('apply_task_id').value = taskId;
    document.getElementById('application_message').value = '';

    // Enhanced task details layout
    let detailsHtml = `
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title mb-2"><i class="fas fa-tasks me-2 text-primary"></i>${taskData.title}</h5>
                <p class="mb-2"><i class="fas fa-align-left me-2 text-secondary"></i>${taskData.description}</p>
                <ul class="list-group list-group-flush mb-2">
                    <li class="list-group-item px-0"><i class="fas fa-tag me-2 text-info"></i><strong>Category:</strong> ${taskData.category}</li>
                    <li class="list-group-item px-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i><strong>Location:</strong> ${taskData.location || 'N/A'}</li>
                    <li class="list-group-item px-0"><i class="fas fa-calendar-alt me-2 text-warning"></i><strong>Date Posted:</strong> ${taskData.created_at}</li>
                    <li class="list-group-item px-0"><i class="fas fa-money-bill-wave me-2 text-success"></i><strong>Budget:</strong> ₱${taskData.price ? Number(taskData.price).toLocaleString(undefined, {minimumFractionDigits:2}) : 'None'}</li>
                </ul>
            </div>
        </div>
    `;
    document.getElementById('apply-task-details').innerHTML = detailsHtml;

    // Store for PDF (no longer needed, but harmless if left)
    currentTaskForPdf = taskData;

    var modal = new bootstrap.Modal(document.getElementById('applyTaskModal'));
    modal.show();
}
</script> 

<script>
const safezonesData = <?php echo json_encode($safezones); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // For each task card with a safezone map
    <?php foreach ($filtered_tasks as $task): ?>
        <?php if (isset($task['task_type']) && $task['task_type'] === 'safezone' && !empty($task['safezone_id'])): ?>
            (function() {
                // Find the safezone by ID
                const sz = safezonesData.find(z => z.id == <?= json_encode($task['safezone_id']) ?>);
                if (sz && sz.latitude && sz.longitude) {
                    const mapId = 'safezone-map-<?= htmlspecialchars($task['id']) ?>';
                    const map = L.map(mapId, {scrollWheelZoom: false, zoomControl: false, dragging: false}).setView([sz.latitude, sz.longitude], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    L.marker([sz.latitude, sz.longitude]).addTo(map)
                        .bindPopup(`<strong>${sz.name}</strong><br>${sz.location_description}<br>Barangay: ${sz.barangay}`);
                    setTimeout(() => { map.invalidateSize(); }, 200);
                }
            })();
        <?php endif; ?>
    <?php endforeach; ?>
});
</script>

</body>
</html>