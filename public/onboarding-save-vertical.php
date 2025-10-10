<?php
/**
 * Salva a vertical escolhida e completa o onboarding
 * Para verticais que NÃO requerem aprovação ou info extra
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;

require_login();

// Validar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/onboarding-step2.php');
}

// Validar CSRF
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de segurança inválido';
    redirect(BASE_URL . '/onboarding-step2.php');
}

$vertical = $_POST['vertical'] ?? '';

// Verticais válidas (que não requerem aprovação/info extra)
$verticais_diretas = ['docencia', 'pesquisa', 'vendas', 'marketing', 'licitacoes', 'rh', 'geral'];

if (!in_array($vertical, $verticais_diretas)) {
    $_SESSION['error'] = 'Vertical inválida';
    redirect(BASE_URL . '/onboarding-step2.php');
}

try {
    $db = Database::getInstance();

    // Atualizar usuário
    $db->update('users', [
        'selected_vertical' => $vertical,
        'completed_onboarding' => true
    ], 'id = :id', ['id' => $_SESSION['user_id']]);

    // Atualizar sessão (CRÍTICO: deve atualizar antes de redirecionar)
    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = [];
    }
    $_SESSION['user']['selected_vertical'] = $vertical;
    $_SESSION['user']['completed_onboarding'] = true;

    // Log de auditoria
    $db->insert('audit_logs', [
        'user_id' => $_SESSION['user_id'],
        'action' => 'onboarding_completed',
        'entity_type' => 'users',
        'entity_id' => $_SESSION['user_id'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'details' => json_encode(['vertical' => $vertical])
    ]);

    // Mensagem de sucesso
    $_SESSION['success'] = 'Perfil configurado com sucesso! Bem-vindo à ' . ucfirst($vertical) . '!';

    // Redirecionar para a vertical escolhida
    redirect(BASE_URL . "/areas/{$vertical}/");

} catch (Exception $e) {
    error_log('Erro ao salvar vertical: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao salvar configuração. Por favor, tente novamente.';
    redirect(BASE_URL . '/onboarding-step2.php');
}
