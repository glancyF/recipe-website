import { AddRecipeForm } from "../../AddRecipe/add_recipe.js";

class EditRecipeForm extends AddRecipeForm {
    getEndpoint()        { return "/profile/posts/update.php"; }
    getSuccessRedirect() { return "/profile/profile.php"; }


    controlRecipeImage(field, errors) {
        const file = field.files[0];
        if (file) {
            super.controlRecipeImage(field, errors);
            return;
        }
        const hasPreview = document.getElementById('imagePreview')?.src;
        if (!hasPreview) errors.push("Image is required");
    }

    init() {

        const hidden = document.getElementById('ingredientsHiddenInput');
        const initial = (hidden?.value || '')
            .split(';')
            .map(s => s.trim())
            .filter(Boolean);


        super.init({
            initialIngredients: initial,
            restoreOnReset: true,
            useBaseReset: false,
        });
    }
}

new EditRecipeForm();
