<?php
/**
 * Google OAuth Callback Handler
 *
 * @package Sunyata
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Auth\GoogleAuth;
use Sunyata\Compliance\ConsentManager;
use Sunyata\Core\MarkdownLogger;

$auth = new GoogleAuth();
$consentManager = new ConsentManager();

// Check for error from Google
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Autenticação cancelada ou falhou';

    // Log failed login attempt
    MarkdownLogger::getInstance()->access(
        userId: 0,
        action: 'LOGIN_FAILED',
        resource: 'OAuth',
        status: 'cancelled',
        extraContext: ['error' => $_GET['error']]
    );

    redirect(BASE_URL . '/index.php');
}

// Check for authorization code
if (!isset($_GET['code'])) {
    redirect(BASE_URL . '/index.php');
}

// Handle OAuth callback
$result = $auth->handleCallback($_GET['code']);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'] ?? 'Erro na autenticação';

    // Log authentication failure
    MarkdownLogger::getInstance()->access(
        userId: 0,
        action: 'LOGIN_FAILED',
        resource: 'OAuth',
        status: 'auth_error',
        extraContext: ['error' => $result['error'] ?? 'Unknown error']
    );

    redirect(BASE_URL . '/index.php');
}

$user = $result['user'];

// Check if user needs to complete onboarding
if (!$user['completed_onboarding']) {
    redirect(BASE_URL . '/onboarding-step1.php');
}

// Check if user needs to accept terms
if ($consentManager->needsConsent($user['id'], 'terms_of_use')) {
    $_SESSION['needs_consent'] = true;
    redirect(BASE_URL . '/dashboard.php?consent=required');
}

// Successful login - redirect based on vertical
$vertical = $user['selected_vertical'] ?? null;
$_SESSION['success'] = 'Login realizado com sucesso!';

// Log successful login
MarkdownLogger::getInstance()->access(
    userId: $user['id'],
    action: 'LOGIN',
    resource: '-',
    status: 'success',
    extraContext: [
        'vertical' => $vertical,
        'email' => $user['email'] ?? 'unknown'
    ]
);

if ($vertical && file_exists(__DIR__ . "/areas/{$vertical}/index.php")) {
    redirect(BASE_URL . "/areas/{$vertical}/");
} else {
    redirect(BASE_URL . '/dashboard.php');
}
