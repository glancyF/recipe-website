import {FormsValidation} from "../registration/registration.js";

class LoginValidation extends FormsValidation{
    constructor() {
        super();
        this.init()
    }
    init() {
        document.addEventListener('blur', (event) => {
            this.onBlur(event)
        },{capture: true})
        document.addEventListener('submit', (event) => this.onSubmit(event))

    }
    controlPassword(fieldControlElement, errorMessages) {

    }

    getEndpoint() {
        return '../login/login.php'
    }
    bindEvents() {


    }
}
new LoginValidation()