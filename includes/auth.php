<?php
declare(strict_types=1);

function require_authentication(): int
{
    if (empty($_SESSION['user_id'])) {
        flash('Please log in to continue.', 'error');
        redirect('login.php');
    }

    return (int) $_SESSION['user_id'];
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}
