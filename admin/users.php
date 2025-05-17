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
    header("Location: users.php");
    exit();
}

// Search/filter logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];
$types = '';

if ($search !== '') {
    $where = "WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = [$search_param, $search_param, $search_param];
    $types = 'sss';
}

// Fetch users
$sql = "SELECT * FROM users $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($where) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Users | HatidGawa</title>
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
            background-color: var(--card-bg);
            color: var(--text-color);
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-color);
            background: var(--primary-color);
        }        .table {
            margin-bottom: 0;
            background-color: var(--primary-color);
            color: var(--text-color);
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
        .search-bar {
            max-width: 350px;
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
            <a href="users.php" class="active"><i class="fas fa-fw fa-users me-2"></i> Users</a>
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
        <!-- Users Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users me-2"></i> Users</span>
                <form class="d-flex search-bar" method="get" action="users.php">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Barangay/Valid ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="text-center text-muted">No users found.</td></tr>
                            <?php else: foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <img src="<?php
                                        if (!empty($user['profile_picture'])) {
                                            echo (strpos($user['profile_picture'], 'http') === 0)
                                                ? htmlspecialchars($user['profile_picture'])
                                                : '../' . htmlspecialchars($user['profile_picture']);
                                        } else {
                                            echo 'https://randomuser.me/api/portraits/lego/1.jpg';
                                        }
                                    ?>" alt="Avatar" class="user-avatar">
                                </td>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <?php if ($user['verified']): ?>
                                        <span class="badge badge-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Unverified</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if (!empty($user['barangay_id_path'])): ?>
                                        <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $user['barangay_id_path'])): ?>
                                            <a href="../<?= htmlspecialchars($user['barangay_id_path']) ?>" target="_blank" title="View Barangay/Valid ID">
                                                <i class="fas fa-id-card fa-lg text-primary"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="../<?= htmlspecialchars($user['barangay_id_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View ID</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-danger">No ID</span>
                                    <?php endif; ?>
                                </td>
                                <td>  <a href="user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-action btn-view"><i class="fas fa-eye"></i></a>
                                    <button 
                                        class="btn btn-sm btn-action btn-verify" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#verifyUserModal"
                                        data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>'
                                        title="Verify"
                                        <?= $user['verified'] ? 'disabled' : '' ?>
                                    >
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-action btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal"
                                        data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>'
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="ban_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-action btn-ban" title="Ban"><i class="fas fa-ban"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
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
      <form method="post" action="users.php" class="p-3">
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center">
            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
              <i class="fas fa-user-check fs-4 text-success"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark mb-0" id="verifyUserModalLabel">Verify User Account</h5>
              <p class="text-muted small mb-0">Confirm user identity and activate account</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body py-4">
          <input type="hidden" name="id" id="verify-user-id">
          <input type="hidden" name="verified" value="1">
          
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <div>
              Verifying this account will grant full access to platform features
            </div>
          </div>
          
          <div id="verify-user-details" class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="flex-shrink-0">
                  <img id="verify-user-avatar" src="https://via.placeholder.com/80" class="rounded-circle border" width="80" height="80" alt="User Avatar">
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5 id="verify-user-name" class="mb-1">John Doe</h5>
                  <div class="d-flex flex-wrap gap-2">
                    <span id="verify-user-email" class="badge bg-light text-dark">
                      <i class="fas fa-envelope me-1"></i> user@example.com
                    </span>
                    <span id="verify-user-role" class="badge bg-primary">
                      <i class="fas fa-user-tag me-1"></i> Member
                    </span>
                    <span id="verify-user-status" class="badge bg-warning text-dark">
                      <i class="fas fa-clock me-1"></i> Unverified
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded">
                    <h6 class="text-muted small mb-3">REGISTRATION DETAILS</h6>
                    <ul class="list-unstyled mb-0">
                      <li class="mb-2">
                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                        <span id="verify-user-created">Joined on Jan 1, 2023</span>
                      </li>
                      <li class="mb-2">
                        <i class="fas fa-id-card text-muted me-2"></i>
                        <span id="verify-user-id-type">Email verification</span>
                      </li>
                    </ul>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded">
                    <h6 class="text-muted small mb-3">ACTIVITY SUMMARY</h6>
                    <ul class="list-unstyled mb-0">
                      <li class="mb-2">
                        <i class="fas fa-tasks text-muted me-2"></i>
                        <span id="verify-user-tasks">5 tasks completed</span>
                      </li>
                      <li class="mb-2">
                        <i class="fas fa-star text-muted me-2"></i>
                        <span id="verify-user-rating">4.8 average rating</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" checked>
            <label class="form-check-label" for="sendNotification">
              Send verification confirmation email
            </label>
          </div>
        </div>
        
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">
            <i class="fas fa-times-circle me-2"></i> Cancel
          </button>
          <button type="submit" class="btn btn-success px-4 rounded-pill">
            <i class="fas fa-check-circle me-2"></i> Confirm Verification
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript to populate modal -->
<script>
function showVerifyModal(userId) {
  // Fetch user data via AJAX
  fetch(`get_user_details.php?id=${userId}`)
    .then(response => response.json())
    .then(data => {
      // Populate modal fields
      document.getElementById('verify-user-id').value = data.id;
      document.getElementById('verify-user-avatar').src = data.avatar || 'https://ui-avatars.com/api/?name='+encodeURIComponent(data.name)+'&background=random';
      document.getElementById('verify-user-name').textContent = data.name;
      document.getElementById('verify-user-email').innerHTML = `<i class="fas fa-envelope me-1"></i> ${data.email}`;
      document.getElementById('verify-user-role').innerHTML = `<i class="fas fa-user-tag me-1"></i> ${data.role}`;
      document.getElementById('verify-user-status').innerHTML = `<i class="fas fa-clock me-1"></i> ${data.verified ? 'Verified' : 'Unverified'}`;
      document.getElementById('verify-user-created').textContent = `Joined on ${new Date(data.created_at).toLocaleDateString()}`;
      document.getElementById('verify-user-tasks').textContent = `${data.tasks_completed} tasks completed`;
      document.getElementById('verify-user-rating').textContent = `${data.rating} average rating`;
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('verifyUserModal'));
      modal.show();
    })
    .catch(error => console.error('Error:', error));
}
</script>

  <!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="users.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editUserModalLabel">User Verification Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-user-id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">User Information</h6>
                                </div>
                                <div class="card-body" id="edit-user-details"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Verification Documents</h6>
                                </div>
                                <div class="card-body" id="edit-user-documents"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="verified" id="edit-user-verified" style="width: 1.5em; height: 1.5em;">
                                <label class="form-check-label fs-5" for="edit-user-verified">
                                    Mark user as verified
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
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
        if (verifyUserModal) {
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
                    ${user.barangay_id_path ? `<div class="mt-2"><strong>Barangay/Valid ID:</strong><br>
                        ${/\.(jpg|jpeg|png|gif)$/i.test(user.barangay_id_path)
                            ? `<img src="../${user.barangay_id_path}" class="img-fluid rounded" style="max-width:200px;">`
                            : `<a href="../${user.barangay_id_path}" target="_blank" class="btn btn-outline-primary btn-sm">View ID Document</a>`
                        }
                    </div>` : '<div class="mt-2 text-danger">No Barangay/Valid ID uploaded.</div>'}
                    ${user.requirement_id_photo ? `<div class="mt-2"><strong>ID Photo:</strong><br><img src="../${user.requirement_id_photo}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                    ${user.requirement_proof_address ? `<div class="mt-2"><strong>Proof of Address:</strong><br><img src="../${user.requirement_proof_address}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                `;
            });
        }

        // For Edit Modal
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
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
                    ${user.barangay_id_path ? `<div class="mt-2"><strong>Barangay/Valid ID:</strong><br>
                        ${/\.(jpg|jpeg|png|gif)$/i.test(user.barangay_id_path)
                            ? `<img src="../${user.barangay_id_path}" class="img-fluid rounded" style="max-width:200px;">`
                            : `<a href="../${user.barangay_id_path}" target="_blank" class="btn btn-outline-primary btn-sm">View ID Document</a>`
                        }
                    </div>` : '<div class="mt-2 text-danger">No Barangay/Valid ID uploaded.</div>'}
                    ${user.requirement_id_photo ? `<div class="mt-2"><strong>ID Photo:</strong><br><img src="../${user.requirement_id_photo}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                    ${user.requirement_proof_address ? `<div class="mt-2"><strong>Proof of Address:</strong><br><img src="../${user.requirement_proof_address}" class="img-fluid rounded" style="max-width:200px;"></div>` : ''}
                `;
                document.getElementById('edit-user-verified').checked = !!user.verified;
            });
        }
    });
    </script>
</body>
</html>