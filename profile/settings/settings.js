import {FormsValidation} from "../../registration/registration.js";



export class SettingsForm extends FormsValidation {
    constructor() {
        super()

    }

    editProfile(){
        document.querySelectorAll('.fa-pen').forEach(icon =>{

            const fieldWrapper = icon.closest('.field') || icon.closest('fieldset')
            const input = fieldWrapper?.querySelector('input,textarea,select')
            if(!input){
                return;
            }
            icon.addEventListener('click',()=>{
                if(input.type==='radio'){
                    const radios = document.querySelectorAll(`[name="${input.name}"]`);
                    radios.forEach(radio => radio.disabled = false);
                }else{
                    input.disabled = false
                    input.dispatchEvent(new Event('blur'));
                    input.classList.add('field__control--editing')
                    input.focus()
                }
            });

        });
    }

    resetChanges() {
        const form = document.querySelector('.settingsForm');
        form.addEventListener('reset',()=>{
            setTimeout(()=>{
                form.querySelectorAll('.field__errors').forEach(errorEl =>{
                    errorEl.textContent='';
                });
                form.querySelectorAll('.field__control--editing').forEach(input =>{
                    input.classList.remove('field__control--editing');
                });

            });

        });
    }
    controlPassword(fieldControlElement, errorMessages) {

    }
    getSuccessRedirect(){
        return '/profile/profile.php';
    }
    getEndpoint() {
        return '/profile/settings/update-profile.php';
    }

    init(){
        this.editProfile();
        super.bindEvents();
        this.resetChanges();
    }
}
new SettingsForm();