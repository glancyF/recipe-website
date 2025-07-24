import {FormsValidation} from "../registration/registration.js";

class LoginValidation extends FormsValidation{
    constructor() {
        super();
        // this._eventsBound = false; подумай завтра на счет этого!
    }
    init() {
        this.bindEvents();

    }
    controlPassword(fieldControlElement, errorMessages) {

    }
    bindEvents() {
        // чтобы два раза алерт не выскакивал
        if (this._eventsBound) return;
        this._eventsBound = true;
        document.addEventListener('blur', (event) => {
            this.onBlur(event)
        },{capture: true})
        document.addEventListener('submit', (event) => this.onSubmit(event))
    }

    getEndpoint() {
        return '../login/login.php'
    }

}
new LoginValidation()