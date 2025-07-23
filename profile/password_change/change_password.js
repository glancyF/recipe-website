import {FormsValidation} from "../../registration/registration.js";


class ChangePassForm extends FormsValidation {
    constructor() {
        super();

    }
    init(){
        this.bindEvents()
    }
    getEndpoint() {
        return '/profile/password_change/change_password.php';
    }

    getSuccessRedirect() {
        return '/profile/profile.php';
    }
    enablePasswordsFields () {
        const icon = document.querySelector('.fa-pen[data-edit-target]');
        if(!icon) return;
        icon.addEventListener('click', () => {
            const form = document.querySelector(icon.dataset.editTarget);
            if(!form) return;
            form.querySelectorAll('input[type="password"]').forEach(input => {
               input.disabled = false;
               input.classList.add('field__control--editing')
            });
            const first = form.querySelector('input[type="password"]')
            if(first){
                first.focus();
            }
        });
}

resetChanges() {
    const form = document.querySelector('.changePasswordForm')
    form.addEventListener('reset', () =>{
       setTimeout(()=>{
           form.querySelectorAll('.field__errors').forEach(errorEL =>{
              errorEL.textContent=''
           });
           form.querySelectorAll('.field__control--editing').forEach(input =>{
              input.classList.remove('field__control--editing') ;
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
