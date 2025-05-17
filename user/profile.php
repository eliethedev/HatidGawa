<?php
require_once('../auth/db.php');
session_start();

if (isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
} else {
    $user_id = null;
}

// Determine which profile to show
if (isset($_GET['id'])) {
    $profile_id = $_GET['id'];
} else if (isset($_SESSION['user']['id'])) {
    $profile_id = $_SESSION['user']['id'];
} else {
    header('Location: ../login.php');
    exit();
}

// Fetch message count for the current user
$message_count = 0;
if ($user_id) {
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
}

// Fetch user info
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, role, barangay, address, profile_picture, verified, verification_status, created_at, skills, about, barangay_id_path FROM users WHERE id = ?");
$stmt->bind_param("s", $profile_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<h2>User not found.</h2>";
    exit();
}

// Fetch average rating and total reviews
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM ratings WHERE ratee_id = ?");
$stmt->bind_param("s", $profile_id);
$stmt->execute();
$stmt->bind_result($avg_rating, $total_reviews);
$stmt->fetch();
$stmt->close();
$avg_rating = $avg_rating ? round($avg_rating, 2) : 0;

// Fetch reviews (latest 10)
$stmt = $conn->prepare("SELECT r.rating, r.feedback, r.created_at, u.first_name, u.last_name, u.profile_picture, t.title FROM ratings r JOIN users u ON r.rater_id = u.id LEFT JOIN tasks t ON r.task_id = t.id WHERE r.ratee_id = ? ORDER BY r.created_at DESC LIMIT 10");
$stmt->bind_param("s", $profile_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch task history (as helper)
$stmt = $conn->prepare("SELECT t.title, t.category, t.address, t.created_at, t.status, t.pay, 'Helper' as role FROM tasks t JOIN task_applications ta ON t.id = ta.task_id WHERE ta.helper_id = ? AND ta.status = 'accepted' ORDER BY t.created_at DESC LIMIT 10");
$stmt->bind_param("s", $profile_id);
$stmt->execute();
$tasks_helper = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch task history (as poster)
$stmt = $conn->prepare("SELECT t.title, t.category, t.address, t.created_at, t.status, t.pay, 'Poster' as role FROM tasks t WHERE t.contractor_id = ? ORDER BY t.created_at DESC LIMIT 10");
$stmt->bind_param("s", $profile_id);
$stmt->execute();
$tasks_poster = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Merge and sort tasks by date
$all_tasks = array_merge($tasks_helper, $tasks_poster);
usort($all_tasks, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Count completed tasks
$completed_tasks = array_filter($all_tasks, function($t) { return strtolower($t['status']) == 'completed'; });
$completed_count = count($completed_tasks);

// Format join date
$member_since = date('F Y', strtotime($user['created_at']));

// Avatar fallback
$avatar = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'https://randomuser.me/api/portraits/lego/1.jpg';

$skills = [];
if (!empty($user['skills'])) {
    $skills = array_map('trim', explode(',', $user['skills']));
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

function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'accepted': return 'primary';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        case 'applied': return 'info';
        case 'rejected': return 'secondary';
        default: return 'light';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_skills']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id) {
    $skills = trim($_POST['skills']);
    $stmt = $conn->prepare("UPDATE users SET skills = ? WHERE id = ?");
    $stmt->bind_param("ss", $skills, $profile_id);
    $success = $stmt->execute();
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'skills' => $skills]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $about = trim($_POST['about']);

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, address=?, about=? WHERE id=?");
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $address, $about, $profile_id);
    $success = $stmt->execute();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'about' => $about
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_avatar']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id) {
    $response = ['success' => false, 'message' => ''];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = basename($_FILES['avatar']['name']);
        $fileSize = $_FILES['avatar']['size'];
        $fileType = mime_content_type($fileTmp);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($fileType, $allowedTypes)) {
            $response['message'] = 'Invalid file type.';
        } elseif ($fileSize > $maxSize) {
            $response['message'] = 'File is too large.';
        } else {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'profile_' . $profile_id . '_' . time() . '.' . $ext;
            $uploadDir = '../uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Save relative path to DB
                $relativePath = 'uploads/profile_pictures/' . $newFileName;
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("ss", $relativePath, $profile_id);
                $success = $stmt->execute();
                $stmt->close();
                if ($success) {
                    $response['success'] = true;
                    $response['avatar'] = $relativePath;
                } else {
                    $response['message'] = 'Failed to update profile picture in database.';
                }
            } else {
                $response['message'] = 'Failed to upload file.';
            }
        }
    } else {
        $response['message'] = 'No file uploaded or upload error.';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_barangay_id']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id) {
    $response = ['success' => false, 'message' => ''];
    if (isset($_FILES['barangay_id']) && $_FILES['barangay_id']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['barangay_id']['tmp_name'];
        $fileName = basename($_FILES['barangay_id']['name']);
        $fileSize = $_FILES['barangay_id']['size'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($fileType, $allowedTypes)) {
            $response['message'] = 'Invalid file type.';
        } elseif ($fileSize > $maxSize) {
            $response['message'] = 'File is too large.';
        } elseif ($fileSize < 10 * 1024) {
            $response['message'] = 'File is too small or invalid.';
        } else {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'barangay_id_' . $profile_id . '_' . time() . '.' . $ext;
            $uploadDir = '../uploads/barangay_ids/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Save relative path to DB
                $relativePath = 'uploads/barangay_ids/' . $newFileName;
                $stmt = $conn->prepare("UPDATE users SET barangay_id_path = ? WHERE id = ?");
                $stmt->bind_param("ss", $relativePath, $profile_id);
                $success = $stmt->execute();
                $stmt->close();
                if ($success) {
                    $response['success'] = true;
                    $response['barangay_id_path'] = $relativePath;
                } else {
                    $response['message'] = 'Failed to update ID in database.';
                }
            } else {
                $response['message'] = 'Failed to upload file.';
            }
        }
    } else {
        $response['message'] = 'No file uploaded or upload error.';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Calculate total earned from completed tasks
$total_earned = 0;
foreach ($all_tasks as $task) {
    if (strtolower($task['status']) == 'completed' && is_numeric($task['pay'])) {
        $total_earned += $task['pay'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HatidGawa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/gabay.css">
    <link rel="icon" href="assets/images/logo.svg">
    
</head>
<body>
    <!-- Loading Overlay -->


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
                    <p class="text-primary">Community Member</p>
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
                <li class="sidebar-nav-item">
                    <a href="mytasks.php" class="sidebar-nav-link" id="my-tasks-link">
                        <i class="fas fa-clipboard-list"></i>
                        <span>My Tasks</span>
                    </a>
                </li>
                <li class="sidebar-nav-item active">
                    <a href="profile.php" class="sidebar-nav-link">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="messages.php" class="sidebar-nav-link">
                        <i class="fas fa-comment-alt"></i>
                        <span>Messages</span>
                        <?php if ($user_id && $message_count > 0): ?>
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
                        <h1 class="page-title mb-0">My Profile</h1>
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
            
            <!-- Profile Content -->
            <div class="dashboard-content">
                <!-- Profile Header -->
                <div class="profile-header mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 text-center text-md-start mb-4 mb-md-0">
                                <div class="avatar avatar-xl mx-auto mx-md-0">
                                    <img src="<?= !empty($user['profile_picture']) 
                                        ? (strpos($user['profile_picture'], 'http') === 0 
                                            ? htmlspecialchars($user['profile_picture']) 
                                            : '../' . htmlspecialchars($user['profile_picture'])) 
                                        : '../assets/images/default-avatar.png' ?>" 
                                        alt="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>" 
                                        style="object-fit: cover; width: 100%; height: 100%; border-radius: 50%;">
                                    
                                    <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id): ?>
                                        <button class="avatar-edit-btn" id="change-avatar-btn">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                </div>
                                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                                    <h2 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                                    <p class="text-gray mb-2">Member since <?= $member_since ?></p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                                        <div class="badge badge-light">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= htmlspecialchars($user['address']) ?>
                                        </div>
                                        <div class="badge badge-light">
                                            <i class="fas fa-tasks me-1"></i>
                                            <?= $completed_count ?> Tasks Completed
                                        </div>
                                        <div class="badge badge-light">
                                            <i class="fas fa-shield-alt me-1 text-success"></i>
                                            <?= $user['verified'] ? 'Verified Member' : 'Unverified' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center text-md-end">
                                    <div class="rating-large mb-2">
                                        <?php
                                        $fullStars = floor($avg_rating);
                                        $halfStar = ($avg_rating - $fullStars) >= 0.5;
                                        for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star text-warning"></i>';
                                        if ($halfStar) echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                        for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '<i class="far fa-star text-warning"></i>';
                                        ?>
                                        <span class="rating-value"><?= $avg_rating ?></span>
                                    </div>
                                    <p class="text-gray mb-3">Based on <?= $total_reviews ?> reviews</p>
                                    <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id): ?>
                                    <button class="btn btn-primary" id="edit-profile-btn">
                                        <i class="fas fa-edit me-2"></i>
                                        Edit Profile
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Content -->
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-4 mb-4">
                        <!-- About Me -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">About Me</h5>
                            </div>
                            <div class="card-body">
                                <div class="mt-4">
                                    <p><?php echo htmlspecialchars(($user['about']));?></p>
                                    <h6 class="mb-2">Contact Information</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        <span><?php echo htmlspecialchars($user['address']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Skills -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Skills</h5>
                                <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id): ?>
                                    <button class="btn btn-sm btn-ghost" id="edit-skills-btn">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($skills)): ?>
                                    <span class="text-muted">No skills listed.</span>
                                <?php else: ?>
                                    <?php foreach ($skills as $skill): ?>
                                        <div class="skill-tag"><?= htmlspecialchars($skill) ?></div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Availability -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Availability</h5>
                                <button class="btn btn-sm btn-ghost" id="edit-availability-btn">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            
                        </div>

                        <!-- Barangay/Valid ID Upload Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Barangay/Valid ID</h5>
                                <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $profile_id): ?>
                                    <button class="btn btn-sm btn-ghost" id="upload-id-btn">
                                        <i class="fas fa-upload"></i> <?= empty($user['barangay_id_path']) ? 'Upload' : 'Update' ?> ID
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body text-center">
                                <?php
                                $barangay_id = trim($user['barangay_id_path'] ?? '');
                                if ($barangay_id):
                                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $barangay_id)): ?>
                                        <img src="../<?= htmlspecialchars($barangay_id) ?>" alt="Barangay/Valid ID" class="img-fluid rounded mb-2" style="max-width:300px;">
                                    <?php else: ?>
                                        <a href="../<?= htmlspecialchars($barangay_id) ?>" target="_blank" class="btn btn-outline-primary btn-sm mb-2">View Uploaded ID</a>
                                    <?php endif;
                                else: ?>
                                    <span class="text-muted">No Barangay/Valid ID uploaded yet.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-lg-8">
                        <!-- Reviews -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Reviews</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline active">All</button>
                                    <!-- You can add filter logic for "As Helper" and "As Poster" if you want -->
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="review-list">
                                    <?php if (empty($reviews)): ?>
                                        <div class="p-4 text-center text-muted">No reviews yet.</div>
                                    <?php else: foreach ($reviews as $review): ?>
                                        <div class="review-item">
                                            <div class="d-flex">
                                                <div class="avatar me-3">
                                                    <img src="<?= !empty($review['profile_picture']) ? htmlspecialchars($review['profile_picture']) : 'https://randomuser.me/api/portraits/lego/2.jpg' ?>" alt="<?= htmlspecialchars($review['first_name']) ?>">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h6 class="mb-0"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></h6>
                                                        <div class="rating">
                                                            <?php
                                                            for ($i = 0; $i < $review['rating']; $i++) echo '<i class="fas fa-star text-warning"></i>';
                                                            for ($i = $review['rating']; $i < 5; $i++) echo '<i class="far fa-star text-warning"></i>';
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <p class="text-sm text-gray mb-2">
                                                        For: <?= htmlspecialchars($review['title'] ?? 'Task') ?> • <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                                    </p>
                                                    <p class="mb-0"><?= htmlspecialchars($review['feedback']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="#" class="btn btn-outline">View All Reviews</a>
                            </div>
                        </div>
                        
                        <!-- Task History -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Task History</h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline active">All</button>
                                    <!-- You can add filter logic for "As Helper" and "As Poster" if you want -->
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Role</th>
                                                <th>Date</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($all_tasks)): ?>
                                                <tr><td colspan="5" class="text-center text-muted">No task history yet.</td></tr>
                                            <?php else: foreach ($all_tasks as $task): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="task-icon bg-primary-light text-primary me-3">
                                                                <i class="fas fa-<?= htmlspecialchars(getTaskIcon($task['category'])) ?>"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                                <span class="text-sm text-gray"><?= htmlspecialchars($task['address']) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($task['role']) ?></td>
                                                    <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                                                    <td><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></td>
                                                    <td>
                                                        <?php if (strtolower($task['status']) == 'completed'): ?>
                                                            <span class="badge bg-success">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?= ucfirst($task['status']) ?></span>
                                                        <?php endif; ?>
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

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="edit-profile-modal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="edit-profile-form" class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editProfileModalLabel">Edit Profile</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit-first-name" class="form-label">First Name</label>
                            <input type="text" id="edit-first-name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-last-name" class="form-label">Last Name</label>
                            <input type="text" id="edit-last-name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-email" class="form-label">Email Address</label>
                            <input type="email" id="edit-email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-phone" class="form-label">Phone Number</label>
                            <input type="tel" id="edit-phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="edit-address" class="form-label">Address</label>
                            <input type="text" id="edit-address" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                        </div>
                        <div class="col-12">
                            <label for="edit-about" class="form-label">About Me</label>
                            <textarea id="edit-about" name="about" class="form-control" rows="4"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

<!-- Edit Skills Modal -->
<div class="modal fade" id="edit-skills-modal" tabindex="-1" aria-labelledby="editSkillsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="edit-skills-form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSkillsModalLabel">Edit Skills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="skills-input" class="form-label">Enter skills (comma separated):</label>
                <input type="text" class="form-control" id="skills-input" name="skills"
                       value="<?= htmlspecialchars(implode(', ', $skills)) ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Skills</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Profile Picture Modal -->
<div class="modal fade" id="change-avatar-modal" tabindex="-1" aria-labelledby="changeAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="change-avatar-form" class="modal-content" enctype="multipart/form-data" method="post">
            <div class="modal-header">
                <h5 class="modal-title" id="changeAvatarModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <img id="avatar-preview" src="<?= !empty($user['profile_picture']) 
                        ? (strpos($user['profile_picture'], 'http') === 0 
                            ? htmlspecialchars($user['profile_picture']) 
                            : '../' . htmlspecialchars($user['profile_picture'])) 
                        : '../assets/images/default-avatar.png' ?>" 
                        alt="Current Avatar" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                <div class="mb-3">
                    <label for="avatar-file" class="form-label">Select a new profile picture</label>
                    <input type="file" class="form-control" id="avatar-file" name="avatar" accept="image/*" required>
                </div>
                <div class="form-text text-center">Max size: 2MB. Allowed: JPG, PNG, GIF.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="upload-id-modal" tabindex="-1" aria-labelledby="uploadIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="upload-id-form" class="modal-content" enctype="multipart/form-data" method="post">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadIdModalLabel">Upload Barangay/Valid ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <?php if (!empty($user['barangay_id_path'])): ?>
                        <img id="id-preview" src="../<?= htmlspecialchars($user['barangay_id_path']) ?>" alt="Current ID" class="rounded" style="max-width: 200px;">
                    <?php else: ?>
                        <img id="id-preview" src="https://via.placeholder.com/200x120?text=No+ID" alt="No ID" class="rounded" style="max-width: 200px;">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="barangay-id-file" class="form-label">Select a valid ID (JPG, PNG, GIF, PDF, WEBP, max 5MB)</label>
                    <input type="file" class="form-control" id="barangay-id-file" name="barangay_id" accept="image/*,application/pdf" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded. Please include Bootstrap JS.');
        return;
    }
    
    console.log('Bootstrap version:', bootstrap.Modal.VERSION); // Debug info
    
    // Function to manually close and clean up a modal
    function closeAndCleanupModal(modalId) {
        // Get the modal element
        const modalElement = document.getElementById(modalId);
        if (!modalElement) return;
        
        // Get the Bootstrap modal instance
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            // Hide the modal
            modalInstance.hide();
        }
        
        // Remove modal and backdrop after a short delay
        setTimeout(() => {
            // Remove the modal element
            if (modalElement) modalElement.remove();
            
            // Remove any modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            
            // Reset body state
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 300);
    }
    
    // Edit Profile Modal handler
    const editProfileBtn = document.getElementById('edit-profile-btn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Edit profile button clicked'); // Debug info
            
            // Check if modal already exists in the DOM
            let profileModal = document.getElementById('edit-profile-modal');
            
            // If it doesn't exist, add it to the DOM
            if (!profileModal) {
                // Get the modal HTML from the template
                const modalTemplate = `
                <div class="modal fade" id="edit-profile-modal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <form id="edit-profile-form" class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="editProfileModalLabel">Edit Profile</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edit-first-name" class="form-label">First Name</label>
                                        <input type="text" id="edit-first-name" name="first_name" class="form-control" value="${document.querySelector('.profile-header h2').textContent.trim()}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-last-name" class="form-label">Last Name</label>
                                        <input type="text" id="edit-last-name" name="last_name" class="form-control" value="" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-email" class="form-label">Email Address</label>
                                        <input type="email" id="edit-email" name="email" class="form-control" value="${document.querySelector('.card-body .fa-envelope').nextElementSibling.textContent.trim()}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="edit-phone" class="form-label">Phone Number</label>
                                        <input type="tel" id="edit-phone" name="phone" class="form-control" value="${document.querySelector('.card-body .fa-phone').nextElementSibling.textContent.trim()}" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="edit-address" class="form-label">Address</label>
                                        <input type="text" id="edit-address" name="address" class="form-control" value="${document.querySelector('.card-body .fa-map-marker-alt').nextElementSibling.textContent.trim()}">
                                    </div>
                                    <div class="col-12">
                                        <label for="edit-about" class="form-label">About Me</label>
                                        <textarea id="edit-about" name="about" class="form-control" rows="4">${document.querySelector('.card-body p').textContent.trim()}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>`;
                
                // Add the modal to the DOM
                document.body.insertAdjacentHTML('beforeend', modalTemplate);
                profileModal = document.getElementById('edit-profile-modal');
            }
            
            // Initialize the modal
            const editProfileModal = new bootstrap.Modal(profileModal);
            
            // Show the modal
            editProfileModal.show();
            
            // Set up the form submission handler
            const editProfileForm = document.getElementById('edit-profile-form');
            if (editProfileForm) {
                // Remove any existing event listeners
                const newForm = editProfileForm.cloneNode(true);
                editProfileForm.parentNode.replaceChild(newForm, editProfileForm);
                
                // Add the event listener to the new form
                newForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submitted'); // Debug info
                    
                    // Create form data
                    const formData = new FormData(newForm);
                    formData.append('update_profile', 1);
                    
                    // Manually close the modal BEFORE the fetch
                    editProfileModal.hide();
                    
                    // Send the form data
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI fields
                            document.querySelector('.profile-header h2').textContent = data.first_name + ' ' + data.last_name;
                            document.querySelectorAll('#sidebar-user-name, #header-user-name').forEach(el => {
                                if (el) el.textContent = data.first_name + ' ' + data.last_name;
                            });
                            
                            // Update address badge
                            const addressBadge = document.querySelector('.profile-header .badge-light i.fa-map-marker-alt');
                            if (addressBadge && addressBadge.parentNode) {
                                addressBadge.parentNode.innerHTML = '<i class="fas fa-map-marker-alt me-1"></i> ' + data.address;
                            }
                            
                            // Update about section
                            const aboutParagraph = document.querySelector('.card-body p');
                            if (aboutParagraph) {
                                aboutParagraph.textContent = data.about;
                            }
                            
                            // Update contact info
                            document.querySelectorAll('.card-body span').forEach(el => {
                                if (el.previousElementSibling && el.previousElementSibling.classList.contains('fa-envelope')) {
                                    el.textContent = data.email;
                                }
                                if (el.previousElementSibling && el.previousElementSibling.classList.contains('fa-phone')) {
                                    el.textContent = data.phone;
                                }
                                if (el.previousElementSibling && el.previousElementSibling.classList.contains('fa-map-marker-alt')) {
                                    el.textContent = data.address;
                                }
                            });
                            
                            // Remove the modal from the DOM
                            setTimeout(() => {
                                profileModal.remove();
                                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, 300);
                            
                            // Show success message
                            alert('Profile updated successfully!');
                        } else {
                            alert('Failed to update profile: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the profile.');
                    });
                });
            }
        });
    }

    // Edit Skills Modal handler
    const editSkillsBtn = document.getElementById('edit-skills-btn');
    if (editSkillsBtn) {
        editSkillsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Edit skills button clicked'); // Debug info
            
            // Check if modal already exists in the DOM
            let skillsModal = document.getElementById('edit-skills-modal');
            
            // If it doesn't exist, add it to the DOM
            if (!skillsModal) {
                // Get the current skills
                const skillsContainer = findSkillsContainer();
                let currentSkills = '';
                if (skillsContainer) {
                    const skillTags = skillsContainer.querySelectorAll('.skill-tag');
                    if (skillTags.length > 0) {
                        currentSkills = Array.from(skillTags).map(tag => tag.textContent.trim()).join(', ');
                    }
                }
                
                // Get the modal HTML from the template
                const modalTemplate = `
                <div class="modal fade" id="edit-skills-modal" tabindex="-1" aria-labelledby="editSkillsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form id="edit-skills-form" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editSkillsModalLabel">Edit Skills</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label for="skills-input" class="form-label">Enter skills (comma separated):</label>
                                <input type="text" class="form-control" id="skills-input" name="skills" value="${currentSkills}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Skills</button>
                            </div>
                        </form>
                    </div>
                </div>`;
                
                // Add the modal to the DOM
                document.body.insertAdjacentHTML('beforeend', modalTemplate);
                skillsModal = document.getElementById('edit-skills-modal');
            }
            
            // Initialize the modal
            const editSkillsModal = new bootstrap.Modal(skillsModal);
            
            // Show the modal
            editSkillsModal.show();
            
            // Set up the form submission handler
            const editSkillsForm = document.getElementById('edit-skills-form');
            const skillsInput = document.getElementById('skills-input');
            
            if (editSkillsForm && skillsInput) {
                // Remove any existing event listeners
                const newForm = editSkillsForm.cloneNode(true);
                editSkillsForm.parentNode.replaceChild(newForm, editSkillsForm);
                
                // Add the event listener to the new form
                newForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Skills form submitted'); // Debug info
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('update_skills', 1);
                    formData.append('skills', document.getElementById('skills-input').value);
                    
                    // Manually close the modal BEFORE the fetch
                    editSkillsModal.hide();
                    
                    // Send the form data
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update the skills tags in the UI
                            const skillsContainer = findSkillsContainer();
                            if (skillsContainer) {
                                const skillsArr = data.skills.split(',').map(s => s.trim()).filter(Boolean);
                                skillsContainer.innerHTML = skillsArr.length
                                    ? skillsArr.map(skill => `<div class="skill-tag">${skill}</div>`).join('')
                                    : '<span class="text-muted">No skills listed.</span>';
                            }
                            
                            // Remove the modal from the DOM
                            setTimeout(() => {
                                skillsModal.remove();
                                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                document.body.classList.remove('modal-open');
                                document.body.style.overflow = '';
                                document.body.style.paddingRight = '';
                            }, 300);
                            
                            // Show success message
                            alert('Skills updated successfully!');
                        } else {
                            alert('Failed to update skills: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating skills.');
                    });
                });
            }
        });
    }
    
    // Find skills container helper function
    function findSkillsContainer() {
        const headers = document.querySelectorAll('.card-header');
        for (let header of headers) {
            if (header.textContent.includes('Skills')) {
                return header.nextElementSibling;
            }
        }
        return null;
    }
    
    // Edit availability button handler
    const editAvailabilityBtn = document.getElementById('edit-availability-btn');
    if (editAvailabilityBtn) {
        editAvailabilityBtn.addEventListener('click', function() {
            alert('Availability editing functionality coming soon!');
        });
    }
    
    // Add loading overlay if it doesn't exist
    if (!document.getElementById('loading-overlay')) {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loading-overlay';
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="spinner-container">
                <i class="fas fa-circle-notch fa-spin spinner"></i>
                <p>Loading...</p>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
        
        // Add CSS for loading overlay if not already in styles.css
        const style = document.createElement('style');
        style.textContent = `
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                display: none;
            }
            
            .loading-overlay.show {
                display: flex;
            }
            
            .spinner-container {
                text-align: center;
            }
            
            .spinner {
                font-size: 3rem;
                color: var(--primary);
                margin-bottom: 1rem;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Loading overlay functions
    window.showLoading = function() {
        document.getElementById('loading-overlay').classList.add('show');
    };
    
    window.hideLoading = function() {
        document.getElementById('loading-overlay').classList.remove('show');
    };

    // Change Avatar Modal handler
    const changeAvatarBtn = document.getElementById('change-avatar-btn');
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const avatarModal = new bootstrap.Modal(document.getElementById('change-avatar-modal'));
            avatarModal.show();
        });
    }

    // Preview selected image
    const avatarFileInput = document.getElementById('avatar-file');
    if (avatarFileInput) {
        avatarFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle avatar upload form
    const changeAvatarForm = document.getElementById('change-avatar-form');
    if (changeAvatarForm) {
        changeAvatarForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(changeAvatarForm);
            formData.append('change_avatar', 1);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update all avatar images on the page
                    document.querySelectorAll('img[src*="profile"], img[src*="randomuser"], .avatar img').forEach(img => {
                        img.src = data.avatar + '?t=' + Date.now();
                    });
                    // Close modal
                    const avatarModal = bootstrap.Modal.getInstance(document.getElementById('change-avatar-modal'));
                    if (avatarModal) avatarModal.hide();
                    alert('Profile picture updated!');
                } else {
                    alert('Failed to update profile picture: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the profile picture.');
            });
        });
    }

    // Show modal on button click
    const uploadIdBtn = document.getElementById('upload-id-btn');
    if (uploadIdBtn) {
        uploadIdBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const idModal = new bootstrap.Modal(document.getElementById('upload-id-modal'));
            idModal.show();
        });
    }

    // Preview selected ID image
    const idFileInput = document.getElementById('barangay-id-file');
    if (idFileInput) {
        idFileInput.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('id-preview');
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else if (file && file.type === 'application/pdf') {
                preview.src = 'https://via.placeholder.com/200x120?text=PDF+Selected';
            }
        });
    }

    // Handle upload form submit
    const uploadIdForm = document.getElementById('upload-id-form');
    if (uploadIdForm) {
        uploadIdForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(uploadIdForm);
            formData.append('upload_barangay_id', 1);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update the ID preview in the card
                    const cardImg = document.querySelector('.card-body img[alt="Barangay/Valid ID"]');
                    if (cardImg) cardImg.src = '../' + data.barangay_id_path + '?t=' + Date.now();
                    // Close modal
                    const idModal = bootstrap.Modal.getInstance(document.getElementById('upload-id-modal'));
                    if (idModal) idModal.hide();
                    alert('Barangay/Valid ID uploaded!');
                    // Hide the text
                    document.querySelector('.card-body .text-muted').style.display = 'none';
                } else {
                    alert('Failed to upload ID: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the ID.');
            });
        });
    }
});
</script>
</body>
</html>