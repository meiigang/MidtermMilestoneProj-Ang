<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && hash_equals((string) $_SESSION['csrf_token'], $token);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($message) ? $message : null;
}

function format_date(string $date): string
{
    return date('M j, Y \a\t g:i A', strtotime($date));
}

function reading_time(string $text): int
{
    return max(1, (int) ceil(str_word_count(strip_tags($text)) / 200));
}

function old_input(string $key, string $default = ''): string
{
    return e(isset($_SESSION['old'][$key]) && is_string($_SESSION['old'][$key]) ? $_SESSION['old'][$key] : $default);
}

function remember_input(array $input): void
{
    $_SESSION['old'] = $input;
}

function forget_input(): void
{
    unset($_SESSION['old']);
}
