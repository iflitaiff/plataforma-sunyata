<?php
/**
 * Admin: Canvas Templates - Edição
 * Editar Canvas existente (form config + prompts)
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

// Obter ID do Canvas
$canvasId = $_GET['id'] ?? null;
if (!$canvasId) {
    $_SESSION['error'] = 'Canvas ID não fornecido';
    redirect(BASE_URL . '/admin/canvas-templates.php');
}

// Buscar Canvas
$canvas = $db->fetchOne("
    SELECT * FROM canvas_templates WHERE id = :id
", ['id' => $canvasId]);

if (!$canvas) {
    $_SESSION['error'] = 'Canvas não encontrado';
    redirect(BASE_URL . '/admin/canvas-templates.php');
}

// Processar formulário de edição
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Token de segurança inválido';
        $message_type = 'danger';
    } else {
        try {
            // Validar JSON do form_config
            $formConfig = $_POST['form_config'] ?? '';
            $jsonDecoded = json_decode($formConfig, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Form Config JSON inválido: ' . json_last_error_msg());
            }

            // Atualizar Canvas
            $updated = $db->update('canvas_templates', [
                'name' => $_POST['name'] ?? $canvas['name'],
                'form_config' => $formConfig,
                'system_prompt' => $_POST['system_prompt'] ?? '',
                'user_prompt_template' => $_POST['user_prompt_template'] ?? '',
                'max_questions' => (int)($_POST['max_questions'] ?? 5),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $canvasId]);

            if ($updated) {
                $message = 'Canvas atualizado com sucesso!';
                $message_type = 'success';

                // Recarregar canvas atualizado
                $canvas = $db->fetchOne("SELECT * FROM canvas_templates WHERE id = :id", ['id' => $canvasId]);
            } else {
                $message = 'Nenhuma mudança detectada';
                $message_type = 'info';
            }

        } catch (Exception $e) {
            $message = 'Erro ao atualizar Canvas: ' . $e->getMessage();
            $message_type = 'danger';
            error_log('Canvas edit error: ' . $e->getMessage());
        }
    }
}

// Formatar JSON para exibição (pretty print)
$formConfigFormatted = json_encode(json_decode($canvas['form_config']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$pageTitle = 'Editar Canvas: ' . $canvas['name'];

// Include header
include __DIR__ . '/../../src/views/admin-header.php';
?>

<style>
    /* Monaco Editor Container */
    #monaco-container {
        width: 100%;
        height: 600px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    /* Textarea fallback (se Monaco falhar) */
    #form_config_textarea {
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
    }

    .prompt-textarea {
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
    }
</style>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/">Admin</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/canvas-templates.php">Canvas Templates</a></li>
        <li class="breadcrumb-item active"><?= sanitize_output($canvas['name']) ?></li>
    </ol>
</nav>

<h1 class="mb-3">Editar Canvas: <?= sanitize_output($canvas['name']) ?></h1>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
        <?= sanitize_output($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Informações do Canvas -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informações do Canvas</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Slug:</strong> <code><?= sanitize_output($canvas['slug']) ?></code></p>
                <p><strong>Vertical:</strong> <span class="badge bg-primary"><?= ucfirst($canvas['vertical']) ?></span></p>
                <p><strong>Status:</strong>
                    <?php if ($canvas['is_active']): ?>
                        <span class="badge bg-success">ATIVO</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">INATIVO</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <p><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($canvas['created_at'])) ?></p>
                <p><strong>Última atualização:</strong> <?= date('d/m/Y H:i', strtotime($canvas['updated_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Formulário de Edição -->
<form method="POST" id="editForm">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <!-- Nome do Canvas -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">1. Nome do Canvas</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nome Exibido</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= sanitize_output($canvas['name']) ?>" required>
                <div class="form-text">Nome exibido aos usuários (ex: "Canvas Jurídico Geral")</div>
            </div>
        </div>
    </div>

    <!-- Form Configuration (Monaco Editor) -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">2. Form Configuration (SurveyJS JSON)</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Configure os campos do formulário usando a sintaxe SurveyJS. Use o Monaco Editor abaixo para editar o JSON.
            </p>

            <!-- Monaco Editor -->
            <div id="monaco-container"></div>

            <!-- Hidden textarea (para enviar o JSON) -->
            <textarea id="form_config" name="form_config" style="display: none;"><?= htmlspecialchars($canvas['form_config']) ?></textarea>

            <!-- Fallback textarea (se Monaco falhar) -->
            <noscript>
                <textarea id="form_config_textarea" name="form_config" class="form-control"
                          rows="20"><?= htmlspecialchars($canvas['form_config']) ?></textarea>
            </noscript>

            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-secondary" id="validateJson">
                    <i class="bi bi-check-circle"></i> Validar JSON
                </button>
                <span id="jsonStatus" class="ms-2"></span>
            </div>
        </div>
    </div>

    <!-- System Prompt -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">3. System Prompt</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Instruções enviadas ao Claude sobre como se comportar. Use <code>[PERGUNTA-N]</code> e <code>[RESPOSTA-FINAL]</code> como marcadores obrigatórios.
            </p>

            <textarea class="form-control prompt-textarea" id="system_prompt" name="system_prompt"
                      rows="15" required><?= htmlspecialchars($canvas['system_prompt']) ?></textarea>

            <div class="form-text">
                <strong>Importante:</strong> Os marcadores <code>[PERGUNTA-1]</code> até <code>[PERGUNTA-5]</code>
                e <code>[RESPOSTA-FINAL]</code> são obrigatórios para o sistema detectar o tipo de mensagem.
            </div>
        </div>
    </div>

    <!-- User Prompt Template -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">4. User Prompt Template</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Template do prompt do usuário com placeholders. Use <code>{{campo}}</code> para inserir valores do formulário.
            </p>

            <textarea class="form-control prompt-textarea" id="user_prompt_template" name="user_prompt_template"
                      rows="12" required><?= htmlspecialchars($canvas['user_prompt_template']) ?></textarea>

            <div class="form-text">
                <strong>Placeholders disponíveis:</strong> Os nomes dos campos definidos no Form Configuration.
                Ex: <code>{{tarefa}}</code>, <code>{{contexto}}</code>, <code>{{documentos}}</code>
            </div>
        </div>
    </div>

    <!-- Configurações -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">5. Configurações</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="max_questions" class="form-label">Máximo de Perguntas Contextuais</label>
                <input type="number" class="form-control" id="max_questions" name="max_questions"
                       value="<?= $canvas['max_questions'] ?>" min="1" max="10" required>
                <div class="form-text">Número máximo de perguntas que Claude pode fazer antes da resposta final (padrão: 5)</div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Salvar Alterações
        </button>
        <a href="canvas-templates.php" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<!-- Monaco Editor (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs/loader.js"></script>

<script>
    // Inicializar Monaco Editor
    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.0/min/vs' } });

    require(['vs/editor/editor.main'], function () {
        const editor = monaco.editor.create(document.getElementById('monaco-container'), {
            value: <?= json_encode($formConfigFormatted) ?>,
            language: 'json',
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: true },
            fontSize: 13,
            wordWrap: 'on',
            formatOnPaste: true,
            formatOnType: true
        });

        // Sincronizar editor com textarea hidden
        editor.onDidChangeModelContent(() => {
            document.getElementById('form_config').value = editor.getValue();
        });

        // Validar JSON
        document.getElementById('validateJson').addEventListener('click', () => {
            const jsonValue = editor.getValue();
            try {
                JSON.parse(jsonValue);
                document.getElementById('jsonStatus').innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> JSON válido!</span>';
            } catch (e) {
                document.getElementById('jsonStatus').innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> JSON inválido: ' + e.message + '</span>';
            }
        });

        // Validar antes de enviar
        document.getElementById('editForm').addEventListener('submit', (e) => {
            const jsonValue = editor.getValue();
            try {
                JSON.parse(jsonValue);
            } catch (error) {
                e.preventDefault();
                alert('Erro no JSON: ' + error.message + '\n\nPor favor, corrija o JSON antes de salvar.');
            }
        });
    });
</script>

<?php include __DIR__ . '/../../src/views/admin-footer.php'; ?>
