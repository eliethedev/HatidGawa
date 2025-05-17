<?php
session_start();
require_once('../auth/db.php');
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = $_POST['id'];
    $verified = isset($_POST['verified']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET verified=? WHERE id=?");
    $stmt->bind_param("is", $verified, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Platform stats
$stats = [];
$stats['users'] = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$stats['tasks'] = $conn->query("SELECT COUNT(*) FROM tasks")->fetch_row()[0];
$stats['completed_tasks'] = $conn->query("SELECT COUNT(*) FROM tasks WHERE status='completed'")->fetch_row()[0];
$stats['safezones'] = $conn->query("SELECT COUNT(*) FROM safezones")->fetch_row()[0];

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
// Fetch tasks
$tasks = $conn->query("SELECT t.*, u.first_name, u.last_name FROM tasks t JOIN users u ON t.contractor_id=u.id ORDER BY t.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
// Fetch safezones
$safezones = $conn->query("SELECT * FROM safezones ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HatidGawa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #F6E7D8;   /* Light beige background */
            --secondary-color: #BFA181; /* Medium brown accent */
            --text-color: #22211F;      /* Almost black for text */
            --card-bg: #FFF8F0;         /* Slightly lighter for cards */
            --border-color: #E5D3C0;    /* Subtle border */
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
        
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: var(--text-color);
            margin-bottom: 20px;
            background: var(--card-bg);
            box-shadow: 0 2px 8px rgba(191,161,129,0.05);
            border: 1px solid var(--border-color);
        }
        
        .stat-card i {
            font-size: 2rem;
            opacity: 0.7;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            opacity: 0.8;
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
            <a href="dashboard.php" class="active"><i class="fas fa-fw fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="users.php"><i class="fas fa-fw fa-users me-2"></i> Users</a>
            <a href="tasks.php"><i class="fas fa-fw fa-tasks me-2"></i> Tasks</a>
            <a href="safezones.php"><i class="fas fa-fw fa-map-marker-alt me-2"></i> Safezones</a>
            <a href="reports.php"><i class="fas fa-fw fa-flag me-2"></i> Reports</a>
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

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card users">
                    <div class="row">
                        <div class="col-8">
                            <div class="stat-value"><?= $stats['users'] ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card tasks">
                    <div class="row">
                        <div class="col-8">
                            <div class="stat-value"><?= $stats['tasks'] ?></div>
                            <div class="stat-label">Total Tasks</div>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card completed">
                    <div class="row">
                        <div class="col-8">
                            <div class="stat-value"><?= $stats['completed_tasks'] ?></div>
                            <div class="stat-label">Completed Tasks</div>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card pending">
                    <div class="row">
                        <div class="col-8">
                            <div class="stat-value"><?= $stats['safezones'] ?></div>
                            <div class="stat-label">Total Safezones</div>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users me-2"></i> Recent Users</span>
                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if ($user['verified']): ?>
                                        <span class="badge badge-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Unverified</span>
                                    <?php endif; ?>
                                </td>
                               
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks me-2"></i> Recent Tasks</span>
                <a href="tasks.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Poster</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Safezones -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-map-marker-alt me-2"></i> Safezones</span>
                <a href="safezones.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Location Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($safezones as $zone): ?>
                            <tr>
                                <td><?= htmlspecialchars($zone['name']) ?></td>
                                <td><?= htmlspecialchars($zone['location_description']) ?></td>
                                <td>
                                    <?php if ($zone['is_approved']): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$zone['is_approved']): ?>
                                        <a href="approve_safezone.php?id=<?= $zone['id'] ?>" class="btn btn-sm btn-action btn-verify"><i class="fas fa-check"></i></a>
                                        <a href="reject_safezone.php?id=<?= $zone['id'] ?>" class="btn btn-sm btn-action btn-ban"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Verify User Modal -->
<div class="modal fade" id="verifyUserModal" tabindex="-1" aria-labelledby="verifyUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4">
      <form method="post" action="verify_user.php" class="p-3">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold text-success" id="verifyUserModalLabel">Verify User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <input type="hidden" name="id" id="verify-user-id">
          
          <div id="verify-user-details" class="mb-4 p-3 border rounded bg-light"></div>
        </div>
        
        <div class="modal-footer border-0">
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-check-circle me-2"></i> Confirm
          </button>
          <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
            <i class="fas fa-times-circle me-2"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form method="post" action="edit_user.php">
            <div class="modal-header">
              <h5 class="modal-title" id="editUserModalLabel">Edit User Verification</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" id="edit-user-id">
              <div id="edit-user-details"></div>
              <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="verified" id="edit-user-verified">
                <label class="form-check-label" for="edit-user-verified">
                  Verified
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // For Verify Modal
        var verifyUserModal = document.getElementById('verifyUserModal');
        verifyUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var user = JSON.parse(button.getAttribute('data-user'));
            document.getElementById('verify-user-id').value = user.id;
            document.getElementById('verify-user-details').innerHTML = `
                <h6>Name: ${user.first_name} ${user.last_name}</h6>
                <div>Email: ${user.email}</div>
                <div>Role: ${user.role}</div>
                <div>Barangay: ${user.barangay}</div>
                <div>Address: ${user.address}</div>
                <div>Joined: ${user.created_at}</div>
                <div>Status: ${user.verified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Unverified</span>'}</div>
                ${user.requirement_id_photo ? `<div class="mt-2"><strong>ID Photo:</strong><br><img src="../${user.requirement_id_photo}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                ${user.requirement_proof_address ? `<div class="mt-2"><strong>Proof of Address:</strong><br><img src="../${user.requirement_proof_address}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
            `;
        });

        // For Edit Modal
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var user = JSON.parse(button.getAttribute('data-user'));
            document.getElementById('edit-user-id').value = user.id;
            document.getElementById('edit-user-details').innerHTML = `
                <h6>Name: ${user.first_name} ${user.last_name}</h6>
                <div>Email: ${user.email}</div>
                <div>Role: ${user.role}</div>
                <div>Barangay: ${user.barangay}</div>
                <div>Address: ${user.address}</div>
                <div>Joined: ${user.created_at}</div>
                <div>Status: ${user.verified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Unverified</span>'}</div>
                ${user.requirement_id_photo ? `<div class="mt-2"><strong>ID Photo:</strong><br><img src="../${user.requirement_id_photo}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                ${user.requirement_proof_address ? `<div class="mt-2"><strong>Proof of Address:</strong><br><img src="../${user.requirement_proof_address}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
            `;
            document.getElementById('edit-user-verified').checked = !!user.verified;
        });
    });
    </script>
</body>
</html>