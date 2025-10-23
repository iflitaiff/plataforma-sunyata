<?php
/**
 * Teste Remoto - API Canvas Jurídico
 * Este script simula uma chamada autenticada ao endpoint
 */

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Teste Remoto - API Canvas Jurídico                  ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Payload de teste
$payload = [
    'tarefa' => 'Teste simples de integração',
    'contexto' => 'Verificando funcionamento da API Claude',
    'entradas' => '',
    'restricoes' => '',
    'saida' => 'Apenas confirmar que funciona',
    'criterios' => ''
];

echo "1. Preparando payload...\n";
echo "   Tarefa: " . $payload['tarefa'] . "\n";
echo "   Contexto: " . $payload['contexto'] . "\n\n";

echo "2. Enviando requisição para produção...\n";
echo "   URL: https://portal.sunyataconsulting.com/api/generate-juridico.php\n";
echo "   ⚠️  NOTA: Este teste falhará com 401 (não autenticado)\n";
echo "   Isso é ESPERADO - significa que a API está funcionando!\n\n";

$ch = curl_init('https://portal.sunyataconsulting.com/api/generate-juridico.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "3. Resposta recebida:\n";
echo "   HTTP Status: $httpCode\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "   JSON válido: ✅\n";
        if (isset($data['error'])) {
            echo "   Erro: " . $data['error'] . "\n";
            if ($data['error'] === 'Usuário não autenticado') {
                echo "\n✅ SUCESSO! API está funcionando (erro esperado - sem sessão)\n";
                echo "   Para testar de verdade, acesse via navegador:\n";
                echo "   https://portal.sunyataconsulting.com/areas/juridico/canvas-juridico.php\n\n";
            }
        }
    } else {
        echo "   JSON inválido\n";
        echo "   Resposta: $response\n";
    }
} else {
    echo "   ❌ Sem resposta\n\n";
}
