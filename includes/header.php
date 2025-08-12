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
    <nav>
        <ul class="nav">
            <li><a href="/main/index.php"><i class="main"></i>
                <img src="/images/logo2.png" alt="Logo icon" class="logo">
                </a></li>
            <li><a href="/mostPopular/mostPopular.php"><i class="most-popular"></i>Most popular</a></li>
            <li><a href="/allRecipes/allRecipe.php"><i class="all-recipes"></i>All recipes</a></li>
            <li><a href="/Category/category.php"><i class="category"></i>Category</a></li>
            <?php if ($loggedIn): ?>
            <li><a href="/AddRecipe/addRecipe.php"><i class="ad-recipe"></i>Add a recipe</a></li>
            <li><a href="/profile/profile.php"><i class="login"></i>My Account</a></li>
            <?php else: ?>
            <li><a href="/login/auth.php"><i class="login"></i>Log in</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>

