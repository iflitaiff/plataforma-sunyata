<?php
/**
 * Admin: Canvas Templates - Listagem
 * Gerenciamento de Canvas Templates (formulários dinâmicos)
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

// IMPORTANTE: Inicializar $stats como array ANTES de usar
$stats = [];

// Stats for admin-header.php (pending requests badge)
try {
    $result = $db->fetchOne("
        SELECT COUNT(*) as count
        FROM vertical_access_requests
        WHERE status = 'pending'
    ");
    $stats['pending_requests'] = $result['count'] ?? 0;
} catch (Exception $e) {
    // Table doesn't exist or query failed - set to 0
    $stats['pending_requests'] = 0;
}

// Buscar todos os Canvas Templates
$canvasTemplates = $db->fetchAll("
    SELECT
        id,
        slug,
        name,
        vertical,
        max_questions,
        is_active,
        created_at,
        updated_at
    FROM canvas_templates
    ORDER BY vertical ASC, name ASC
");

// Contar conversas por canvas (para estatísticas)
// CORRIGIDO: Usar $statsResult ao invés de $stats para evitar sobrescrita
$canvasStats = [];
foreach ($canvasTemplates as $canvas) {
    $statsResult = $db->fetchOne("
        SELECT COUNT(*) as total_conversations
        FROM conversations
        WHERE canvas_id = :canvas_id
    ", ['canvas_id' => $canvas['id']]);

    $canvasStats[$canvas['id']] = $statsResult['total_conversations'] ?? 0;
}

$pageTitle = 'Canvas Templates';

// Include header
include __DIR__ . '/../../src/views/admin-header.php';
?>

<h1 class="mb-4">Canvas Templates</h1>

<p class="text-muted">
    Gerencie os Canvas (formulários dinâmicos) usados na plataforma. Cada Canvas define os campos do formulário e os prompts enviados ao Claude.
</p>

<!-- Botão Novo Canvas (desabilitado no MVP) -->
<div class="mb-4">
    <button class="btn btn-success" disabled title="Funcionalidade disponível em breve">
        <i class="bi bi-plus-circle"></i> Novo Canvas
    </button>
    <span class="text-muted small ms-2">
        (MVP: Edite os Canvas existentes. Criação de novos Canvas será implementada na próxima versão)
    </span>
</div>

<!-- Cards de Canvas -->
<div class="row g-4">
    <?php foreach ($canvasTemplates as $canvas): ?>
        <div class="col-12">
            <div class="card <?= $canvas['is_active'] ? 'border-success' : 'border-secondary' ?>">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Info do Canvas -->
                        <div class="col-md-8">
                            <h5 class="card-title mb-2">
                                <?= sanitize_output($canvas['name']) ?>
                                <?php if ($canvas['is_active']): ?>
                                    <span class="badge bg-success">ATIVO</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">INATIVO</span>
                                <?php endif; ?>
                            </h5>

                            <p class="mb-2">
                                <strong>Slug:</strong> <code><?= sanitize_output($canvas['slug']) ?></code> |
                                <strong>Vertical:</strong> <span class="badge bg-primary"><?= ucfirst($canvas['vertical']) ?></span> |
                                <strong>Máx. Perguntas:</strong> <?= $canvas['max_questions'] ?>
                            </p>

                            <p class="mb-2">
                                <i class="bi bi-calendar"></i> Criado em: <?= date('d/m/Y H:i', strtotime($canvas['created_at'])) ?><br>
                                <i class="bi bi-clock-history"></i> Atualizado em: <?= date('d/m/Y H:i', strtotime($canvas['updated_at'])) ?>
                            </p>

                            <p class="mb-0 text-muted small">
                                <i class="bi bi-chat-dots"></i>
                                <strong><?= number_format($canvasStats[$canvas['id']] ?? 0) ?></strong> conversas realizadas
                            </p>
                        </div>

                        <!-- Ações -->
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="canvas-edit.php?id=<?= $canvas['id'] ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>

                            <?php if (!$canvas['is_active']): ?>
                                <button class="btn btn-success" disabled title="Ativar Canvas (próxima versão)">
                                    <i class="bi bi-check-circle"></i> Ativar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($canvasTemplates)): ?>
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Nenhum Canvas Template encontrado. Execute a migration 004_mvp_console.sql.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Informações Adicionais -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Sobre Canvas Templates</h6>
    </div>
    <div class="card-body">
        <h6>O que é um Canvas?</h6>
        <p>
            Um Canvas é uma ferramenta interativa que guia o usuário através de campos específicos para coletar informações
            contextuais. Essas informações são então processadas por prompts otimizados enviados ao Claude API.
        </p>

        <h6>Componentes de um Canvas:</h6>
        <ul>
            <li><strong>Form Config (JSON):</strong> Define os campos do formulário usando SurveyJS</li>
            <li><strong>System Prompt:</strong> Instruções enviadas ao Claude sobre como se comportar</li>
            <li><strong>User Prompt Template:</strong> Template com placeholders (ex: <code>{{tarefa}}</code>) preenchidos com dados do formulário</li>
            <li><strong>Max Questions:</strong> Número máximo de perguntas contextuais que Claude pode fazer (padrão: 5)</li>
        </ul>

        <h6>MVP - Limitações Atuais:</h6>
        <ul>
            <li>✅ Editar Canvas existentes (form config, prompts)</li>
            <li>❌ Criar novos Canvas (implementado em próxima versão)</li>
            <li>❌ Ativar/desativar Canvas (implementado em próxima versão)</li>
            <li>❌ Versionamento completo (implementado em próxima versão)</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../../src/views/admin-footer.php'; ?>
