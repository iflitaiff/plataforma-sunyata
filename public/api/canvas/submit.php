<?php
/**
 * API: Canvas Submit
 * Processa submissão de formulário SurveyJS e gera resposta via Claude
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\Core\Database;
use Sunyata\AI\ClaudeService;

// Headers
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Pegar dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON inválido']);
    exit;
}

$canvasId = $input['canvas_id'] ?? null;
$formData = $input['form_data'] ?? null;

if (!$canvasId || !$formData) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'canvas_id e form_data são obrigatórios']);
    exit;
}

try {
    $db = Database::getInstance();

    // Buscar canvas template
    $canvas = $db->fetchOne("
        SELECT id, slug, name, system_prompt, user_prompt_template, max_questions
        FROM canvas_templates
        WHERE id = :id AND is_active = 1
    ", ['id' => $canvasId]);

    if (!$canvas) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Canvas não encontrado']);
        exit;
    }

    // Construir prompt do usuário substituindo placeholders
    $userPrompt = $canvas['user_prompt_template'];

    // Substituir cada campo do form_data no template
    foreach ($formData as $key => $value) {
        // Ignorar campo de upload de arquivos por enquanto
        if ($key === 'documentos') {
            continue;
        }

        $placeholder = '{{' . $key . '}}';
        $userPrompt = str_replace($placeholder, $value, $userPrompt);
    }

    // Inicializar Claude Service
    $claude = new ClaudeService();

    // Chamar Claude API
    $result = $claude->generate(
        prompt: $userPrompt,
        userId: $_SESSION['user_id'],
        vertical: $_SESSION['user']['selected_vertical'] ?? 'juridico',
        toolName: $canvas['slug'],
        inputData: $formData,
        options: [
            'system' => $canvas['system_prompt'],
            'max_tokens' => 4096
        ]
    );

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'response' => $result['response'],
            'history_id' => $result['history_id'],
            'tokens' => $result['tokens'],
            'cost_usd' => $result['cost_usd']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Erro ao gerar conteúdo'
        ]);
    }

} catch (Exception $e) {
    error_log('Canvas submit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
}
