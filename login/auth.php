<?php
$pageTitle ='Login';
$extra_css = '<link rel="stylesheet" href="formlogin.css">';
include "../includes/header.php";
?>

<div class="form-wrapper">
<form id="logForm" method="post" novalidate data-js-form>
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
        <span class="field__errors" id="email-errors" data-js-form-field-errors></span>
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
        title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter,must be in english"
        aria-errormessage="password-errors"
        />
        <span  class="field__errors"  id="password-errors" data-js-form-field-errors></span>
    </p>
    <button type="submit">Log in</button>
    <p class="redirect-text">
        Don't have an account yet?
        Click here to
        <a href="../registration/register.php">register</a>
    </p>
</form>
</div>


<script type="module" src="../login/login.js"></script>
<?php
include "../includes/footer.php";
?>

