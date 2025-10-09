<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTA: A função require_login() está definida em config/config.php
// para evitar conflito de redeclaração de função.

function current_user_name(): string
{
    return $_SESSION['user']['name'] ?? 'Visitante';
}
