<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    flash('That request could not be verified.', 'error');
    redirect('index.php');
}
$action = (string) ($_POST['action'] ?? '');
$recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$commentId = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$comment = new Comment($pdo);
$recipe = new Recipe($pdo);

if ($action === 'create') {
    $content = trim((string) ($_POST['content'] ?? ''));
    if ($recipeId === false || $recipeId === null || $recipe->find((int) $recipeId) === null) {
        flash('Recipe not found.', 'error');
    } elseif (mb_strlen($content) < 1 || mb_strlen($content) > 1000) {
        flash('Comments must be between 1 and 1,000 characters.', 'error');
    } else {
        $comment->create((int) $recipeId, $userId, $content);
        flash('Comment added.');
    }
} elseif ($action === 'edit') {
    $content = trim((string) ($_POST['content'] ?? ''));
    if ($commentId === false || $commentId === null || mb_strlen($content) < 1 || mb_strlen($content) > 1000 || !$comment->update((int) $commentId, $userId, $content)) {
        flash('Comment not found or you do not own it.', 'error');
    } else {
        flash('Comment updated.');
    }
} elseif ($action === 'delete') {
    if ($commentId === false || $commentId === null || !$comment->delete((int) $commentId, $userId)) {
        flash('Comment not found or you do not own it.', 'error');
    } else {
        flash('Comment deleted.');
    }
}
redirect($recipeId !== false && $recipeId !== null ? 'recipe.php?id=' . (int) $recipeId : 'index.php');
