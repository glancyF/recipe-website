
export class FormsValidation {
    selectors = {
        form: '[data-js-form]',
        fieldErrors: '[data-js-form-field-errors]',
    }
    errorMessages = {
        valueMissing: () => 'Please, enter that field',
        patternMismatch: (field) => field.title || 'Invalid format, only english letters',
        tooShort: ({minLength}) =>  `Too short, min length - ${minLength}`,
        tooLong: ({maxLength}) => `Too long, max length - ${maxLength}`,
        typeMismatch: () => 'Please enter a valid email address',
        passwordMismatch: () => "Passwords dont match",
    }
    constructor() {
        this.init()
    }
    init(){
        this.bindEvents()
    }
    manageErrors(fieldControlElement,errorMessages) {

        const fieldErrorsElement = fieldControlElement.parentElement?.querySelector(this.selectors.fieldErrors)
        if(errorMessages.length === 0){
            fieldErrorsElement.innerHTML = ''
            return
        }
        if (!fieldErrorsElement) return;
        fieldErrorsElement.innerHTML = errorMessages
            .map((message) => `<span class="field__errors">${message}</span>`)
            .join('')

    }
    showServerErrors(form, errors = {}) {
        [...form.elements].forEach(el => {
            el.removeAttribute('aria-invalid');
            el.parentElement
                ?.querySelector(this.selectors.fieldErrors)
                ?.replaceChildren();
        });


        Object.entries(errors).forEach(([name, msg]) => {
            const field = form.elements.namedItem(name);
            if (!field) return;

            field.setAttribute('aria-invalid', 'true');
            const box = field.parentElement?.querySelector(this.selectors.fieldErrors);
            if (box) {
                box.innerHTML = `<span class="field__errors">${msg}</span>`;
            }
        });

        form.querySelector('[aria-invalid="true"]')?.focus();
    }


    clearSensitiveFields(form) {
        form.querySelectorAll('input[type="password"]').forEach(i => i.value = '');

        const file = form.querySelector('input[type="file"]');
        if (file) file.value = '';

        const previewWrapper = document.getElementById('imagePreviewWrapper');
        if (previewWrapper) previewWrapper.style.display = 'none';
        const previewImg = document.getElementById('imagePreview');
        if (previewImg) previewImg.src = '';
    }

    getEndpoint(){
        return 'registration/registration.php'
    }
    controlPassword(fieldControlElement,errorMessages){
        const isPasswordMatch = fieldControlElement.id === 'confirm_password'
        if (isPasswordMatch){
            const pwd = document.getElementById('password').value;
            const confirmPwd = fieldControlElement.value;
            if (pwd !== confirmPwd)
            {
                errorMessages.push(this.errorMessages.passwordMismatch())
            }
        }
    }
    controlUsername(fieldControlElement,errorMessages){
        const pattern = /^[A-Za-z][A-Za-z0-9_-]*$/;
        if (!pattern.test(fieldControlElement.value)) {
            errorMessages.push(fieldControlElement.title || 'Invalid username format');
        }
    }
    validateField(fieldControlElement){
        const errors = fieldControlElement.validity
        const errorMessages = []
        if (fieldControlElement.id === 'username') {
            this.controlUsername(fieldControlElement,errorMessages)
        }
        Object.entries(this.errorMessages).forEach( ([errorType,getErrorMessage])=> {
            if(errors[errorType])
            {
                errorMessages.push(getErrorMessage(fieldControlElement))
            }

        })
        if (['confirm_password', 'confirm_new_password'].includes(fieldControlElement.id)) {
            this.controlPassword(fieldControlElement, errorMessages);
        }
        this.manageErrors(fieldControlElement,errorMessages)
        const isValid = errorMessages.length === 0
        fieldControlElement.ariaInvalid = !isValid
        return isValid
    }
    onBlur(event) {
        const { target } = event
        const isFormField = target.closest(this.selectors.form)
        const isRequired = target.required

        if (isFormField && isRequired ){
            this.validateField(target)
        }
    }

    onChange(event) {
        const {target} = event
        const isRequired = target.required
        const isToggleType = ['radio','checkbox'].includes(target.type)
        if (isToggleType && isRequired){
            this.validateField(target)
        }

    }
    getSuccessRedirect(){
        return 'main/main.php';
    }
    async onSubmit(event) {
        event.preventDefault();

        const { target } = event;
        const isFormElement = target.matches(this.selectors.form);

        if (!isFormElement) return;

        const requiredControlElements = [...target.elements].filter(({ required }) => required);
        requiredControlElements.forEach(el => {
            if (el.disabled) el.disabled = false;
        });
        let isFormValid = true;
        let firstInvalidFieldControl = null;

        requiredControlElements.forEach((element) => {
            const isFieldValid = this.validateField(element);
            if (!isFieldValid) {
                isFormValid = false;
                if (!firstInvalidFieldControl) {
                    firstInvalidFieldControl = element;
                }
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            firstInvalidFieldControl.focus();
            return;
        }



        const formData = new FormData(target);
        const submitButton = target.querySelector('button[type=submit]');
        console.log('>>> Sending data to:', this.getEndpoint());
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(this.getEndpoint(), {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json',}
            });

            const text = await response.text();

            try {
                const result = JSON.parse(text);
                if (result.status === 'error') {
                    this.showServerErrors(target, result.errors || {});
                    this.clearSensitiveFields(target);
                    if (result.message) {
                        const message = typeof result.message === 'string'
                            ? result.message
                            : (result.message?.message || JSON.stringify(result.message));
                        if(message === 'Username already exists' || message === 'Username already taken'){
                            const name = document.querySelector('#username');
                            this.manageErrors(name,[message]);
                        }
                        else if(message === 'Current password is incorrect'){
                            const pswd = document.querySelector('#current_password')
                            this.manageErrors(pswd, [message])
                        }
                        else if(message ==='Email already exists' || message==='Invalid credentials or account doesn\'t exist' || message==='Invalid email format'){
                            const email = document.querySelector('#email');
                            this.manageErrors(email, [message]);
                        }
                        else{
                            alert(message)
                        }
                    }

                    submitButton && (submitButton.disabled = false);
                    return;
                } else if (result.status === 'success') {

                    window.location.href = this.getSuccessRedirect()
                    return;
                }
            } catch (err) {
                console.error('Server did not return valid JSON. Full response:', text);
                alert('Unexpected server response. Check console for details.');
                submitButton.disabled = false;
            }
        } catch (error) {
            console.error('Network error', error);
            alert('Network error occurred');
            submitButton.disabled = false;
        }
    }


    bindEvents() {
        document.addEventListener('blur', (event) => {
            this.onBlur(event)
        },{capture: true})
        document.addEventListener('change',(event) => this.onChange(event))
        document.addEventListener('submit', (event) => this.onSubmit(event))
    }
}

