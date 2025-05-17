<?php
session_start();
require_once('../auth/db.php');
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all tasks with poster info
$tasks = $conn->query("
    SELECT t.*, u.first_name, u.last_name 
    FROM tasks t 
    JOIN users u ON t.contractor_id = u.id 
    ORDER BY t.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tasks | HatidGawa</title>
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
            border-right: 2px solid var(--border-color);
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
        } .table td {
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
        .btn-action {
            padding: 3px 8px;
            font-size: 0.8rem;
            margin-right: 5px;
            border-radius: 5px;
            border: none;
            background: var(--secondary-color);
            color: var(--text-color);
            transition: background 0.2s;
        }
        .btn-action:hover {
            background: var(--primary-color);
            color: var(--text-color);
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
            <a href="tasks.php" class="active"><i class="fas fa-fw fa-tasks me-2"></i> Tasks</a>
            <a href="safezones.php"><i class="fas fa-fw fa-map-marker-alt me-2"></i> Safezones</a>
            <a href="reports.php"><i class="fas fa-fw fa-flag me-2"></i> Reports</a>
        </div>
        <div class="sidebar-nav">
            <a href="logout.php"><i class="fas fa-fw fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks me-2"></i> All Tasks</span>
                <a href="dashboard.php" class="btn btn-sm btn-outline-primary">Back to Dashboard</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Poster</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No tasks found.</td></tr>
                            <?php else: foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= htmlspecialchars($task['title']) ?></td>
                                <td><?= htmlspecialchars($task['first_name'] . ' ' . $task['last_name']) ?></td>
                                <td>
                                    <?php if ($task['status'] === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($task['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($task['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                                <td>
                                    <a href="#" 
                                       class="btn btn-sm btn-action btn-view" 
                                       title="View"
                                       data-bs-toggle="modal"
                                       data-bs-target="#viewTaskModal"
                                       data-task='<?= htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8") ?>'
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-action btn-ban" title="Delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- View Task Modal -->
    <div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewTaskModalLabel">Task Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <dl class="row">
              <dt class="col-sm-3">Title</dt>
              <dd class="col-sm-9" id="task-title"></dd>

              <dt class="col-sm-3">Description</dt>
              <dd class="col-sm-9" id="task-description"></dd>

              <dt class="col-sm-3">Category</dt>
              <dd class="col-sm-9" id="task-category"></dd>

              <dt class="col-sm-3">Poster</dt>
              <dd class="col-sm-9" id="task-poster"></dd>

              <dt class="col-sm-3">Status</dt>
              <dd class="col-sm-9" id="task-status"></dd>

              <dt class="col-sm-3">Pay</dt>
              <dd class="col-sm-9" id="task-pay"></dd>

              <dt class="col-sm-3">Address</dt>
              <dd class="col-sm-9" id="task-address"></dd>

              <dt class="col-sm-3">Created</dt>
              <dd class="col-sm-9" id="task-created"></dd>
            </dl>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var viewTaskModal = document.getElementById('viewTaskModal');
        viewTaskModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var task = JSON.parse(button.getAttribute('data-task'));

            document.getElementById('task-title').textContent = task.title || '';
            document.getElementById('task-description').textContent = task.description || 'No description';
            document.getElementById('task-category').textContent = task.category || '';
            document.getElementById('task-poster').textContent = (task.first_name ? task.first_name + ' ' : '') + (task.last_name || '');
            document.getElementById('task-status').innerHTML = 
                task.status === 'completed' ? '<span class="badge badge-success">Completed</span>' :
                task.status === 'pending' ? '<span class="badge badge-warning">Pending</span>' :
                `<span class="badge badge-secondary">${task.status}</span>`;
            document.getElementById('task-pay').textContent = task.pay ? '₱' + parseFloat(task.pay).toLocaleString(undefined, {minimumFractionDigits:2}) : 'None';
            document.getElementById('task-address').textContent = task.address || '';
            document.getElementById('task-created').textContent = task.created_at ? (new Date(task.created_at)).toLocaleString() : '';
        });
    });
    </script>
</body>
</html>