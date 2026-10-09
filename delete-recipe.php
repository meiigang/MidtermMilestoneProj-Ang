<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf_token'] ?? null)) {
    flash('That request could not be verified.', 'error');
    redirect('index.php');
}
$recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($recipeId === false || $recipeId === null || !(new Recipe($pdo))->delete((int) $recipeId, $userId)) {
    flash('Recipe not found or you do not own it.', 'error');
} else {
    flash('Recipe deleted.');
}
redirect('index.php');
