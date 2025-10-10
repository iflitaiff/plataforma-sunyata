<?php
/**
 * Admin - Gerenciamento de Usuários
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
$filter_level = $_GET['level'] ?? '';
$filter_vertical = $_GET['vertical'] ?? '';
$search = $_GET['search'] ?? '';

// Query base
$sql = "SELECT u.id, u.name, u.email, u.access_level, u.selected_vertical,
               u.completed_onboarding, u.created_at, u.last_login
        FROM users u
        WHERE 1=1";

$params = [];

if ($filter_level) {
    $sql .= " AND u.access_level = :level";
    $params['level'] = $filter_level;
}

if ($filter_vertical) {
    $sql .= " AND u.selected_vertical = :vertical";
    $params['vertical'] = $filter_vertical;
}

if ($search) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search)";
    $params['search'] = "%$search%";
}

$sql .= " ORDER BY u.created_at DESC";

$users = $db->fetchAll($sql, $params);

// Estatísticas
$stats = $db->fetchOne("SELECT COUNT(*) as total FROM users");

$pageTitle = 'Usuários - Admin';
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
                    <a href="<?= BASE_URL ?>/admin/users.php" class="admin-nav-link active">
                        <i class="bi bi-people"></i> Usuários
                    </a>
                    <a href="<?= BASE_URL ?>/admin/access-requests.php" class="admin-nav-link">
                        <i class="bi bi-key"></i> Solicitações
                    </a>
                    <a href="<?= BASE_URL ?>/admin/audit-logs.php" class="admin-nav-link">
                        <i class="bi bi-journal-text"></i> Logs de Auditoria
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <h1 class="mb-4">Gerenciamento de Usuários</h1>

                <!-- Stats -->
                <div class="alert alert-info">
                    <strong>Total de Usuários:</strong> <?= $stats['total'] ?>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Pesquisar</label>
                                <input type="text" class="form-control" name="search" value="<?= sanitize_output($search) ?>" placeholder="Nome ou email">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nível</label>
                                <select class="form-select" name="level">
                                    <option value="">Todos</option>
                                    <option value="guest" <?= $filter_level === 'guest' ? 'selected' : '' ?>>Guest</option>
                                    <option value="student" <?= $filter_level === 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="client" <?= $filter_level === 'client' ? 'selected' : '' ?>>Client</option>
                                    <option value="admin" <?= $filter_level === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vertical</label>
                                <select class="form-select" name="vertical">
                                    <option value="">Todas</option>
                                    <option value="docencia">Docência</option>
                                    <option value="pesquisa">Pesquisa</option>
                                    <option value="ifrj_alunos">IFRJ-Alunos</option>
                                    <option value="juridico">Jurídico</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-secondary">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Nível</th>
                                        <th>Vertical</th>
                                        <th>Onboarding</th>
                                        <th>Cadastro</th>
                                        <th>Último Login</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= sanitize_output($user['name']) ?></td>
                                            <td><?= sanitize_output($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['access_level'] === 'admin' ? 'danger' : 'primary' ?>">
                                                    <?= ucfirst($user['access_level']) ?>
                                                </span>
                                            </td>
                                            <td><?= $user['selected_vertical'] ? sanitize_output(ucfirst(str_replace('_', ' ', $user['selected_vertical']))) : '-' ?></td>
                                            <td>
                                                <?php if ($user['completed_onboarding']): ?>
                                                    <span class="badge bg-success">Completo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                            <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
