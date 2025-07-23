<?php
$pageTitle = 'Add Recipe';
$extra_css = '<link rel="stylesheet" href="/AddRecipe/addRecipe-styles.css">';
include_once("../includes/header.php");
?>

<div class="form-wrapper">
    <form class="AddForm" method="post" enctype="multipart/form-data" novalidate data-js-form>
        <div class="field">
            <label class="field__label" for="recipeImage">Upload image</label>
            <input
            class="field__control"
            id="recipeImage"
            name="recipeImage"
            type="file"

            required
            title="Upload JPG, JPEG or PNG image"
            aria-errormessage="recipeImage-errors"
            accept=".jpg,.jpeg,.png"
            />
        <div class="image-preview-wrapper" id="imagePreviewWrapper">
            <!-- v js potom pridavam -->
            <img id="imagePreview" alt="Image preview" />
            <button type="button" id="removeImageBtn" aria-label="Remove image">
                <i class="fa fa-times"></i> <!-- ИСПРАВИЛ -->
            </button>
        </div>
        <span class="field__errors" id="recipeImage-errors" data-js-form-field-errors></span>
            </div>

        <p class="field">
            <label class="field__label" for="name">Title</label>
            <input
            class="field__control"
            id="name"
            name="name"
            required
            minlength="3"
            maxlength="100"
            pattern="^[A-Za-z\s]+$"
            title="Name must contain only letters and spaces"
            aria-errormessage="name-errors"
            />
            <span class="field__errors" id="name-errors" data-js-form-field-errors></span>
        </p>
        <p class="field">
            <label class="field__label" for="description">Description</label>
            <textarea class="field__control" id="description" name="description" minlength="10" maxlength="300" required></textarea>
            <span class="field__errors" id="description-errors" data-js-form-field-errors></span>
        </p>
        <div class="field" id="ingredientsField">
            <label class="field__label" for="IngredientInput">Ingredients</label>
            <div class="Ingredients-wrapper">
                <div class="ingredients-input">
                    <input
                    type="text"
                    id="IngredientInput"
                    class="field__control"
                    placeholder="Add ingredient.."
                    required
                    />
                    <button type="button" id="addIngredientBtn" aria-label="Add ingredient">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            <ul id="ingredientsList" class="ingredients-list">
                <!-- dynamicky pomoci js delam to -->
            </ul>
            <input type="hidden" name="ingredients" id="ingredientsHiddenInput"  />
           </div>
        <span class="field__errors" id="IngredientInput-errors" data-js-form-field-errors></span>
        </div>
        <p class="field">
            <label class="field__label" for="instruction">Instruction</label>
            <textarea class="field__control" id="instruction" name="instruction" required minlength="20" maxlength="5000"></textarea>
            <span class="field__errors" id="instruction-errors" data-js-form-field-errors></span>
        </p>
        <p class="field">
            <label class="field__label" for="category">Category</label>
            <select class="field__control"
                    name="category"
                    id="category"
                    required>
                <option value="" disabled selected>Select category</option>
                <option value="breakfast">Breakfast</option>
                <option value="lunch">Lunch</option>
                <option value="dinner">Dinner </option>
                <option value="dessert">Dessert</option>
                <option value="snack">Snack</option>
            </select>
            <span class="field__errors" id="category-errors" data-js-form-field-errors></span>
        </p>
        <div class="reset-button">
            <button type="reset">Reset changes</button>
        </div>
        <div class="submit-button">
            <button type="submit">Confirm changes</button>
        </div>
    </form>



</div>
<script src="add_recipe.js" type="module"></script>

<?php
include_once("../includes/footer.php");
?>