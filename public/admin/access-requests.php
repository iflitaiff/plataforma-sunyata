<?php
/**
 * Admin - Gerenciamento de Solicitações de Acesso
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
$message = '';
$message_type = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $request_id = (int)($_POST['request_id'] ?? 0);

    if ($action === 'approve' || $action === 'reject') {
        $notes = trim($_POST['notes'] ?? '');
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        try {
            // Buscar solicitação
            $request = $db->fetchOne(
                "SELECT * FROM vertical_access_requests WHERE id = :id",
                ['id' => $request_id]
            );

            if ($request) {
                // Atualizar status
                $db->update('vertical_access_requests', [
                    'status' => $new_status,
                    'processed_at' => date('Y-m-d H:i:s'),
                    'processed_by' => $_SESSION['user_id'],
                    'notes' => $notes ?: null
                ], 'id = :id', ['id' => $request_id]);

                // Se aprovado, dar acesso ao usuário
                if ($action === 'approve') {
                    $db->update('users', [
                        'selected_vertical' => $request['vertical'],
                        'completed_onboarding' => true
                    ], 'id = :id', ['id' => $request['user_id']]);

                    $message = 'Solicitação aprovada! Usuário recebeu acesso à vertical.';
                } else {
                    $message = 'Solicitação rejeitada.';
                }

                // Log
                $db->insert('audit_logs', [
                    'user_id' => $_SESSION['user_id'],
                    'action' => "vertical_access_{$new_status}",
                    'entity_type' => 'vertical_access_requests',
                    'entity_id' => $request_id,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'details' => json_encode([
                        'request_user_id' => $request['user_id'],
                        'vertical' => $request['vertical']
                    ])
                ]);

                $message_type = 'success';
            } else {
                $message = 'Solicitação não encontrada.';
                $message_type = 'danger';
            }
        } catch (Exception $e) {
            error_log('Erro ao processar solicitação: ' . $e->getMessage());
            $message = 'Erro ao processar solicitação.';
            $message_type = 'danger';
        }
    }
}

// Filtros
$filter_status = $_GET['status'] ?? 'pending';
$filter_vertical = $_GET['vertical'] ?? '';

// Buscar solicitações
$where = [];
$params = [];

if ($filter_status) {
    $where[] = "r.status = :status";
    $params['status'] = $filter_status;
}

if ($filter_vertical) {
    $where[] = "r.vertical = :vertical";
    $params['vertical'] = $filter_vertical;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$requests = $db->fetchAll("
    SELECT
        r.*,
        u.name as user_name,
        u.email as user_email,
        u.access_level,
        p.name as processor_name
    FROM vertical_access_requests r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN users p ON r.processed_by = p.id
    {$where_clause}
    ORDER BY
        CASE r.status
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
        END,
        r.requested_at DESC
", $params);

$pageTitle = 'Solicitações de Acesso';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Admin - <?= APP_NAME ?></title>
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
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .admin-nav-link:hover {
            background: #e9ecef;
            color: #212529;
        }
        .admin-nav-link.active {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                    <a href="<?= BASE_URL ?>/admin/access-requests.php" class="admin-nav-link active">
                        <i class="bi bi-key"></i> Solicitações
                    </a>
                    <a href="<?= BASE_URL ?>/admin/audit-logs.php" class="admin-nav-link">
                        <i class="bi bi-journal-text"></i> Logs de Auditoria
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <h1 class="mb-4">Solicitações de Acesso às Verticais</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                        <?= sanitize_output($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pendentes</option>
                                    <option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>Aprovadas</option>
                                    <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>Rejeitadas</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Vertical</label>
                                <select name="vertical" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="juridico" <?= $filter_vertical === 'juridico' ? 'selected' : '' ?>>Jurídico</option>
                                    <option value="docencia" <?= $filter_vertical === 'docencia' ? 'selected' : '' ?>>Docência</option>
                                    <option value="pesquisa" <?= $filter_vertical === 'pesquisa' ? 'selected' : '' ?>>Pesquisa</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requests List -->
                <?php if (empty($requests)): ?>
                    <div class="alert alert-info">
                        Nenhuma solicitação encontrada com os filtros selecionados.
                    </div>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <?php
                        $request_data = json_decode($request['request_data'], true);
                        $status_badge = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger'
                        ];
                        $status_icon = [
                            'pending' => 'clock-history',
                            'approved' => 'check-circle',
                            'rejected' => 'x-circle'
                        ];
                        ?>
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-<?= $status_icon[$request['status']] ?>"></i>
                                        <?= sanitize_output($request['user_name']) ?>
                                        <small class="text-muted">(<?= sanitize_output($request['user_email']) ?>)</small>
                                    </h5>
                                </div>
                                <span class="badge bg-<?= $status_badge[$request['status']] ?> text-uppercase">
                                    <?= $request['status'] ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Vertical:</strong> <?= ucfirst($request['vertical']) ?></p>
                                        <p><strong>Data da Solicitação:</strong> <?= date('d/m/Y H:i', strtotime($request['requested_at'])) ?></p>
                                        <?php if ($request['processed_at']): ?>
                                            <p><strong>Processado em:</strong> <?= date('d/m/Y H:i', strtotime($request['processed_at'])) ?></p>
                                            <p><strong>Processado por:</strong> <?= sanitize_output($request['processor_name'] ?? 'N/A') ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if ($request['vertical'] === 'juridico' && $request_data): ?>
                                            <p><strong>Profissão:</strong> <?= sanitize_output($request_data['profissao'] ?? 'N/A') ?></p>
                                            <?php if (!empty($request_data['oab'])): ?>
                                                <p><strong>OAB:</strong> <?= sanitize_output($request_data['oab']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($request_data['escritorio'])): ?>
                                                <p><strong>Escritório:</strong> <?= sanitize_output($request_data['escritorio']) ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($request_data && !empty($request_data['motivo'])): ?>
                                    <div class="alert alert-light mt-2">
                                        <strong>Motivo:</strong><br>
                                        <?= nl2br(sanitize_output($request_data['motivo'])) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($request['notes']): ?>
                                    <div class="alert alert-secondary mt-2">
                                        <strong>Observações do Admin:</strong><br>
                                        <?= nl2br(sanitize_output($request['notes'])) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($request['status'] === 'pending'): ?>
                                    <hr>
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="notes-<?= $request['id'] ?>"
                                                   placeholder="Observações (opcional)">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-success w-100" onclick="processRequest(<?= $request['id'] ?>, 'approve')">
                                                <i class="bi bi-check-lg"></i> Aprovar
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-danger w-100" onclick="processRequest(<?= $request['id'] ?>, 'reject')">
                                                <i class="bi bi-x-lg"></i> Rejeitar
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Hidden Form -->
    <form id="action-form" method="POST" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" id="action-input">
        <input type="hidden" name="request_id" id="request-id-input">
        <input type="hidden" name="notes" id="notes-input">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function processRequest(requestId, action) {
            const notes = document.getElementById('notes-' + requestId).value;
            const actionText = action === 'approve' ? 'aprovar' : 'rejeitar';

            if (confirm(`Tem certeza que deseja ${actionText} esta solicitação?`)) {
                document.getElementById('action-input').value = action;
                document.getElementById('request-id-input').value = requestId;
                document.getElementById('notes-input').value = notes;
                document.getElementById('action-form').submit();
            }
        }
    </script>
</body>
</html>
