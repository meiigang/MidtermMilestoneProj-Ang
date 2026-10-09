<?php
$pageTitle = $pageTitle ?? 'Tamis';
$currentPage = $currentPage ?? '';
$flashMessage = take_flash();
$userName = isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> · Tamis</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php" aria-label="Tamis home">
            <span class="brand-mark">T</span>
            <span>Tamis</span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" aria-label="Open navigation menu">
            <span></span><span></span><span></span>
        </button>
        <nav id="main-navigation" class="site-nav" aria-label="Main navigation">
            <a class="<?= $currentPage === 'home' ? 'is-current' : '' ?>" href="index.php">Discover</a>
            <a class="<?= $currentPage === 'favorites' ? 'is-current' : '' ?>" href="favorites.php">Saved recipes</a>
            <a class="<?= $currentPage === 'create' ? 'is-current' : '' ?>" href="create-recipe.php">Share a recipe</a>
            <span class="nav-divider" aria-hidden="true"></span>
            <span class="nav-user">Hi, <?= e($userName) ?></span>
            <a class="nav-quiet" href="logout.php">Log out</a>
        </nav>
    </div>
</header>
<main class="site-main">
    <?php if ($flashMessage !== null): ?>
        <div class="notice notice-<?= e((string) ($flashMessage['type'] ?? 'success')) ?>" role="status"><?= e((string) ($flashMessage['message'] ?? '')) ?></div>
    <?php endif; ?>
