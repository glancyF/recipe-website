import {FormsValidation} from "../../registration/registration.js";


class ChangePassForm extends FormsValidation {
    constructor() {
        super();

    }
    init(){
        this.bindEvents()
        this.lockOnInit();
    }
    getEndpoint() {
        return 'profile/password_change/change_password.php';
    }
    lockOnInit() {
        const form = document.querySelector('.changePasswordForm');
        if (!form) return;
        form.querySelectorAll('input[type="password"]').forEach(inp => {
            inp.disabled = true;
        });
    }
    getSuccessRedirect() {
        return 'profile/profile.php';
    }
    enablePasswordsFields () {
        const icon = document.querySelector('.fa-pen[data-edit-target]');
        if(!icon) return;
        icon.addEventListener('click', () => {
            const form = document.querySelector(icon.dataset.editTarget);
            if(!form) return;
            form.querySelectorAll('input[type="password"]').forEach(input => {
                input.disabled = false;
                input.classList.add('field__control--editing');
            });
            form.querySelector('input[type="password"]')?.focus();
        });
}

resetChanges() {
    const form = document.querySelector('.changePasswordForm');
    form.addEventListener('reset', () => {
        setTimeout(() => {
            form.querySelectorAll('.field__errors').forEach(el => el.textContent = '');
            form.querySelectorAll('input[type="password"]').forEach(input => {
                input.value = '';
                input.classList.remove('field__control--editing');
                input.disabled = true;
            });
        });
    });

}
controlPassword(fieldControlElement, errorMessages){
        const isConfirm = fieldControlElement.id ==='confirm_new_password';
        if(isConfirm) {
            const pwd = document.getElementById('new_password').value;
            const confirmPwd = fieldControlElement.value;
            if (pwd !== confirmPwd) {
                errorMessages.push(this.errorMessages.passwordMismatch());
            }
        }
}
bindEvents() {
    super.bindEvents();
    this.enablePasswordsFields();
    this.resetChanges();


}
}

new ChangePassForm()
