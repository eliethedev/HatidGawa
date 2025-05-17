<?php
session_start();
require_once('../auth/db.php');
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch reports (example: adjust query to your actual reports table/structure)
$sql = "SELECT r.*, u.first_name, u.last_name, t.title AS task_title 
    FROM reports r 
    LEFT JOIN users u ON r.user_id = u.id 
    LEFT JOIN tasks t ON r.task_id = t.id 
    ORDER BY r.created_at DESC 
    LIMIT 20";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$reports = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - HatidGawa Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #F6E7D8;
            --secondary-color: #BFA181;
            --text-color: #22211F;
            --card-bg: #FFF8F0;
            --border-color: #E5D3C0;
            --badge-success: #BFA181;
            --badge-warning: #F6E7D8;
            --badge-danger: #E57373;
            --badge-secondary: #BFA181;
        }
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            height: 100vh;
            background-color: var(--primary-color);
            color: var(--text-color);
            position: fixed;
            width: 250px;
            padding-top: 20px;
            border-right: 1px solid var(--border-color);
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-nav {
            padding: 20px 0;
        }
        .sidebar-nav a {
            color: var(--text-color);
            padding: 10px 20px;
            display: block;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: var(--secondary-color);
            color: var(--text-color);
        }
        .main-content {
            margin-left: 250px;
            padding: 24px;
            background-color: var(--primary-color);
            min-height: 100vh;
        }
        .card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: var(--primary-color);
            box-shadow: 0 0 15px rgba(191,161,129,0.04);
            margin-bottom: 30px;
        }
        .card-header {
            background-color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
            color: var(--text-color);
        }
        .table {
            margin-bottom: 0;
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-color);
            background: var(--primary-color);
        }
        .table td {
            background: var(--primary-color);
        }
        .badge {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.9em;
        }
        .badge-success {
            background-color: var(--badge-success);
            color: var(--text-color);
        }
        .badge-warning {
            background-color: var(--badge-warning);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        .badge-danger {
            background-color: var(--badge-danger);
            color: #fff;
        }
        .badge-secondary {
            background-color: var(--badge-secondary);
            color: var(--text-color);
        }
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(191,161,129,0.08);
            padding: 0.5rem 1rem;
            color: var(--text-color);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <h4><i class="fas fa-hands-helping me-2"></i> HatidGawa</h4>
            <small>Admin Portal</small>
        </div>
        <div class="sidebar-nav flex-grow-1">
            <a href="dashboard.php"><i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-fw fa-users me-2"></i> Users</a>
            <a href="tasks.php"><i class="fas fa-fw fa-tasks me-2"></i> Tasks</a>
            <a href="safezones.php"><i class="fas fa-fw fa-map-marker-alt me-2"></i> Safezones</a>
            <a href="reports.php" class="active"><i class="fas fa-fw fa-flag me-2"></i> Reports</a>
        </div>
        <div class="sidebar-nav">
            <a href="logout.php"><i class="fas fa-fw fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand mb-4">
            <div class="container-fluid justify-content-end">
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_username'] ?? 'A') ?>&background=random" class="user-avatar">
                </div>
            </div>
        </nav>

        <!-- Reports Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-flag me-2"></i> Reports</span>
                <!-- You can add a filter/search here if needed -->
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reported By</th>
                                <th>Task</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No reports found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($report['first_name'] ?? '') . ' ' . ($report['last_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($report['task_title'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($report['type'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($report['description'] ?? '') ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($report['created_at'])) ?></td>
                                    <td>
                                        <?php if (($report['status'] ?? '') === 'resolved'): ?>
                                            <span class="badge badge-success">Resolved</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>