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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verified = isset($_POST['verified']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET verified = ? WHERE id = ?");
    $stmt->bind_param("is", $verified, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: user.php?id=" . $user_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - HatidGawa Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <a href="dashboard.php" class="btn btn-outline-secondary mb-3">&larr; Back to Dashboard</a>
    <div class="card p-4">
        <h3>Edit User: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
        <form method="post">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="verified" id="verified" value="1" <?= $user['verified'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="verified">
                    Verified
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="user.php?id=<?= $user['id'] ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html> 