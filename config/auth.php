<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: /index.php?m=login_required');
        exit;
    }
}

function current_user_name(): string
{
    return $_SESSION['user']['name'] ?? 'Visitante';
}
