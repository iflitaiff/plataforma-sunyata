<?php
/**
 * Teste Simples - Claude API Integration
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use Sunyata\AI\ClaudeService;

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Teste de Integração - Claude API                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

try {
    echo "1. Inicializando ClaudeService...\n";
    $claude = new ClaudeService();
    echo "   ✅ ClaudeService criado com sucesso!\n\n";

    echo "2. Preparando teste simples...\n";
    $testPrompt = "Olá! Este é um teste de integração. Por favor, responda apenas: 'Teste OK!'";

    echo "3. Enviando requisição para Claude API...\n";
    echo "   (Aguarde 5-10 segundos...)\n\n";

    $result = $claude->generate(
        $testPrompt,
        1, // user_id fake
        'test',
        'test_integration',
        ['test' => true],
        [
            'max_tokens' => 100,
            'temperature' => 0.5
        ]
    );

    if ($result['success']) {
        echo "✅ SUCESSO!\n\n";
        echo "Resposta Claude:\n";
        echo "─────────────────────────────────────────────────────────────\n";
        echo $result['response'] . "\n";
        echo "─────────────────────────────────────────────────────────────\n\n";

        echo "Métricas:\n";
        echo sprintf("   - Tokens Input: %d\n", $result['tokens']['input'] ?? 0);
        echo sprintf("   - Tokens Output: %d\n", $result['tokens']['output'] ?? 0);
        echo sprintf("   - Tokens Total: %d\n", $result['tokens']['total'] ?? 0);
        echo sprintf("   - Custo: USD %.6f\n", $result['cost_usd'] ?? 0);
        echo sprintf("   - Tempo: %d ms\n", $result['response_time_ms'] ?? 0);
        echo sprintf("   - History ID: %d\n\n", $result['history_id'] ?? 0);

        echo "✅ Integração funcionando perfeitamente!\n\n";
        exit(0);

    } else {
        echo "❌ ERRO na API:\n";
        echo "   " . ($result['error'] ?? 'Erro desconhecido') . "\n\n";
        if (isset($result['error_detail'])) {
            echo "Detalhes: " . $result['error_detail'] . "\n\n";
        }
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}
