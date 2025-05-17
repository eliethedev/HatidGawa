<?php
session_start();
require_once('../auth/db.php');
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "<h2>User not found.</h2>";
    exit();
}

// Example: If you have requirements like ID photo, proof of address, etc.
$requirements = [];
if (!empty($user['requirement_id_photo'])) {
    $requirements[] = ['label' => 'ID Photo', 'file' => $user['requirement_id_photo']];
}
if (!empty($user['requirement_proof_address'])) {
    $requirements[] = ['label' => 'Proof of Address', 'file' => $user['requirement_proof_address']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - User Details | HatidGawa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #F6E7D8; color: #22211F; font-family: 'Inter', sans-serif; }
        .card { background: #FFF8F0; border: 1px solid #E5D3C0; border-radius: 10px; }
        .badge-success { background: #BFA181; color: #22211F; }
        .badge-warning { background: #F6E7D8; color: #22211F; border: 1px solid #E5D3C0; }
        .badge-danger { background: #E57373; color: #fff; }
        .user-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #BFA181; }
    </style>
</head>
<body>
<div class="container py-4">
    <a href="users.php" class="btn btn-outline-secondary mb-3">&larr; Back to Users</a>
    <div class="card p-4">
        <div class="d-flex align-items-center mb-4">
            <img src="<?= !empty($user['profile_picture']) ? (strpos($user['profile_picture'], 'http') === 0 ? htmlspecialchars($user['profile_picture']) : '../' . htmlspecialchars($user['profile_picture'])) : 'https://randomuser.me/api/portraits/lego/1.jpg' ?>" class="user-avatar me-4" alt="Avatar">
            <div>
                <h3 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <div><?= htmlspecialchars($user['email']) ?></div>
                <div><?= htmlspecialchars($user['phone']) ?></div>
                <div>
                    <?php if ($user['verification_status'] === 'banned'): ?>
                        <span class="badge badge-danger">Banned</span>
                    <?php elseif ($user['verified']): ?>
                        <span class="badge badge-success">Verified</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Unverified</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <h5>Details</h5>
        <ul>
            <li><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></li>
            <li><strong>Barangay:</strong> <?= htmlspecialchars($user['barangay']) ?></li>
            <li><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></li>
            <li><strong>Joined:</strong> <?= date('M d, Y', strtotime($user['created_at'])) ?></li>
            <li><strong>About:</strong> <?= htmlspecialchars($user['about']) ?></li>
        </ul>
        <h5>Requirements</h5>
        <?php if (empty($requirements)): ?>
            <div class="text-muted">No requirements uploaded.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($requirements as $req): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card p-2">
                            <div class="mb-2"><strong><?= htmlspecialchars($req['label']) ?></strong></div>
                            <img src="../<?= htmlspecialchars($req['file']) ?>" alt="<?= htmlspecialchars($req['label']) ?>" class="img-fluid rounded">
                            <a href="../<?= htmlspecialchars($req['file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">View Full</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-4">
            <?php if (!$user['verified'] && $user['verification_status'] !== 'banned'): ?>
                <a href="verify_user.php?id=<?= $user['id'] ?>" class="btn btn-success me-2">Verify User</a>
            <?php endif; ?>
            <?php if ($user['verification_status'] !== 'banned'): ?>
                <a href="ban_user.php?id=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to ban this user?');">Ban User</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html> 