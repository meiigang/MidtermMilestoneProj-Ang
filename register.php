<?php
declare(strict_types=1);
require __DIR__ . '/config/app.php';
require __DIR__ . '/includes/auth.php';

if (current_user_id() !== null) {
    redirect('index.php');
}

$user = new User($pdo);
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Name must be between 2 and 100 characters.';
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords must match.';
    }
    if ($errors === [] && $user->findByEmail($email) !== null) {
        $errors[] = 'That email is already registered.';
    }
    if ($errors === []) {
        $user->register($name, $email, $password);
        flash('Registration successful. You can now log in.');
        redirect('login.php');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Tamis · Tamis</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<main class="auth-shell">
    <a class="brand auth-brand" href="login.php"><span class="brand-mark">T</span><span>Tamis</span></a>
    <section class="auth-card">
        <p class="eyebrow">A place at the table</p>
        <h1>Bring your sweet spot.</h1>
        <p class="auth-lede">Join fellow home cooks sharing the desserts, merienda, and small food memories that make a barangay feel like home.</p>
        <?php foreach ($errors as $error): ?><div class="notice notice-error"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Name<input type="text" name="name" value="<?= e($name) ?>" autocomplete="name" minlength="2" maxlength="100" required></label>
            <label>Email address<input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" maxlength="255" required></label>
            <div class="form-grid">
                <label>Password<input type="password" name="password" autocomplete="new-password" minlength="8" required></label>
                <label>Confirm password<input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required></label>
            </div>
            <button class="button button-primary" type="submit">Create account <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">Already a member? <a href="login.php">Log in</a></p>
    </section>
</main>
</body>
</html>
