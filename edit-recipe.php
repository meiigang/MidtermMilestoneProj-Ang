<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();
$id = filter_var($_GET['id'] ?? $_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$recipeService = new Recipe($pdo);
$recipe = $id === false || $id === null ? null : $recipeService->find((int) $id, $userId);
if ($recipe === null || (int) $recipe['user_id'] !== $userId) { flash('Recipe not found or you do not own it.', 'error'); redirect('index.php'); }
$categories = (new Category($pdo))->all();
$errors = [];
$title = (string) $recipe['title']; $description = (string) $recipe['description']; $instructions = (string) $recipe['instructions']; $categoryId = (int) $recipe['category_id']; $ingredients = $recipe['ingredients'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? '')); $description = trim((string) ($_POST['description'] ?? '')); $instructions = trim((string) ($_POST['instructions'] ?? '')); $categoryId = (int) ($_POST['category_id'] ?? 0); $ingredients = is_array($_POST['ingredients'] ?? null) ? array_map(fn($value): string => trim((string) $value), $_POST['ingredients']) : []; $ingredients = array_values(array_filter($ingredients, fn(string $value): bool => $value !== ''));
    if (!verify_csrf($_POST['csrf_token'] ?? null)) $errors[] = 'Your form session expired. Please try again.';
    if (mb_strlen($title) < 3 || mb_strlen($title) > 150) $errors[] = 'Title must be between 3 and 150 characters.';
    if (mb_strlen($description) < 10 || mb_strlen($description) > 500) $errors[] = 'Description must be between 10 and 500 characters.';
    if (!(new Category($pdo))->exists($categoryId)) $errors[] = 'Choose an existing category.';
    if (count($ingredients) < 1 || count($ingredients) > 30 || count(array_filter($ingredients, fn(string $value): bool => mb_strlen($value) < 1 || mb_strlen($value) > 255)) > 0) $errors[] = 'Add between 1 and 30 valid ingredients.';
    if (mb_strlen($instructions) < 10 || mb_strlen($instructions) > 10000) $errors[] = 'Instructions must be between 10 and 10,000 characters.';
    if ($errors === []) { $recipeService->update((int) $id, $userId, $categoryId, $title, $description, $instructions, $ingredients); flash('Recipe updated.'); redirect('recipe.php?id=' . (int) $id); }
}
$pageTitle = 'Edit recipe';
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro compact-intro"><div><p class="eyebrow">A little refining</p><h1>Edit your<br><em>recipe.</em></h1></div></section>
<?php require __DIR__ . '/includes/recipe-form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
