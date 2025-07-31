<?php
global $recipe, $ingredients;
$pageTittle ='Recipes';
include "../includes/header.php";
include "../recipes/recipe.php";
?>



<div class="recipe-page">
    <h1><?=htmlspecialchars($recipe['name']) ?></h1>

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

<?php
include "../includes/footer.php";
?>

