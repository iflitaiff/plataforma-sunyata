<?php
/**
 * Plataforma Sunyata - Main Configuration
 *
 * @package Sunyata
 */

// Start session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');
define('CONFIG_PATH', BASE_PATH . '/config');

// URLs
define('BASE_URL', 'https://portal.sunyataconsulting.com');
define('CALLBACK_URL', BASE_URL . '/callback.php');

// Load secrets
$secretsFile = CONFIG_PATH . '/secrets.php';
if (!file_exists($secretsFile)) {
    die('Configuration error: secrets.php not found. Copy secrets.php.example to secrets.php and configure.');
}
require_once $secretsFile;

// Database configuration
// Priority: database.local.php (local dev) > secrets.php (production)
$localDbFile = CONFIG_PATH . '/database.local.php';
if (file_exists($localDbFile)) {
    // Local development database
    require_once $localDbFile;
} else {
    // Production database (from secrets.php)
    define('DB_HOST', DB_HOST);
    define('DB_NAME', DB_NAME);
    define('DB_USER', DB_USER);
    define('DB_PASS', DB_PASS);
    define('DB_CHARSET', 'utf8mb4');
}

// Google OAuth (loaded from secrets.php)
define('GOOGLE_CLIENT_ID', GOOGLE_CLIENT_ID);
define('GOOGLE_CLIENT_SECRET', GOOGLE_CLIENT_SECRET);

// Session configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_NAME', 'SUNYATA_SESSION');

// LGPD Configuration
define('CONSENT_VERSION', '1.0.0');
define('DATA_RETENTION_DAYS', 730); // 2 years
define('ANONYMIZATION_AFTER_DAYS', 2555); // 7 years

// Application settings
define('APP_NAME', 'Plataforma Sunyata');
define('COMPANY_NAME', 'Sunyata Consulting');
define('SUPPORT_EMAIL', 'suporte@sunyataconsulting.com');
define('DPO_EMAIL', 'dpo@sunyataconsulting.com');

// Access levels
define('ACCESS_LEVELS', [
    'guest' => 0,
    'student' => 10,
    'client' => 20,
    'admin' => 100
]);

// Verticals configuration
define('VERTICALS', [
    'sales' => 'Vendas',
    'marketing' => 'Marketing',
    'customer_service' => 'Atendimento',
    'hr' => 'RH',
    'general' => 'Geral'
]);

// Helper functions
function require_login() {
    // Verifica se o usuário está logado (compatível com auth.php)
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/index.php?m=login_required');
        exit;
    }
}

function has_access($required_level) {
    if (!isset($_SESSION['access_level'])) {
        return false;
    }

    $user_level = ACCESS_LEVELS[$_SESSION['access_level']] ?? 0;
    $required = ACCESS_LEVELS[$required_level] ?? 0;

    return $user_level >= $required;
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
