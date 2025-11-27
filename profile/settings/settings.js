import { FormsValidation } from "../../registration/registration.js";

export class SettingsForm extends FormsValidation {
    lockOnInit() {
        document.querySelectorAll('[data-js-lock]').forEach(block => {
            block.classList.add('locked');
            block.setAttribute('aria-disabled', 'true');

            block.querySelectorAll('input, textarea, select').forEach(el => {
                if (el.matches('input[type="text"]')) {
                    el.readOnly = true;
                } else {
                    el.disabled = true;
                }
            });
        });
    }

    editProfile() {
        document.querySelectorAll('.fa-pen[data-edit-target]').forEach(icon => {
            icon.addEventListener('click', () => {
                const block = icon.closest('[data-js-lock]');
                if (!block) return;

                block.classList.remove('locked');
                block.classList.add('editing');
                block.removeAttribute('aria-disabled');

                const sel = icon.getAttribute('data-edit-target') || 'input,textarea,select';
                const inputs = block.querySelectorAll(sel);

                inputs.forEach(el => {
                    if (el.matches('input[type="text"], input[type="email"], textarea')) {
                        el.readOnly = false;
                        el.classList.add('field__control--editing');
                    } else {
                        el.disabled = false;
                    }
                });

                inputs[0]?.focus();
            });
        });
    }

    resetChanges() {
        const form = document.querySelector('.settingsForm');
        form?.addEventListener('reset', () => {
            setTimeout(() => {
                form.querySelectorAll('.field__errors').forEach(el => el.textContent = '');
                form.querySelectorAll('[data-js-lock]').forEach(block => {
                    block.classList.add('locked');
                    block.classList.remove('editing');
                    block.setAttribute('aria-disabled', 'true');

                    block.querySelectorAll('input, textarea, select').forEach(el => {
                        if (el.matches('input[type="text"], input[type="email"], textarea')) {
                            el.readOnly = true;
                            el.classList.remove('field__control--editing');
                        } else {
                            el.disabled = true;
                        }
                    });
                });
            });
        });
    }
    controlPassword(fieldControlElement, errorMessages) {

    }

    getEndpoint()        { return 'profile/settings/update-profile.php'; }
    getSuccessRedirect() { return 'profile/profile.php'; }

    init() {
        this.lockOnInit();
        this.editProfile();
        super.bindEvents();
        this.resetChanges();
    }
}


document.addEventListener('DOMContentLoaded', () => new SettingsForm().init());
