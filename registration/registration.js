
export class FormsValidation {
    selectors = {
        form: '[data-js-form]',
        fieldErrors: '[data-js-form-field-errors]',
    }
    errorMessages = {
        valueMissing: () => 'Please, enter that field',
        patternMismatch: (field) => field.title || 'Invalid format',
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
        // необязательная проверка, так как в случае отсутствия ошибок, мэп обновит поле. Оставленно во избежание мерцаний или залипаний ДОМ, и якобы оптимизации
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
    getEndpoint(){
        return '../registration/registration.php'
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
        return '/main/index.php';
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
        console.log('>>> Sending data to:', this.getEndpoint());//Debug
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(this.getEndpoint(), {
                method: 'POST',
                body: formData,
            });

            const text = await response.text();

            try {
                const result = JSON.parse(text);
                if (result.status === 'error') {
                    alert(result.message || 'Something went wrong');
                    submitButton.disabled = false;
                } else if (result.status === 'success') {
                    // alert(result.message || 'Registration successful!');
                    window.location.href = this.getSuccessRedirect()
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


