<?php
/**
 * Admin - Logs de Auditoria
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;

require_login();

// Verificar se é admin
if (!isset($_SESSION['user']['access_level']) || $_SESSION['user']['access_level'] !== 'admin') {
    $_SESSION['error'] = 'Acesso negado. Área restrita a administradores.';
    redirect(BASE_URL . '/dashboard.php');
}

$db = Database::getInstance();

// Filtros
$filter_action = $_GET['action'] ?? '';
$limit = 50;
$offset = (int)($_GET['page'] ?? 0) * $limit;

// Query base
$sql = "SELECT a.id, a.user_id, u.name as user_name, u.email, a.action,
               a.entity_type, a.entity_id, a.ip_address, a.details, a.created_at
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE 1=1";

$params = [];

if ($filter_action) {
    $sql .= " AND a.action = :action";
    $params['action'] = $filter_action;
}

$sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
$params['limit'] = $limit;
$params['offset'] = $offset;

$logs = $db->fetchAll($sql, $params);

// Buscar ações únicas para o filtro
$actions = $db->fetchAll("SELECT DISTINCT action FROM audit_logs ORDER BY action");

$pageTitle = 'Logs de Auditoria - Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            min-height: calc(100vh - 56px);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .admin-nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            display: block;
            text-decoration: none;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background: #e9ecef;
            color: #212529;
        }
        .details-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/admin/">
                <i class="bi bi-shield-lock"></i> Admin - <?= APP_NAME ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= BASE_URL ?>/dashboard.php">
                    <i class="bi bi-box-arrow-left"></i> Voltar ao Portal
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar">
                <div class="p-3">
                    <h6 class="text-muted text-uppercase small mb-3">Menu</h6>
                    <a href="<?= BASE_URL ?>/admin/" class="admin-nav-link">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/admin/users.php" class="admin-nav-link">
                        <i class="bi bi-people"></i> Usuários
                    </a>
                    <a href="<?= BASE_URL ?>/admin/access-requests.php" class="admin-nav-link">
                        <i class="bi bi-key"></i> Solicitações
                    </a>
                    <a href="<?= BASE_URL ?>/admin/audit-logs.php" class="admin-nav-link active">
                        <i class="bi bi-journal-text"></i> Logs de Auditoria
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <h1 class="mb-4">Logs de Auditoria</h1>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ação</label>
                                <select class="form-select" name="action">
                                    <option value="">Todas</option>
                                    <?php foreach ($actions as $act): ?>
                                        <option value="<?= sanitize_output($act['action']) ?>" <?= $filter_action === $act['action'] ? 'selected' : '' ?>>
                                            <?= sanitize_output($act['action']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                <a href="<?= BASE_URL ?>/admin/audit-logs.php" class="btn btn-secondary">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data/Hora</th>
                                        <th>Usuário</th>
                                        <th>Ação</th>
                                        <th>Entidade</th>
                                        <th>IP</th>
                                        <th>Detalhes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                Nenhum log encontrado
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?= $log['id'] ?></td>
                                                <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($log['user_name']): ?>
                                                        <small>
                                                            <?= sanitize_output($log['user_name']) ?><br>
                                                            <span class="text-muted"><?= sanitize_output($log['email']) ?></span>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sistema</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><code class="small"><?= sanitize_output($log['action']) ?></code></td>
                                                <td>
                                                    <?php if ($log['entity_type']): ?>
                                                        <small><?= sanitize_output($log['entity_type']) ?> #<?= $log['entity_id'] ?></small>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?= sanitize_output($log['ip_address'] ?? '-') ?></small></td>
                                                <td class="details-cell">
                                                    <?php if ($log['details']): ?>
                                                        <small class="text-muted"><?= sanitize_output($log['details']) ?></small>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    <nav>
                        <ul class="pagination">
                            <?php
                            $current_page = (int)($_GET['page'] ?? 0);
                            if ($current_page > 0):
                            ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $current_page - 1 ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?>">Anterior</a>
                                </li>
                            <?php endif; ?>
                            <?php if (count($logs) === $limit): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $current_page + 1 ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?>">Próxima</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
