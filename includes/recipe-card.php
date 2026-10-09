<?php
$recipe = $recipe ?? [];
$isFavorited = (int) ($recipe['is_favorited'] ?? 0) > 0;
?>
<article class="recipe-card">
    <div class="recipe-card-top">
        <span class="category-label"><?= e((string) ($recipe['category_name'] ?? 'Recipe')) ?></span>
        <button class="favorite-button <?= $isFavorited ? 'is-favorited' : '' ?>" type="button" data-favorite-id="<?= (int) $recipe['id'] ?>" aria-pressed="<?= $isFavorited ? 'true' : 'false' ?>" aria-label="<?= $isFavorited ? 'Remove from saved recipes' : 'Save recipe' ?>">
            <span aria-hidden="true">♥</span><span class="favorite-text"><?= $isFavorited ? 'Saved' : 'Save' ?></span>
        </button>
    </div>
    <h2><a href="recipe.php?id=<?= (int) $recipe['id'] ?>"><?= e((string) $recipe['title']) ?></a></h2>
    <p><?= e((string) $recipe['description']) ?></p>
    <div class="recipe-meta"><span>By <?= e((string) $recipe['author_name']) ?></span><span><?= format_date((string) $recipe['created_at']) ?><?= !empty($recipe['is_edited']) ? ' · Edited' : '' ?></span></div>
    <div class="recipe-card-bottom"><span><?= (int) ($recipe['comment_count'] ?? 0) ?> comment<?= (int) ($recipe['comment_count'] ?? 0) === 1 ? '' : 's' ?></span><a class="text-link" href="recipe.php?id=<?= (int) $recipe['id'] ?>">Read recipe <span aria-hidden="true">↗</span></a></div>
</article>
