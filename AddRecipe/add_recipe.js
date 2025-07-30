import {FormsValidation} from "../registration/registration.js";

export class AddRecipeForm extends FormsValidation {
    constructor() {
        super();

    }

    controlRecipeImage(fieldControlElement,errorMessages){
        const file = fieldControlElement.files[0]
        if(!file){
            errorMessages.push('Image is required');
            return;
        }
        const maxSizeMB = 2;
        const maxSizeBytes = maxSizeMB * 1024*1024;

        const validTypes =['image/jpeg','image/png','image/jpg'];
        if(!validTypes.includes(file.type)){
            errorMessages.push("Only JPG,PNG,JPEG formats allowed");
        }
        if(file.size>maxSizeBytes){
            errorMessages.push(`Image must be smaller than ${maxSizeMB}MB`);
        }
    }
    controlName(fieldControlElement,errorMessages){
        const pattern = /^[A-Za-z\s]+$/;
        if(!pattern.test(fieldControlElement.value.trim())){
            errorMessages.push()
        }
    }

    controlDescription(fieldControlElement,errorMessages){
        const pattern = /^[A-Za-z0-9+\-,.%:;() ]+(,[A-Za-z0-9+\-,.%:;() ]+)*$/;
        if(!pattern.test(fieldControlElement.value.trim())){
            errorMessages.push('Invalid symbols')
        }
    }

    bindIngredientControls(){
        const input = document.getElementById('IngredientInput');
        const addBtn = document.getElementById('addIngredientBtn');
        const list = document.getElementById('ingredientsList');
        const hiddenInput = document.getElementById('ingredientsHiddenInput');
        const updateHidden = () => {
            const ingredients = [...list.querySelectorAll('li')]
                .map(li => li.querySelector('.ingredient-text')?.textContent.trim())
                .filter(Boolean);
            hiddenInput.value = ingredients.join(';');
        };

        const showIngredientError = (message) => {
            const errorElement = document.getElementById('IngredientInput-errors');
            errorElement.innerHTML =`<span class="field__errors">${message}</span>`;
            input.setAttribute('aria-invalid','true');
        };
        const clearIngredientError = () =>{
            const errorElement = document.getElementById('IngredientInput-errors');
            errorElement.innerHTML='';
            input.removeAttribute('aria-invalid');
        }

        addBtn?.addEventListener('click',()=> {
            const value = input.value.trim();
            const pattern = /^[A-Za-z0-9+\-,.%:;() ]+(,[A-Za-z0-9+\-,.%:;() ]+)*$/;

            if (!value) {

                return;
            }
            if (!pattern.test(value)) {
                showIngredientError('Ingredient contains invalid characters');
                return;
            }
            const existingIngredients =[...list.querySelectorAll('.ingredient-text')]
                .map(el => el.textContent.trim().toLowerCase());
            if(existingIngredients.includes(value.toLowerCase())){
                showIngredientError("Ingredient already added");
                return;
            }
            clearIngredientError();

            const li = document.createElement('li');
            li.innerHTML = `
            <span class="ingredient-text">${value}</span>
            <button type="button" class="remove-ingredient" aria-label="Remove ingredient">
                <i class="fa fa-times"></i>
            </button>
        `;
            list.appendChild(li)
            input.value='';
            updateHidden();
            input.required = false;
        });
        list?.addEventListener('click', (e) =>{
            const {target} = e;
            if(target.closest('.remove-ingredient'))
            {
                const li = target.closest('li');
                li.remove();
                updateHidden();
                if (list.querySelectorAll('li').length === 0) {
                    input.required = true;
                }

            }
        });
    }

    controlInstruction(fieldControlElement,errorMessages){
        const pattern = /^[A-Za-z0-9+\-,.%:;() ]+$/;
        if (!pattern.test(fieldControlElement.value.trim())) {
            errorMessages.push('Invalid symbols.');
        }
    }
    Controls(fieldControlElement,errorMessages){
        if(fieldControlElement.id === 'recipeImage'){
            this.controlRecipeImage(fieldControlElement,errorMessages);
        }
        if(fieldControlElement.id === 'name'){
            this.controlName(fieldControlElement,errorMessages);
        }
        if(fieldControlElement.id==='description'){
            this.controlDescription(fieldControlElement,errorMessages);
        }
        if(fieldControlElement.id==='instruction'){
            this.controlInstruction(fieldControlElement,errorMessages);
        }
    }
    validateField(fieldControlElement) {
        const errors = fieldControlElement.validity
        const errorMessages = []
        this.Controls(fieldControlElement,errorMessages)
        if(fieldControlElement.id === 'IngredientInput'){
            if(!fieldControlElement.value.trim())
            {
                errorMessages.push('Please add at least one ingredient');
            }
        }
        Object.entries(this.errorMessages).forEach( ([errorType,getErrorMessage])=> {
            if(errors[errorType])
            {
                errorMessages.push(getErrorMessage(fieldControlElement));
            }
        });
        this.manageErrors(fieldControlElement,errorMessages)
        const isValid = errorMessages.length === 0
        fieldControlElement.ariaInvalid = !isValid
        return isValid
    }
    ImagePreview() {
        const imagePreview = document.getElementById('imagePreview');
        const imageInput = document.getElementById('recipeImage');
        const removeBtn =document.getElementById('removeImageBtn');
        const previewWrapper = document.getElementById('imagePreviewWrapper');
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', () => {
                const file = imageInput.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        imagePreview.src = e.target.result;
                        previewWrapper.style.display ='block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                    imagePreview.src = '';
                }
            });
            removeBtn.addEventListener('click',()=>{
                imageInput.value='';
                imagePreview.src='';
                previewWrapper.style.display='none';
            });
        }
    }
    ResetChanges(){
        const form = document.querySelector('form');
        if(!form) return;
        form.addEventListener('reset',()=>{
           const list = document.getElementById('ingredientsList');
           const hiddenInput = document.getElementById('ingredientsHiddenInput');
           const input = document.getElementById('IngredientInput');
            if (list) list.innerHTML = '';
            if (hiddenInput) hiddenInput.value = '';
            if (input) {
                input.value = '';
                input.required = true;
            }
        });
    }

    init() {
        super.init();
        this.bindIngredientControls();
        this.ImagePreview();
        this.ResetChanges();
    }

    getEndpoint() {
        return '/AddRecipe/add_recipe.php';
    }
    getSuccessRedirect(){
        return '/main/index.php';
    }


}


