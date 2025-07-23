<?php
require_once __DIR__ . '/../../includes/authorization.php';

require_once __DIR__ . '/../../db.php';
?>

<div class="form-wrapper">

    <form class="changePasswordForm" method="post" novalidate data-js-form>

        <p class="field">
            <label class="field__label" for="current_password">Current password</label>
            <i class="fa fa-pen" data-edit-target=".changePasswordForm"></i>
            <input
                    class="field__control"
                    id="current_password"
                    name="current_password"
                    type="password"
                    required
                    minlength="8"
                    maxlength="16"
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}"
                    title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter"
                    aria-errormessage="password-errors"
                    disabled
            />
            <span  class="field__errors"  id="current_password-errors" data-js-form-field-errors></span>
        </p>
        <p class="field">
            <label class="field__label" for="new_password">New password</label>
            <input
                    class="field__control"
                    id="new_password"
                    name="new_password"
                    type="password"
                    required
                    minlength="8"
                    maxlength="16"
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}"
                    title="The password must be between 8 and 16 characters long, include at least one number, one lower case letter and one upper case letter"
                    aria-errormessage="password-errors"
                    disabled
            />
            <span  class="field__errors"  id="new_password-errors" data-js-form-field-errors></span>
        </p>
        <p class="field">
            <label class="field__label" for="confirm_new_password">Confirm new password</label>
            <input
                    class="field__control"
                    id="confirm_new_password"
                    name="confirm_new_password"
                    type="password"
                    minlength="8"
                    maxlength="16"
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,16}"
                    required
                    aria-errormessage="confirm_password-errors"
                    disabled
            />
            <span  class="field__errors"  id="confirm_new_password-errors" data-js-form-field-errors></span>
        </p>
        <div class="reset-button">
            <button type="reset">Reset changes</button>
        </div>
        <div class="submit-button">
            <button type="submit">Confirm changes</button>
        </div>
    </form>
</div>

<script type="module" src="/profile/password_change/change_password.js"></script>
