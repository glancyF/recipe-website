<?php
require_once __DIR__ . '/../../includes/authorization.php';
$user = requireAuth();
require_once __DIR__ . '/../../db.php';

?>
<div class="form-wrapper">
<form class="settingsForm" method="post" novalidate data-js-form>
    <p class="field">

        <label class="field__label" for="username">Username</label>

        <i class="fa fa-pen" data-edit-target="#username"></i>
        <input
            class="field__control"
            id="username"
            name="username"
            required
            minlength="3"
            maxlength="12"
            title="Username must start with a letter and contain only letters, numbers, underscores, or hyphens"
            aria-errormessage="username-errors"
            disabled
            value="<?= htmlspecialchars($user['username']) ?>"
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
                readonly
                disabled
                value="<?= htmlspecialchars($user['email']) ?>"
        />
        <span  class="field__errors"  id="email-errors" data-js-form-field-errors></span>
    </p>
    <fieldset class="radios">

        <legend class="radios__legend">Your gender</legend>

        <i class="fa fa-pen" data-edit-target="input[name=gender]"></i>

        <input
                class="radios__control"
                id="male"
                name="gender"
                type="radio"
                value="Male"
                required
                aria-errormessage="gender-errors"
                disabled
                <?= $user['gender'] === 'Male' ? 'checked' : '' ?>
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
                disabled
                <?= $user['gender'] === 'Female' ? 'checked' : '' ?>
        />
        <label class="radios__label" for="female">Female</label>
        <span  class="field__errors"  id="gender-errors" data-js-form-field-errors></span>
    </fieldset>
    <div class="reset-button">
    <button type="reset">Reset changes</button>
    </div>
    <div class="submit-button">
    <button type="submit">Confirm changes</button>
    </div>
</form>
</div>

<script type="module" src="/profile/settings/settings.js"></script>