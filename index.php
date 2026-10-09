<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();

$search = trim((string) ($_GET['search'] ?? ''));
$categoryId = filter_var($_GET['category'] ?? null, FILTER_VALIDATE_INT);
$categoryId = $categoryId !== false && $categoryId > 0 ? $categoryId : null;
$recipeService = new Recipe($pdo);
$categoryService = new Category($pdo);
$recipes = $recipeService->all($search, $categoryId, $userId);
$popularRecipes = $recipeService->popular(3, $userId);
$categories = $categoryService->all();
$pageTitle = 'Discover recipes';
$currentPage = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro home-intro">
    <div>
        <p class="eyebrow">From our barangay kitchen</p>
        <h1>Sweet recipes,<br><em>shared warmly.</em></h1>
        <p class="intro-copy">Tamis is a little table for Filipino home cooks. Find the merienda you remember, then leave a recipe for someone else to discover.</p>
    </div>
    <a class="button button-primary" href="create-recipe.php">Share your recipe <span aria-hidden="true">↗</span></a>
</section>
<section class="discovery-layout">
    <div class="feed-column">
        <form class="filter-bar" method="get">
            <label class="search-field"><span aria-hidden="true">⌕</span><input type="search" name="search" value="<?= e($search) ?>" placeholder="Search by recipe or ingredient memory" aria-label="Search recipes"></label>
            <label class="select-field"><span class="sr-only">Category</span><select name="category" onchange="this.form.submit()"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
            <button class="button button-dark" type="submit">Search</button>
        </form>
        <div class="section-heading"><div><p class="eyebrow">The latest from the table</p><h2><?= $search !== '' || $categoryId !== null ? 'Recipes you might like' : 'Freshly shared' ?></h2></div><span class="result-count"><?= count($recipes) ?> recipe<?= count($recipes) === 1 ? '' : 's' ?></span></div>
        <?php if ($recipes === []): ?><div class="empty-state"><span class="empty-mark">✦</span><h2>No recipes found.</h2><p>Try a different search or category, or be the first to share something sweet.</p><a class="text-link" href="create-recipe.php">Share a recipe ↗</a></div><?php else: ?><div class="recipe-grid"><?php foreach ($recipes as $recipe): require __DIR__ . '/includes/recipe-card.php'; endforeach; ?></div><?php endif; ?>
    </div>
    <aside class="popular-panel"><p class="eyebrow">Community favorites</p><h2>Popular recipes</h2><p class="aside-copy">The recipes getting the most love in the barangay.</p><?php if ($popularRecipes === []): ?><p class="muted">Popular recipes will appear as the community starts cooking.</p><?php else: ?><div class="popular-list"><?php foreach ($popularRecipes as $number => $recipe): ?><a class="popular-item" href="recipe.php?id=<?= (int) $recipe['id'] ?>"><span class="popular-number">0<?= $number + 1 ?></span><span><strong><?= e($recipe['title']) ?></strong><small><?= (int) $recipe['comment_count'] ?> comment<?= (int) $recipe['comment_count'] === 1 ? '' : 's' ?></small></span></a><?php endforeach; ?></div><?php endif; ?><div class="aside-note"><span aria-hidden="true">✿</span><p>Good food travels farther when we write it down.</p></div></aside>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
