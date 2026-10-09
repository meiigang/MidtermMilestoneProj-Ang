<?php
declare(strict_types=1);
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || current_user_id() === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to save recipes.']);
    exit;
}
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Your session token expired. Refresh the page and try again.']);
    exit;
}
$recipeId = filter_var($_POST['recipe_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($recipeId === false || $recipeId === null) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid recipe.']);
    exit;
}
$recipe = (new Recipe($pdo))->find((int) $recipeId, current_user_id());
if ($recipe === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
    exit;
}
$isFavorited = (new Favorite($pdo))->toggle((int) current_user_id(), (int) $recipeId);
echo json_encode(['success' => true, 'is_favorited' => $isFavorited, 'message' => $isFavorited ? 'Recipe saved to favorites.' : 'Recipe removed from saved recipes.']);
