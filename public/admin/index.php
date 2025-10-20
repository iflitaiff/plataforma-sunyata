<?php
/**
 * Admin Dashboard - Página Principal
 * Apenas para usuários com access_level = 'admin'
 * Updated: 2025-10-14 19:25
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;
use Sunyata\Core\Settings;
use Sunyata\AI\ClaudeService;

require_login();

// Verificar se é admin
if (!isset($_SESSION['user']['access_level']) || $_SESSION['user']['access_level'] !== 'admin') {
    $_SESSION['error'] = 'Acesso negado. Área restrita a administradores.';
    redirect(BASE_URL . '/dashboard.php');
}

$db = Database::getInstance();
$settings = Settings::getInstance();

// Handle settings toggle
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de segurança inválido';
        $message_type = 'danger';
    } else {
        $action = $_POST['action'];

        if ($action === 'toggle_juridico_approval') {
            $newValue = $settings->toggle('juridico_requires_approval', $_SESSION['user_id']);
            $statusText = $newValue ? 'ATIVADA' : 'DESATIVADA';
            $message = "Aprovação Jurídico {$statusText} com sucesso!";
            $message_type = 'success';
        }
    }
}

// Ler configurações atuais
$juridico_requires_approval = $settings->get('juridico_requires_approval', true);

// Estatísticas
$stats = [];

// Total de usuários
$result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result['count'];

// Usuários por nível
$stats['users_by_level'] = $db->fetchAll("
    SELECT access_level, COUNT(*) as count
    FROM users
    GROUP BY access_level
    ORDER BY count DESC
");

// Usuários por vertical
$stats['users_by_vertical'] = $db->fetchAll("
    SELECT selected_vertical, COUNT(*) as count
    FROM users
    WHERE selected_vertical IS NOT NULL
    GROUP BY selected_vertical
    ORDER BY count DESC
");

// Solicitações pendentes
$result = $db->fetchOne("
    SELECT COUNT(*) as count
    FROM vertical_access_requests
    WHERE status = 'pending'
");
$stats['pending_requests'] = $result['count'];

// Usuários cadastrados nos últimos 7 dias
$result = $db->fetchOne("
    SELECT COUNT(*) as count
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stats['new_users_week'] = $result['count'];

// Últimos logins
$stats['recent_logins'] = $db->fetchAll("
    SELECT u.id, u.name, u.email, u.access_level, u.last_login
    FROM users u
    WHERE u.last_login IS NOT NULL
    ORDER BY u.last_login DESC
    LIMIT 10
");

// Estatísticas API Claude (mês atual)
try {
    $apiStats = $db->fetchOne("
        SELECT
            COUNT(*) as total_prompts,
            COALESCE(SUM(tokens_total), 0) as total_tokens,
            COALESCE(SUM(cost_usd), 0) as total_cost
        FROM prompt_history
        WHERE status = 'success'
        AND MONTH(created_at) = MONTH(NOW())
        AND YEAR(created_at) = YEAR(NOW())
    ");
    $stats['api_month'] = [
        'total_prompts' => $apiStats['total_prompts'] ?? 0,
        'total_tokens' => $apiStats['total_tokens'] ?? 0,
        'total_cost' => $apiStats['total_cost'] ?? 0
    ];

    // Custo hoje
    $todayCost = $db->fetchOne("
        SELECT COALESCE(SUM(cost_usd), 0) as cost FROM prompt_history
        WHERE status = 'success' AND DATE(created_at) = CURDATE()
    ");
    $stats['api_today'] = $todayCost['cost'] ?? 0;
} catch (Exception $e) {
    error_log('Error fetching API stats: ' . $e->getMessage());
    $stats['api_month'] = ['total_prompts' => 0, 'total_tokens' => 0, 'total_cost' => 0];
    $stats['api_today'] = 0;
}

$pageTitle = 'Admin Dashboard';

// Include responsive header
include __DIR__ . '/../../src/views/admin-header.php';
?>
                <h1 class="mb-4">Dashboard de Administração</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                        <?= sanitize_output($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Quick Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-sliders"></i> Configurações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">⚖️ Aprovação Vertical Jurídico</h6>
                                <p class="mb-0 text-muted small">
                                    <?php if ($juridico_requires_approval): ?>
                                        <span class="badge bg-warning">ATIVA</span> Usuários precisam de aprovação admin para acessar Jurídico
                                    <?php else: ?>
                                        <span class="badge bg-success">DESATIVADA</span> Usuários acessam Jurídico diretamente após onboarding
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="toggle_juridico_approval">
                                    <button type="submit" class="btn btn-<?= $juridico_requires_approval ? 'success' : 'warning' ?>">
                                        <i class="bi bi-toggle-<?= $juridico_requires_approval ? 'on' : 'off' ?>"></i>
                                        <?= $juridico_requires_approval ? 'Desativar Aprovação' : 'Ativar Aprovação' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total de Usuários</h6>
                                        <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                                    </div>
                                    <div class="text-primary" style="font-size: 2.5rem;">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Novos (7 dias)</h6>
                                        <h2 class="mb-0"><?= $stats['new_users_week'] ?></h2>
                                    </div>
                                    <div class="text-success" style="font-size: 2.5rem;">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Solicitações Pendentes</h6>
                                        <h2 class="mb-0"><?= $stats['pending_requests'] ?></h2>
                                    </div>
                                    <div class="text-warning" style="font-size: 2.5rem;">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Verticais Ativas</h6>
                                        <h2 class="mb-0"><?= count($stats['users_by_vertical']) ?></h2>
                                    </div>
                                    <div class="text-info" style="font-size: 2.5rem;">
                                        <i class="bi bi-grid"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Cost Alert Card -->
                <?php
                $costLimit = 10.00; // USD limite mensal
                $costPercent = ($stats['api_month']['total_cost'] / $costLimit) * 100;
                $alertClass = $costPercent > 80 ? 'danger' : ($costPercent > 50 ? 'warning' : 'success');
                ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-<?= $alertClass ?>">
                            <div class="card-header bg-<?= $alertClass ?> text-white">
                                <h5 class="mb-0"><i class="bi bi-robot"></i> Uso API Claude - Mês Atual</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h6 class="text-muted">Prompts Gerados</h6>
                                        <h3><?= number_format($stats['api_month']['total_prompts']) ?></h3>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-muted">Tokens Usados</h6>
                                        <h3><?= number_format($stats['api_month']['total_tokens']) ?></h3>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-muted">Custo Mês</h6>
                                        <h3>USD <?= number_format($stats['api_month']['total_cost'], 4) ?></h3>
                                        <small class="text-muted">Limite: USD <?= number_format($costLimit, 2) ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="text-muted">Custo Hoje</h6>
                                        <h3>USD <?= number_format($stats['api_today'], 4) ?></h3>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 25px;">
                                    <div class="progress-bar bg-<?= $alertClass ?>" role="progressbar"
                                         style="width: <?= min($costPercent, 100) ?>%"
                                         aria-valuenow="<?= $costPercent ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= number_format($costPercent, 1) ?>%
                                    </div>
                                </div>
                                <?php if ($costPercent > 80): ?>
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <strong>⚠️ Atenção!</strong> Uso da API acima de 80% do limite mensal.
                                    </div>
                                <?php elseif ($costPercent > 50): ?>
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <strong>⚡ Aviso:</strong> Uso da API acima de 50% do limite mensal.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Users by Level -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Usuários por Nível de Acesso</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($stats['users_by_level'] as $level): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-capitalize"><?= $level['access_level'] ?></span>
                                            <strong><?= $level['count'] ?></strong>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: <?= ($level['count'] / $stats['total_users']) * 100 ?>%">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Users by Vertical -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Usuários por Vertical</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($stats['users_by_vertical'])): ?>
                                    <p class="text-muted">Nenhum usuário com vertical definida ainda.</p>
                                <?php else: ?>
                                    <?php foreach ($stats['users_by_vertical'] as $vertical): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-capitalize"><?= $vertical['selected_vertical'] ?></span>
                                                <strong><?= $vertical['count'] ?></strong>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                     style="width: <?= ($vertical['count'] / $stats['total_users']) * 100 ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Últimos Acessos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th class="d-none d-md-table-cell">Email</th>
                                        <th>Nível</th>
                                        <th class="d-none d-sm-table-cell">Último Acesso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_logins'] as $user): ?>
                                        <tr>
                                            <td>
                                                <?= sanitize_output($user['name']) ?>
                                                <div class="d-md-none small text-muted"><?= sanitize_output($user['email']) ?></div>
                                            </td>
                                            <td class="d-none d-md-table-cell"><?= sanitize_output($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['access_level'] === 'admin' ? 'danger' : 'secondary' ?>">
                                                    <?= $user['access_level'] ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell"><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

<?php include __DIR__ . '/../../src/views/admin-footer.php'; ?>
