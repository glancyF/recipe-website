import {AddRecipeForm} from "../../AddRecipe/add_recipe.js";

class EditRecipeForm extends AddRecipeForm{
    constructor() {
        super();

    }


    getEndpoint() {
       return '/profile/posts/update.php';
   }
   getSuccessRedirect() {
       return '/profile/profile.php';

   }
   controlRecipeImage(fieldControlElement, errorMessages) {
       const file = fieldControlElement.files[0];
       if (file){
           super.controlRecipeImage(fieldControlElement,errorMessages)
       }
       const hasPreview = document.getElementById('imagePreview')?.src;
       if(!file && hasPreview) return;
       if(!file && !hasPreview){
           errorMessages.push('Image is required');
       }
   }
}
new EditRecipeForm();

