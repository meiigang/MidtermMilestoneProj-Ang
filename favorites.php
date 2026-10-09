<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();
$recipes = (new Recipe($pdo))->favoritesFor($userId);
$pageTitle = 'Saved recipes';
$currentPage = 'favorites';
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro compact-intro"><div><p class="eyebrow">Your little cookbook</p><h1>Saved for<br><em>later.</em></h1><p class="intro-copy">Keep the recipes you want to make when the afternoon calls for something sweet.</p></div><a class="text-link" href="index.php">Back to discover <span aria-hidden="true">←</span></a></section>
<section class="section-heading"><div><p class="eyebrow">Your collection</p><h2>Recipes you saved</h2></div><span class="result-count"><?= count($recipes) ?> saved</span></section>
<?php if ($recipes === []): ?><div class="empty-state"><span class="empty-mark">♥</span><h2>Your saved shelf is empty.</h2><p>Tap Save on a recipe you want to come back to.</p><a class="text-link" href="index.php">Browse recipes ↗</a></div><?php else: ?><div class="recipe-grid"><?php foreach ($recipes as $recipe): require __DIR__ . '/includes/recipe-card.php'; endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
