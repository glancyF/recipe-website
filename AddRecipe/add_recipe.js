import { FormsValidation } from "../registration/registration.js";

export class AddRecipeForm extends FormsValidation {

    static MAX_INGREDIENTS = 86;
    static MAX_IMAGE_MB    = 2;
    static ING_PATTERN     = /^[A-Za-z0-9+\-,.%:;() ]+(,[A-Za-z0-9+\-,.%:;() ]+)*$/;

    constructor() {
        super();
    }


    controlRecipeImage(fieldControlElement, errorMessages) {
        const file = fieldControlElement.files[0];
        if (!file) {
            errorMessages.push("Image is required");
            return;
        }

        const maxBytes = AddRecipeForm.MAX_IMAGE_MB * 1024 * 1024;
        const validTypes = ["image/jpeg", "image/png", "image/jpg"];

        if (!validTypes.includes(file.type)) {
            errorMessages.push("Only JPG, PNG, JPEG formats allowed");
        }
        if (file.size > maxBytes) {
            errorMessages.push(`Image must be smaller than ${AddRecipeForm.MAX_IMAGE_MB}MB`);
        }
    }

    controlName(fieldControlElement, errorMessages) {
        const pattern = /^[A-Za-z\s]+$/;
        if (!pattern.test(fieldControlElement.value.trim())) {
            errorMessages.push("Only letters and spaces are allowed");
        }
    }

    controlDescription(fieldControlElement, errorMessages) {
        if (!AddRecipeForm.ING_PATTERN.test(fieldControlElement.value.trim())) {
            errorMessages.push("Invalid symbols");
        }
    }

    controlInstruction(fieldControlElement, errorMessages) {
        const pattern = /^[A-Za-z0-9+\-,.%:;() ]+$/;
        if (!pattern.test(fieldControlElement.value.trim())) {
            errorMessages.push("Invalid symbols.");
        }
    }

    setupIngredientControls({ initialValues = [], restoreOnReset = false } = {}) {
        const input        = document.getElementById("IngredientInput");
        const addBtn       = document.getElementById("addIngredientBtn");
        const list         = document.getElementById("ingredientsList");
        const hiddenInput  = document.getElementById("ingredientsHiddenInput");
        const errorBox     = document.getElementById("IngredientInput-errors");

        const showError = (msg) => {
            if (!errorBox) return;
            errorBox.innerHTML = `<span class="field__errors">${msg}</span>`;
            input?.setAttribute("aria-invalid", "true");
        };
        const clearError = () => {
            if (!errorBox) return;
            errorBox.innerHTML = "";
            input?.removeAttribute("aria-invalid");
        };

        const updateHidden = () => {
            const ingredients = [...list.querySelectorAll(".ingredient-text")]
                .map((el) => el.textContent.trim())
                .filter(Boolean);
            hiddenInput.value = ingredients.join(";");
        };

        const addIngredient = (value) => {
            const li = document.createElement("li");
            li.innerHTML = `
        <span class="ingredient-text">${value}</span>
        <button type="button" class="remove-ingredient" aria-label="Remove ingredient">
          <i class="fa fa-times"></i>
        </button>
      `;
            list.appendChild(li);
            updateHidden();
            if (input) {
                input.value = "";
                input.required = false;
            }
        };

        const tryAddFromInput = () => {
            const value = input.value.trim();
            if (!value) return;


            if (list.querySelectorAll("li").length >= AddRecipeForm.MAX_INGREDIENTS) {
                showError(`Maximum ${AddRecipeForm.MAX_INGREDIENTS} ingredients allowed`);
                return;
            }

            if (!AddRecipeForm.ING_PATTERN.test(value)) {
                showError("Ingredient contains invalid characters");
                return;
            }

            const existing = new Set(
                [...list.querySelectorAll(".ingredient-text")].map((el) =>
                    el.textContent.trim().toLowerCase()
                )
            );
            if (existing.has(value.toLowerCase())) {
                showError("Ingredient already added");
                return;
            }

            clearError();
            addIngredient(value);
        };


        if (initialValues.length) {
            list.innerHTML = "";
            initialValues.forEach((v) => addIngredient(v));
            if (input) input.required = false;
        }

        addBtn?.addEventListener("click", tryAddFromInput);

        list?.addEventListener("click", (e) => {
            if (e.target.closest(".remove-ingredient")) {
                e.target.closest("li").remove();
                updateHidden();
                if (list.querySelectorAll("li").length === 0 && input) {
                    input.required = true;
                }
            }
        });


        if (restoreOnReset) {
            const form = document.querySelector("form");
            form?.addEventListener("reset", () => {
                list.innerHTML = "";
                initialValues.forEach((v) => addIngredient(v));
                updateHidden();
                clearError();
                if (input) {
                    input.value = "";
                    input.required = initialValues.length === 0;
                }
            });
        }
    }


    Controls(fieldControlElement, errorMessages) {
        if (fieldControlElement.id === "recipeImage") this.controlRecipeImage(fieldControlElement, errorMessages);
        if (fieldControlElement.id === "name") this.controlName(fieldControlElement, errorMessages);
        if (fieldControlElement.id === "description") this.controlDescription(fieldControlElement, errorMessages);
        if (fieldControlElement.id === "instruction") this.controlInstruction(fieldControlElement, errorMessages);
    }

    validateField(fieldControlElement) {
        const errors = fieldControlElement.validity;
        const msgs = [];
        this.Controls(fieldControlElement, msgs);

        if (fieldControlElement.id === "ingredientsHiddenInput") {
            if (!fieldControlElement.value.trim()) msgs.push("Please add at least one ingredient");
        }

        Object.entries(this.errorMessages).forEach(([type, getMsg]) => {
            if (errors[type]) msgs.push(getMsg(fieldControlElement));
        });

        this.manageErrors(fieldControlElement, msgs);
        const ok = msgs.length === 0;
        fieldControlElement.ariaInvalid = !ok;
        return ok;
    }


    ImagePreview() {
        const imagePreview   = document.getElementById("imagePreview");
        const imageInput     = document.getElementById("recipeImage");
        const removeBtn      = document.getElementById("removeImageBtn");
        const previewWrapper = document.getElementById("imagePreviewWrapper");

        if (!imageInput || !imagePreview) return;

        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
            if (file && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    previewWrapper.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = "none";
                imagePreview.src = "";
            }
        });

        removeBtn?.addEventListener("click", () => {
            imageInput.value = "";
            imagePreview.src = "";
            previewWrapper.style.display = "none";
        });
    }


    ResetChanges() {
        const form = document.querySelector('form');
        if (!form) return;
        form.addEventListener('reset', () => {
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

    init(opts = {}) {
        const {
            initialIngredients = [],
            restoreOnReset = false,
            useBaseReset = true
        } = opts;

        super.init();

        this.setupIngredientControls({ initialValues: initialIngredients, restoreOnReset });
        this.ImagePreview();

        if (useBaseReset) {
            this.ResetChanges();
        }
    }

    getEndpoint() { return "/AddRecipe/add_recipe.php"; }
    getSuccessRedirect() { return "/main/index.php"; }
}
