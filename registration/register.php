<?php
$pageTitle ='Register';
$extra_css = '<link rel="stylesheet" href="formregister.css">';
include "../includes/header.php";
?>

<div class="form-wrapper">
<form id="regForm"  method="post"  novalidate data-js-form>
    <p class="field">
        <label class="field__label" for="username">Username</label>
        <input
                class="field__control"
                id="username"
                name="username"
                required
                minlength="3"
                maxlength="12"
                title="Username must start with a letter and contain only letters, numbers, underscores, or hyphens"
                aria-errormessage="username-errors"
                />
        <span  class="field__errors"  id="username-errors" data-js-form-field-errors></span>
    </p>
    <p class="field">
        <label class="field__label" for="email">Email</label>
        <input
                class="field__control"
                id="email"
                name="email"
                type="email"
                required
                minlength="2"
                maxlength="64"
                aria-errormessage="email-errors"
        />
        <span  class="field__errors"  id="email-errors" data-js-form-field-errors></span>
    </p>
    <p class="field">
        <label class="field__label" for="password">Password</label>
        <input
            class="field__control"
            id="password"
            name="password"
            type="password"
            required
            minlength="8"
            maxlength="16"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}"
            title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter"
            aria-errormessage="password-errors"
            />
        <span  class="field__errors"  id="password-errors" data-js-form-field-errors></span>
    </p>
    <p class="field">
        <label class="field__label" for="confirm_password">Confirm password</label>
        <input
                class="field__control"
                id="confirm_password"
                name="confirm_password"
                type="password"
                minlength="8"
                maxlength="16"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}"
                required
                aria-errormessage="confirm_password-errors"
        />
        <span  class="field__errors"  id="confirm_password-errors" data-js-form-field-errors></span>
    </p>
    <fieldset class="radios">
        <legend class="radios__legend">Your gender</legend>
        <input
            class="radios__control"
            id="male"
            name="gender"
            type="radio"
            value="Male"
            required
            aria-errormessage="gender-errors"
            />

        <label class="radios__label" for="male">Male</label>
        <input
                class="radios__control"
                id="female"
                name="gender"
                type="radio"
                value="Female"
                required
                aria-errormessage="gender-errors"
        />
        <label class="radios__label" for="female">Female</label>
        <span  class="field__errors"  id="gender-errors" data-js-form-field-errors></span>
    </fieldset>
    <div class="field checkbox">
        <label class="field__label checkbox__label" for="agreement">Agree with the requirements</label>
        <input
              class="checkbox__control"
              id="agreement"
              name="agreement"
              type="checkbox"
              required
              aria-errormessage="agreement-errors"
        />
        <span  class="field__errors"  id="agreement-errors" data-js-form-field-errors></span>
    </div>
   <button type="submit">Register</button>
</form>
    </div>

<script type="module" src="../registration/register.js"></script>
<?php
include "../includes/footer.php";
?>

