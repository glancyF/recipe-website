<?php
    require_once __DIR__ . '/../../includes/authorization.php';
    $user = requireAuth();
?>
<div class="overview">
    <div class="overview-box">
<ul class="profile-info">
    <li><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></li>
    <li><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
    <li><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></li>
    <li><strong>Status:</strong> <?= htmlspecialchars($user['status']) ?></li>
    <li><strong>Id:</strong> <?= htmlspecialchars($user['id']) ?></li>
</ul>
    </div>
</div>