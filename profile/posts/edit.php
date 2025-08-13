<?php
global $recipe;
$extra_css = '<link rel="stylesheet" href="/AddRecipe/addRecipe-styles.css">';
include(__DIR__ . '/../../includes/header.php');

require_once (__DIR__ . '/getPostForEdit.php')

?>
<div class="form-wrapper">
<form data-js-form class="AddForm edit-recipe-form" method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="id"  value="<?= htmlspecialchars($recipe['id']) ?>">
    <div class="field">
        <label for="recipeImage" class="field__label">Recipe Image</label>
        <input id="recipeImage" name="recipeImage" type="file"  class="field__control" accept=".jpg,.jpeg,.png" aria-errormessage="recipeImage-errors"  />
        <span class="field__errors" id="recipeImage-errors" data-js-form-field-errors></span>

        <?php if (!empty($recipe['image_path'])): ?>
            <div id="imagePreviewWrapper">
                <img id="imagePreview" src="/uploads/<?= htmlspecialchars($recipe['image_path']) ?>" alt="Preview">
                <button type="button" id="removeImageBtn" aria-label="Remove image">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="field">
        <label for="name" class="field__label">Recipe Name</label>
        <input id="name" name="name" type="text" required minlength="3" maxlength="100" pattern="^[A-Za-z\s,]+$" title="Name must contain only letters and spaces" aria-errormessage="name-errors"  value="<?= htmlspecialchars($recipe['name'])?>" class="field__control">
        <span class="field__errors" id="name-errors" data-js-form-field-errors></span>
    </div>


    <div class="field">
        <label for="description" class="field__label">Description</label>
        <textarea id="description" name="description" minlength="10" maxlength="300" required class="field__control"><?= htmlspecialchars($recipe['description']) ?></textarea>
        <span class="field__errors" id="description-errors" data-js-form-field-errors></span>
    </div>

    <div class="field" id="ingredientsField">
        <label for="IngredientInput" class="field__label">Ingredients</label>
        <div class="ingredients-input">
            <input id="IngredientInput" type="text" placeholder="Add ingredient.."  maxlength="50" class="field__control" />
            <button type="button" id="addIngredientBtn" aria-label="Add ingredient">
                <i class="fa fa-plus"></i>
            </button>
            <span class="field__errors" id="IngredientInput-errors" data-js-form-field-errors></span>
        </div>
        <ul id="ingredientsList" class="ingredients-list">
            <?php foreach (explode(';', $recipe['ingredients']) as $ingredient): ?>
                <li>
                    <span class="ingredient-text"><?= htmlspecialchars(trim($ingredient)) ?></span>
                    <button type="button" class="remove-ingredient" aria-label="Remove ingredient">
                        <i class="fa fa-times"></i>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <input type="hidden" id="ingredientsHiddenInput" name="ingredients" value="<?= htmlspecialchars($recipe['ingredients']) ?>">

    </div>
<!-- инструкция -->
    <div class="field">
        <label for="instruction" class="field__label">Instruction</label>
        <textarea id="instruction" name="instruction" minlength="20" maxlength="5000" required class="field__control"><?= htmlspecialchars($recipe['instruction']) ?></textarea>
        <span class="field__errors" id="instruction-errors" data-js-form-field-errors></span>
    </div>

    <div class="field select">
        <label for="category" class="field__label">Category</label>
        <select id="category" name="category" required class="field__control" aria-errormessage="category-errors">
            <option value="" disabled selected>Select category</option>
            <option value="lunch" <?= $recipe['category'] === 'lunch' ? 'selected' : '' ?>>Lunch</option>
            <option value="dessert" <?= $recipe['category'] === 'dessert' ? 'selected' : '' ?>>Dessert</option>
            <option value="snack" <?= $recipe['category'] === 'snack' ? 'selected' : '' ?>>Snack</option>
            <option value="dinner" <?= $recipe['category'] === 'dinner' ? 'selected' : '' ?>>Dinner</option>
            <option value="breakfast" <?= $recipe['category'] === 'breakfast' ? 'selected' : '' ?>>Breakfast</option>
        </select>
        <span class="field__errors" id="category-errors" data-js-form-field-errors></span>
    </div>

    <div class="reset-button">
        <button type="reset">Reset changes</button>
    </div>
    <div class="submit-button">
        <button type="submit">Update Recipe</button>
    </div>
</form>
</div>
<script type="module" src="/profile/posts/editRecipe.js"></script>