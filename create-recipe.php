<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';
$userId = require_authentication();
$categories = (new Category($pdo))->all();
$errors = [];
$title = '';
$description = '';
$instructions = '';
$categoryId = 0;
$ingredients = [''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $instructions = trim((string) ($_POST['instructions'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $ingredients = is_array($_POST['ingredients'] ?? null) ? array_map(fn($value): string => trim((string) $value), $_POST['ingredients']) : [];
    $ingredients = array_values(array_filter($ingredients, fn(string $value): bool => $value !== ''));
    if (!verify_csrf($_POST['csrf_token'] ?? null)) $errors[] = 'Your form session expired. Please try again.';
    if (mb_strlen($title) < 3 || mb_strlen($title) > 150) $errors[] = 'Title must be between 3 and 150 characters.';
    if (mb_strlen($description) < 10 || mb_strlen($description) > 500) $errors[] = 'Description must be between 10 and 500 characters.';
    if (!(new Category($pdo))->exists($categoryId)) $errors[] = 'Choose an existing category.';
    if (count($ingredients) < 1 || count($ingredients) > 30 || count(array_filter($ingredients, fn(string $value): bool => mb_strlen($value) < 1 || mb_strlen($value) > 255)) > 0) $errors[] = 'Add between 1 and 30 valid ingredients.';
    if (mb_strlen($instructions) < 10 || mb_strlen($instructions) > 10000) $errors[] = 'Instructions must be between 10 and 10,000 characters.';
    if ($errors === []) { $id = (new Recipe($pdo))->create($userId, $categoryId, $title, $description, $instructions, $ingredients); flash('Your recipe is on the table.'); redirect('recipe.php?id=' . $id); }
}
$pageTitle = 'Share a recipe';
$currentPage = 'create';
require __DIR__ . '/includes/header.php';
?>
<section class="page-intro compact-intro"><div><p class="eyebrow">Pass it on</p><h1>Share something<br><em>sweet.</em></h1><p class="intro-copy">Write it the way you would tell a neighbor across the gate: clear, generous, and easy to follow.</p></div></section>
<?php require __DIR__ . '/includes/recipe-form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
