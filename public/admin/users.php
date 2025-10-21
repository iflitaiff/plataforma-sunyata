<?php
/**
 * Admin - Gerenciamento de Usuários
 * Updated: 2025-10-14 19:25
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;
use Sunyata\Admin\UserDeletionService;

require_login();

// Verificar se é admin
if (!isset($_SESSION['user']['access_level']) || $_SESSION['user']['access_level'] !== 'admin') {
    $_SESSION['error'] = 'Acesso negado. Área restrita a administradores.';
    redirect(BASE_URL . '/dashboard.php');
}

$db = Database::getInstance();
$deletionService = new UserDeletionService();

// Handle user deletion
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de segurança inválido';
        $message_type = 'danger';
    } else {
        $userId = (int)($_POST['user_id'] ?? 0);
        $result = $deletionService->deleteUser($userId, $_SESSION['user_id']);

        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }
}

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

// Estatísticas - CORRIGIDO: inicializar $stats como array e não sobrescrever
$stats = [];
$totalResult = $db->fetchOne("SELECT COUNT(*) as total FROM users");
$stats['total'] = $totalResult['total'];
$stats['pending_requests'] = $db->fetchOne("SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'")['count'];

$pageTitle = 'Usuários - Admin';

// Include responsive header
include __DIR__ . '/../../src/views/admin-header.php';
?>
                <h1 class="mb-4">Gerenciamento de Usuários</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                        <?= sanitize_output($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

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
                                        <th class="d-none d-lg-table-cell">ID</th>
                                        <th>Nome</th>
                                        <th class="d-none d-md-table-cell">Email</th>
                                        <th>Nível</th>
                                        <th class="d-none d-sm-table-cell">Vertical</th>
                                        <th class="d-none d-xl-table-cell">Onboarding</th>
                                        <th class="d-none d-lg-table-cell">Cadastro</th>
                                        <th class="d-none d-xl-table-cell">Último Login</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="d-none d-lg-table-cell"><?= $user['id'] ?></td>
                                            <td>
                                                <?= sanitize_output($user['name']) ?>
                                                <div class="d-md-none small text-muted"><?= sanitize_output($user['email']) ?></div>
                                            </td>
                                            <td class="d-none d-md-table-cell"><?= sanitize_output($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['access_level'] === 'admin' ? 'danger' : 'primary' ?>">
                                                    <?= ucfirst($user['access_level']) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell"><?= $user['selected_vertical'] ? sanitize_output(ucfirst(str_replace('_', ' ', $user['selected_vertical']))) : '-' ?></td>
                                            <td class="d-none d-xl-table-cell">
                                                <?php if ($user['completed_onboarding']): ?>
                                                    <span class="badge bg-success">Completo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-none d-lg-table-cell"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                            <td class="d-none d-xl-table-cell"><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></td>
                                            <td class="text-center">
                                                <?php if ($user['access_level'] === 'admin'): ?>
                                                    <span class="badge bg-secondary" title="Não é permitido deletar admins">
                                                        <i class="bi bi-shield-lock"></i>
                                                    </span>
                                                <?php elseif ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-secondary" title="Não pode deletar a si mesmo">
                                                        <i class="bi bi-person-x"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="confirmDelete(<?= $user['id'] ?>, '<?= addslashes($user['name']) ?>')"
                                                            title="Deletar usuário">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

<?php include __DIR__ . '/../../src/views/admin-footer.php'; ?>

<!-- Hidden Form for Deletion -->
<form id="delete-form" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete-user-id">
</form>

<script>
function confirmDelete(userId, userName) {
    // Primeira confirmação
    if (!confirm(`⚠️ ATENÇÃO!\n\nVocê está prestes a DELETAR permanentemente o usuário:\n\n"${userName}"\n\nEsta ação é IRREVERSÍVEL e irá remover:\n- Conta do usuário\n- Perfil e dados pessoais\n- Solicitações de acesso\n- Histórico de uso\n\nDeseja continuar?`)) {
        return;
    }

    // Segunda confirmação (confirmação dupla)
    if (!confirm(`⚠️ CONFIRMAÇÃO FINAL\n\nVocê tem ABSOLUTA CERTEZA que deseja deletar "${userName}"?\n\nEsta é sua última chance de cancelar.\n\nClique OK para DELETAR PERMANENTEMENTE ou Cancelar para abortar.`)) {
        return;
    }

    // Se passou pelas duas confirmações, submete o form
    document.getElementById('delete-user-id').value = userId;
    document.getElementById('delete-form').submit();
}
</script>
</body>
</html>
