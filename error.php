<?php

$pageTitle = "Error $code";
include __DIR__ . '/includes/header.php';
?>
    <div class="error-page">
        <h1>Error <?= htmlspecialchars($code) ?></h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/" class="btn">Back to home</a>
    </div>
<?php
include __DIR__ . '/includes/footer.php';
