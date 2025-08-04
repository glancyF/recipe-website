<?php
global $recipe, $ingredients;
$extra_css ='<link rel="stylesheet" href="/recipes/recipe-style.css">';
$pageTittle ='Recipes';
include "../includes/header.php";
include "../recipes/recipe.php";

?>



<div class="recipe-page">
    <h1><?=htmlspecialchars($recipe['name']) ?></h1>
    <div class="category">
        <?=htmlspecialchars($recipe['category']) ?>
    </div>
    <div class="meta">
    <span class="author">
        <i class="fa fa-user"></i> <?= htmlspecialchars($recipe['username']) ?>
    </span>
        <span class="created">
            <i class="fa-solid fa-calendar"></i> Created at <?= date("F j, Y", strtotime($recipe['created_at'])) ?>
        </span>
        <span class="updated">Last update <?= date("F j, Y", strtotime($recipe['updated_at'])) ?>
    </span>
    </div>
    <div class="like-container" data-id="<?= htmlspecialchars($recipe['id']) ?>">
        <i class="fa fa-heart<?= $recipe['liked'] ? ' liked' : '' ?>"></i>
        <span class="like-count"><?= $recipe['like_count'] ?></span>
    </div>
    <?php if (!empty($recipe['image_path'])): ?>
        <img src="/uploads/<?= htmlspecialchars($recipe['image_path']) ?>" alt="Recipe image">
    <?php endif; ?>

    <p class="description"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>

    <h3>Ingredients:</h3>
    <ul class="ingredients">
        <?php foreach ($ingredients as $ingredient): ?>
            <li><?= htmlspecialchars($ingredient) ?></li>
        <?php endforeach; ?>
    </ul>


    <h3>Instructions:</h3>
    <p class="instruction"><?= nl2br(htmlspecialchars($recipe['instruction'])) ?></p>
</div>
<script src="recipe-like.js" type="module"></script>
<?php
include "../includes/footer.php";
?>

