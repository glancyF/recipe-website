<?php
$loggedIn = isset($_COOKIE['auth_token']);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title> <?= $pageTitle ?? 'Main'; ?> </title>
    <link rel="stylesheet" href="/base.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />


</head>
<body>
<header>
    <div class="logo-container">
        <a href="/main/main.php">
            <img src="/images/logo2.png" alt="Logo icon" class="logo">
        </a>
        <button class="burger" id="burger-btn">
            <span></span><span></span><span></span>
        </button>
    </div>

    <nav id="main-nav">
        <ul class="nav">
            <li><a href="/mostPopular/mostPopular.php">Most popular</a></li>
            <li><a href="/allRecipes/allRecipe.php">All recipes</a></li>
            <li><a href="/Category/category.php">Category</a></li>
            <?php if ($loggedIn): ?>
                <li><a href="/AddRecipe/addRecipe.php">Add a recipe</a></li>
                <li><a href="/profile/profile.php">My Account</a></li>
            <?php else: ?>
                <li><a href="/login/auth.php">Log in</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>

