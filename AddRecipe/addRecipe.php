<?php
$pageTitle = 'Add Recipe';
$extra_css = '<link rel="stylesheet" href="AddRecipe/addRecipe-styles.css">';
include __DIR__ . '/../includes/authorization.php';
$user = requireAuth();
$flash  = $_SESSION['flash_add_recipe'] ?? null;
unset($_SESSION['flash_add_recipe']);
$old    = $flash['old']    ?? [];
$errors = $flash['errors'] ?? [];
$general= $flash['general']?? null;
include_once("../includes/header.php");
?>

    <div class="form-wrapper">
        <form class="AddForm" method="post" enctype="multipart/form-data" novalidate data-js-form
              action="AddRecipe/add_recipeApi.php">

            <div class="field">
                <label class="field__label" for="recipeImage">Upload image</label>
                <input class="field__control" id="recipeImage" name="recipeImage" type="file" required
                       title="Upload JPG, JPEG or PNG image" aria-errormessage="recipeImage-errors" accept=".jpg,.jpeg,.png"
                    <?= isset($errors['recipeImage']) ? 'aria-invalid="true"' : '' ?>>
                <div class="image-preview-wrapper" id="imagePreviewWrapper">
                    <img id="imagePreview" alt="Image preview" src="images/blank.png"/>
                    <button type="button" id="removeImageBtn" aria-label="Remove image"><i class="fa fa-times"></i></button>
                </div>
                <span class="field__errors" id="recipeImage-errors" data-js-form-field-errors>
        <?= isset($errors['recipeImage']) ? htmlspecialchars($errors['recipeImage']) : '' ?>
      </span>
            </div>

            <p class="field">
                <label class="field__label" for="name">Title</label>
                <input class="field__control" id="name" name="name" required minlength="3" maxlength="100"
                       pattern="^[A-Za-z\s,]+$" title="Name must contain only letters and spaces"
                       aria-errormessage="name-errors" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                    <?= isset($errors['name']) ? 'aria-invalid="true"' : '' ?>>
                <span class="field__errors" id="name-errors" data-js-form-field-errors>
        <?= isset($errors['name']) ? htmlspecialchars($errors['name']) : '' ?>
      </span>
            </p>

            <p class="field">
                <label class="field__label" for="description">Description</label>
                <textarea class="field__control" id="description" name="description" minlength="10" maxlength="130" required
                <?= isset($errors['description']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                <span class="field__errors" id="description-errors" data-js-form-field-errors>
        <?= isset($errors['description']) ? htmlspecialchars($errors['description']) : '' ?>
      </span>
            </p>

            <div class="field" id="ingredientsField">
                <label class="field__label" for="IngredientInput">Ingredients</label>

                <div class="Ingredients-wrapper">
                    <div class="ingredients-input">
                        <input type="text" id="IngredientInput" class="field__control" placeholder="Add ingredient.." maxlength="50"
                               aria-errormessage="IngredientInput-errors">
                        <button type="button" id="addIngredientBtn" aria-label="Add ingredient"><i class="fa fa-plus"></i></button>
                    </div>
                    <span class="field__errors" id="IngredientInput-errors" data-js-form-field-errors></span>
                    <ul id="ingredientsList" class="ingredients-list"></ul>
                    <input type="hidden" name="ingredients" id="ingredientsHiddenInput">
                </div>

                <noscript>
                <div class="ingredients-fallback">
                    <label for="ingredients_fallback">Enter here, if u don't have enabled the JS, separate with ;</label>
                    <textarea class="field__control" name="ingredients_fallback" id="ingredients_fallback" rows="5"
                              aria-errormessage="ingredients-errors"
                  <?= isset($errors['ingredients']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['ingredients_fallback'] ?? '') ?></textarea>
                    <span class="field__errors" id="ingredients-errors">
          <?= isset($errors['ingredients']) ? htmlspecialchars($errors['ingredients']) : '' ?>
        </span>
                </div>
                </noscript>
            </div>

            <p class="field">
                <label class="field__label" for="instruction">Instruction</label>
                <textarea class="field__control" id="instruction" name="instruction" required minlength="20" maxlength="5000"
                <?= isset($errors['instruction']) ? 'aria-invalid="true"' : '' ?>><?= htmlspecialchars($old['instruction'] ?? '') ?></textarea>
                <span class="field__errors" id="instruction-errors" data-js-form-field-errors>
        <?= isset($errors['instruction']) ? htmlspecialchars($errors['instruction']) : '' ?>
      </span>
            </p>

            <p class="field">
                <label class="field__label" for="category">Category</label>
                <select class="field__control" name="category" id="category" required
                    <?= isset($errors['category']) ? 'aria-invalid="true"' : '' ?>>
                    <option value="" disabled <?= empty($old['category']) ? 'selected' : '' ?>>Select category</option>
                    <?php foreach (['breakfast','lunch','dinner','dessert','snack'] as $c): ?>
                        <option value="<?= $c ?>" <?= (($old['category'] ?? '') === $c) ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="field__errors" id="category-errors" data-js-form-field-errors>
        <?= isset($errors['category']) ? htmlspecialchars($errors['category']) : '' ?>
      </span>
            </p>

            <div class="reset-button"><button type="reset">Reset changes</button></div>
            <div class="submit-button"><button type="submit">Confirm changes</button></div>
        </form>
    </div>

    <script src="AddRecipe/add_recipe_init.js" type="module"></script>

<?php
include_once("../includes/footer.php");
?>