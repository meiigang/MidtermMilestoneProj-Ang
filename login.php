<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';

if (current_user_id() !== null) {
    redirect('index.php');
}

$user = new User($pdo);
$errors = [];
$email = '';
$flashMessage = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Enter a valid email address.';
    } elseif ($password === '') {
        $errors[] = 'Enter your password.';
    } else {
        $authenticatedUser = $user->authenticate($email, $password);
        if ($authenticatedUser === null) {
            $errors[] = 'We could not match that email and password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $authenticatedUser['id'];
            $_SESSION['user_name'] = (string) $authenticatedUser['name'];
            redirect('index.php');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · Tamis</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<main class="auth-shell">
    <a class="brand auth-brand" href="login.php"><span class="brand-mark">T</span><span>Tamis</span></a>
    <section class="auth-card">
        <p class="eyebrow">Welcome back, kapitbahay</p>
        <h1>Come back to the table.</h1>
        <p class="auth-lede">Sign in to save favorites, share family recipes, and keep the barangay kitchen growing.</p>
        <?php if ($flashMessage !== null): ?><div class="notice notice-<?= e((string) ($flashMessage['type'] ?? 'success')) ?>"><?= e((string) $flashMessage['message']) ?></div><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="notice notice-error"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Email address<input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></label>
            <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button button-primary" type="submit">Log in <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">New to Tamis? <a href="register.php">Create an account</a></p>
    </section>
</main>
</body>
</html>
