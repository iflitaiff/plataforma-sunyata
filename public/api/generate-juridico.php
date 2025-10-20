<?php
/**
 * API Endpoint: Geração de Conteúdo Jurídico via Claude
 *
 * Recebe dados do Canvas Jurídico, gera prompt, chama Claude API,
 * guarda histórico (transparente ao usuário) e retorna resposta.
 *
 * @package Sunyata\API
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\AI\ClaudeService;

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuário não autenticado'
    ]);
    exit;
}

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido'
    ]);
    exit;
}

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Dados inválidos'
    ]);
    exit;
}

// Validação básica
if (empty($input['tarefa']) || empty($input['contexto'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Campos obrigatórios não preenchidos'
    ]);
    exit;
}

try {
    // Construir prompt (mesma lógica do JS original)
    $prompt = "Você é um advogado sênior especializado em grandes escritórios com vasta experiência em advocacia empresarial e conhecimento profundo da prática jurídica brasileira.\n\n";

    $prompt .= "**TAREFA/OBJETIVO JURÍDICO:**\n" . $input['tarefa'] . "\n\n";
    $prompt .= "**CONTEXTO & CLIENTE:**\n" . $input['contexto'] . "\n\n";

    if (!empty($input['entradas'])) {
        $prompt .= "**MATERIAIS DISPONÍVEIS:**\n" . $input['entradas'] . "\n\n";
    }

    if (!empty($input['restricoes'])) {
        $prompt .= "**RESTRIÇÕES & MARCO LEGAL:**\n" . $input['restricoes'] . "\n\n";
    }

    if (!empty($input['saida'])) {
        $prompt .= "**FORMATO DA ENTREGA:**\n" . $input['saida'] . "\n\n";
    }

    if (!empty($input['criterios'])) {
        $prompt .= "**CRITÉRIOS DE QUALIDADE:**\n" . $input['criterios'] . "\n\n";
    }

    $prompt .= "**INSTRUÇÕES IMPORTANTES:**\n";
    $prompt .= "- Mantenha rigor técnico-jurídico e aderência às melhores práticas de grandes escritórios\n";
    $prompt .= "- Considere sempre aspectos práticos de implementação e viabilidade econômica\n";
    $prompt .= "- Base suas sugestões na legislação brasileira vigente e jurisprudência consolidada\n";
    $prompt .= "- Estruture sua resposta de forma profissional e diretamente aplicável\n";
    $prompt .= "- Se alguma informação essencial estiver ausente, questione antes de prosseguir\n\n";

    $prompt .= "Faça-me perguntas indexadas, sequenciais, uma por vez até que você julgue entender o suficiente do contexto da tarefa, da qualidade esperada de sua resposta e interação comigo.";

    // Chamar Claude API via ClaudeService
    $claudeService = new ClaudeService();

    $result = $claudeService->generate(
        $prompt,
        $_SESSION['user_id'],
        $_SESSION['user']['selected_vertical'] ?? 'juridico',
        'canvas_juridico',
        $input,  // Dados do formulário (guardados no histórico)
        [
            'max_tokens' => 4096,
            'temperature' => 1.0
        ]
    );

    // Retornar resposta
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'response' => $result['response'],
            'prompt' => $prompt,  // Enviamos o prompt também
            'tokens' => $result['tokens'] ?? null,
            'history_id' => $result['history_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Erro ao gerar conteúdo',
            'prompt' => $prompt  // Mesmo em erro, mostramos o prompt gerado
        ]);
    }

} catch (Exception $e) {
    error_log('API generate-juridico error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor. Por favor, tente novamente.'
    ]);
}
