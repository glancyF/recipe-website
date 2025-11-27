<?php
session_start();
global $recipe;

$pageTitle='Edit Recipe';
$extra_css = '<link rel="stylesheet" href="AddRecipe/addRecipe-styles.css">';

$flash  = $_SESSION['flash_edit_recipe'] ?? null;
unset($_SESSION['flash_edit_recipe']);
$old    = $flash['old']    ?? [];
$errors = $flash['errors'] ?? [];
$general= $flash['general']?? null;

require_once (__DIR__ . '/getPostForEdit.php');
include(__DIR__ . '/../../includes/header.php');
?>
<div class="form-wrapper">
    <form data-js-form class="AddForm edit-recipe-form"
          action="profile/posts/updateApi.php"
          method="POST" enctype="multipart/form-data" novalidate>

        <input type="hidden" name="id" value="<?= htmlspecialchars($recipe['id']) ?>">

        <div class="field">
            <label for="recipeImage" class="field__label">Recipe Image</label>
            <input id="recipeImage" name="recipeImage" type="file" class="field__control"
                   accept=".jpg,.jpeg,.png" aria-errormessage="recipeImage-errors"
                <?= isset($errors['recipeImage']) ? 'aria-invalid="true"' : '' ?>>
            <span class="field__errors" id="recipeImage-errors" data-js-form-field-errors>
        <?= isset($errors['recipeImage']) ? htmlspecialchars($errors['recipeImage']) : '' ?>
      </span>

            <?php if (!empty($recipe['image_path'])): ?>
                <div id="imagePreviewWrapper">
                    <img id="imagePreview" src="uploads/banner/<?= htmlspecialchars($recipe['image_path']) ?>" alt="Preview">
                    <button type="button" id="removeImageBtn" aria-label="Remove image"><i class="fa fa-times"></i></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="name" class="field__label">Recipe Name</label>
            <input id="name" name="name" type="text" required minlength="3" maxlength="100"
                   pattern="^[A-Za-z\s,]+$" title="Name must contain only letters and spaces"
                   aria-errormessage="name-errors" class="field__control"
                   value="<?= htmlspecialchars($old['name'] ?? $recipe['name']) ?>"
                <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
            <span class="field__errors" id="name-errors" data-js-form-field-errors>
        <?= isset($errors['name']) ? htmlspecialchars($errors['name']) : '' ?>
      </span>
        </div>

        <div class="field">
            <label for="description" class="field__label">Description</label>
            <textarea id="description" name="description" minlength="10" maxlength="130" required
                      class="field__control" <?= isset($errors['description']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['description'] ?? $recipe['description']) ?></textarea>
            <span class="field__errors" id="description-errors" data-js-form-field-errors>
        <?= isset($errors['description']) ? htmlspecialchars($errors['description']) : '' ?>
      </span>
        </div>

        <div class="field" id="ingredientsField">
            <label for="IngredientInput" class="field__label">Ingredients</label>

            <div class="ingredients-input">
                <input id="IngredientInput" type="text" placeholder="Add ingredient.." maxlength="50" class="field__control">
                <button type="button" id="addIngredientBtn" aria-label="Add ingredient"><i class="fa fa-plus"></i></button>
                <span class="field__errors" id="IngredientInput-errors" data-js-form-field-errors></span>
            </div>

            <ul id="ingredientsList" class="ingredients-list">
                <?php
                foreach (explode(';', (string)$recipe['ingredients']) as $i) {
                    $i = trim($i);
                    if (!$i) continue; ?>
                    <li>
                        <span class="ingredient-text"><?= htmlspecialchars($i) ?></span>
                        <button type="button" class="remove-ingredient" aria-label="Remove ingredient"><i class="fa fa-times"></i></button>
                    </li>
                <?php } ?>
            </ul>

            <input type="hidden" id="ingredientsHiddenInput" name="ingredients"
                   value="<?= htmlspecialchars($recipe['ingredients']) ?>">

            <noscript>
                <div class="ingredients-fallback">
                    <label for="ingredients_fallback">Enter here if JS is disabled (separate with “;”)</label>
                    <textarea class="field__control" name="ingredients_fallback" id="ingredients_fallback" rows="5"
                              aria-errormessage="ingredients-errors"
            <?= isset($errors['ingredients']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['ingredients_fallback'] ?? '') ?></textarea>
                    <span class="field__errors" id="ingredients-errors">
            <?= isset($errors['ingredients']) ? htmlspecialchars($errors['ingredients']) : '' ?>
          </span>
                </div>
            </noscript>
        </div>

        <div class="field">
            <label for="instruction" class="field__label">Instruction</label>
            <textarea id="instruction" name="instruction" minlength="20" maxlength="5000" required
                      class="field__control" <?= isset($errors['instruction']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['instruction'] ?? $recipe['instruction']) ?></textarea>
            <span class="field__errors" id="instruction-errors" data-js-form-field-errors>
        <?= isset($errors['instruction']) ? htmlspecialchars($errors['instruction']) : '' ?>
      </span>
        </div>

        <div class="field select">
            <label for="category" class="field__label">Category</label>
            <?php $cur = $old['category'] ?? $recipe['category']; ?>
            <select id="category" name="category" required class="field__control" aria-errormessage="category-errors"
                <?= isset($errors['category']) ? 'aria-invalid="true"' : '' ?>>
                <option value="" disabled <?= empty($cur) ? 'selected' : '' ?>>Select category</option>
                <?php foreach (['lunch','dessert','snack','dinner','breakfast'] as $c): ?>
                    <option value="<?= $c ?>" <?= $cur === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="field__errors" id="category-errors" data-js-form-field-errors>
        <?= isset($errors['category']) ? htmlspecialchars($errors['category']) : '' ?>
      </span>
        </div>

        <div class="reset-button"><button type="reset">Reset changes</button></div>
        <div class="submit-button"><button type="submit">Update Recipe</button></div>
    </form>
</div>
<script type="module" src="profile/posts/editRecipe.js"></script>
<?php include(__DIR__ . '/../../includes/footer.php'); ?>
