<?php
global $topRecipe;
require_once __DIR__ . '/../../includes/authorization.php';
    $user = requireAuth();
    require_once __DIR__ . '/../../profile/overview/mostLikedRecipe.php';
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


<?php if ($topRecipe): ?>
<div class="top-liked-recipe">
    <h2>Most Liked Recipe</h2>
    <div class="recipe-card">
        <div class="image-container">
            <img src="/uploads/<?= htmlspecialchars($topRecipe['image_path']) ?>" alt="Recipe image">
            <div class="like-container" data-id="<?= htmlspecialchars($topRecipe['id']) ?>">
                <i class="fa fa-heart<?= $topRecipe['liked'] ? ' liked' : '' ?>"></i>
                <span class="like-count"><?= $topRecipe['like_count'] ?></span>
            </div>
        </div>
        <h3><?= htmlspecialchars($topRecipe['name']) ?></h3>
        <p><?= htmlspecialchars($topRecipe['description']) ?></p>
        <div class="view">
            <a href="/recipes/recipes.php?id=<?= $topRecipe['id'] ?>">View</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="/profile/overview/mostLikedRecipe.js" type="module"></script>



