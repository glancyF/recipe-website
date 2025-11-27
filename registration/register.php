<?php
session_start();
$pageTitle ='Register';
$extra_css = '<link rel="stylesheet" href="registration/formregister.css">';
$flash  = $_SESSION['flash_register'] ?? null;
unset($_SESSION['flash_register']);
$old    = $flash['old']    ?? [];
$errors = $flash['errors'] ?? [];
$general= $flash['general']?? null;
include "../includes/header.php";
?>

<div class="form-wrapper">
    <form id="regForm"  method="post" action="registration/registrationApi.php"  novalidate data-js-form>
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
                    value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                <?= isset($errors['username']) ? 'aria-invalid="true"' : '' ?> >
            <span  class="field__errors"  id="username-errors" data-js-form-field-errors>
                <?= isset($errors['username']) ? htmlspecialchars($errors['username']) : '' ?>
            </span>
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
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>
            >
            <span  class="field__errors"  id="email-errors" data-js-form-field-errors>
                <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
            </span>
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
                    title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
                    aria-errormessage="password-errors"
                    <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>
            >
            <span  class="field__errors"  id="password-errors" data-js-form-field-errors>
                <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
            </span>
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
                    title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter. English letters only"
                    required
                    aria-errormessage="confirm_password-errors"
                    <?= isset($errors['confirm_password']) ? 'aria-invalid="true"' : '' ?>
            >
            <span  class="field__errors"  id="confirm_password-errors" data-js-form-field-errors>
                <?= isset($errors['confirm_password']) ? htmlspecialchars($errors['confirm_password']) : '' ?>
            </span>
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
                    <?= (($old['gender'] ?? '') === 'Male') ? 'checked' : '' ?>
            >

            <label class="radios__label" for="male">Male</label>
            <input
                    class="radios__control"
                    id="female"
                    name="gender"
                    type="radio"
                    value="Female"
                    required
                    aria-errormessage="gender-errors"
                <?= (($old['gender'] ?? '') === 'Female') ? 'checked' : '' ?>
            >
            <label class="radios__label" for="female">Female</label>
            <span  class="field__errors"  id="gender-errors" data-js-form-field-errors>
                <?= isset($errors['gender']) ? htmlspecialchars($errors['gender']) : '' ?>
            </span>
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
                    <?= !empty($old['agreement']) ? 'checked' : '' ?>
            >
            <span  class="field__errors"  id="agreement-errors" data-js-form-field-errors>
                <?= isset($errors['agreement']) ? htmlspecialchars($errors['agreement']) : '' ?>
            </span>
        </div>
        <button type="submit">Register</button>
    </form>
</div>

<script type="module" src="registration/register.js"></script>
<?php
include "../includes/footer.php";
?>
