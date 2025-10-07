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

$auth = new GoogleAuth();
$consentManager = new ConsentManager();

// Check for error from Google
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Autenticação cancelada ou falhou';
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
    redirect(BASE_URL . '/index.php');
}

$user = $result['user'];

// Check if user needs to accept terms
if ($consentManager->needsConsent($user['id'], 'terms_of_use')) {
    $_SESSION['needs_consent'] = true;
    redirect(BASE_URL . '/dashboard.php?consent=required');
}

// Successful login
$_SESSION['success'] = 'Login realizado com sucesso!';
redirect(BASE_URL . '/dashboard.php');
