<?php
session_start();
$pageTitle ='Login';
$extra_css = '<link rel="stylesheet" href="login/formlogin.css">';
$flash  = $_SESSION['flash_login'] ?? null;
unset($_SESSION['flash_login']);
$old    = $flash['old']    ?? [];
$errors = $flash['errors'] ?? [];
$general= $flash['general']?? null;
include "../includes/header.php";
?>

<div class="form-wrapper">
<form id="logForm" method="post" action="login/loginApi.php" novalidate data-js-form>
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
        <span class="field__errors" id="email-errors" data-js-form-field-errors>
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
    <button type="submit">Log in</button>
    <p class="redirect-text">
        Don't have an account yet?
        Click here to
        <a href="registration/register.php">register</a>
    </p>
</form>
</div>


<script type="module" src="login/login.js"></script>
<?php
include "../includes/footer.php";
?>

