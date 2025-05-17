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

<img id="avatar-preview" src="<?= !empty($user['profile_picture']) 
    ? (strpos($user['profile_picture'], 'http') === 0 
        ? htmlspecialchars($user['profile_picture']) 
        : '../' . htmlspecialchars($user['profile_picture'])) 
    : '../assets/images/default-avatar.png' ?>" 
    alt="Current Avatar" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;"> 