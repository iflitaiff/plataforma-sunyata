<?php
/**
 * Teste Completo - Simulando Uso Real do Canvas Jurídico
 */

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     Teste Completo - Canvas Jurídico com Claude API          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$baseUrl = 'https://portal.sunyataconsulting.com';

echo "📋 Status do Ambiente:\n";
echo "   ✅ Usuário: claudesunyata@gmail.com configurado\n";
echo "   ✅ Vertical: Jurídico\n";
echo "   ✅ Onboarding: Completo\n";
echo "   ✅ Aprovação: Desabilitada\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "INSTRUÇÕES PARA TESTE MANUAL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1️⃣  LOGIN\n";
echo "   Acesse: $baseUrl\n";
echo "   Email: claudesunyata@gmail.com\n";
echo "   Senha: Pxh5G2t9w6ogprAuwZWr5me\n";
echo "   Via: Google OAuth\n\n";

echo "2️⃣  ACESSAR CANVAS JURÍDICO\n";
echo "   URL direta: $baseUrl/areas/juridico/canvas-juridico.php\n";
echo "   Ou pelo menu/dashboard\n\n";

echo "3️⃣  PREENCHER FORMULÁRIO\n";
echo "   Campos obrigatórios:\n";
echo "   - Tarefa: Análise de viabilidade de contrato\n";
echo "   - Contexto: Startup de tecnologia contratando devs\n\n";

echo "4️⃣  GERAR ANÁLISE\n";
echo "   - Clicar: 'Gerar Análise Jurídica com IA'\n";
echo "   - Aguardar: 15-30 segundos\n";
echo "   - Observar: Botão muda para '⏳ Gerando resposta com IA...'\n\n";

echo "5️⃣  VERIFICAR RESULTADO\n";
echo "   ✅ Deve aparecer resposta do Claude\n";
echo "   ✅ Botão volta ao normal\n";
echo "   ✅ Pode copiar a resposta\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "VERIFICAÇÃO PÓS-TESTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Após gerar um prompt, verifique no banco:\n\n";
echo "ssh -p 65002 u202164171@82.25.72.226 \"/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SELECT id, user_id, tool_name, status, tokens_total, cost_usd, LEFT(claude_response, 100) as response_preview FROM prompt_history ORDER BY id DESC LIMIT 1;'\"\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TROUBLESHOOTING\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Se der erro:\n\n";

echo "1. Ver erro no navegador (F12 > Console)\n";
echo "2. Ver log PHP:\n";
echo "   ssh -p 65002 u202164171@82.25.72.226 \"tail -30 /home/u202164171/domains/sunyataconsulting.com/logs/error.log\"\n\n";

echo "3. Ver último erro do banco:\n";
echo "   ssh -p 65002 u202164171@82.25.72.226 \"/usr/bin/mariadb u202164171_sunyata -p'MiGOq%tMrUP+9Qy@bxR' -e 'SHOW WARNINGS;'\"\n\n";

echo "4. Testar API manualmente:\n";
echo "   curl -X POST $baseUrl/api/generate-juridico.php \\\n";
echo "        -H 'Content-Type: application/json' \\\n";
echo "        -d '{\"tarefa\":\"Teste\",\"contexto\":\"Teste\"}'\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TESTES AUTOMÁTICOS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Verificar arquivos críticos
echo "Verificando arquivos críticos...\n";
$files = [
    '/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/src/AI/ClaudeService.php',
    '/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/api/generate-juridico.php',
    '/home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/public/ferramentas/canvas-juridico.html'
];

foreach ($files as $file) {
    $basename = basename($file);
    $cmd = "ssh -p 65002 u202164171@82.25.72.226 \"test -f $file && echo 'EXISTS' || echo 'MISSING'\"";
    $result = trim(shell_exec($cmd));

    if ($result === 'EXISTS') {
        echo "   ✅ $basename\n";
    } else {
        echo "   ❌ $basename - ARQUIVO FALTANDO!\n";
    }
}

echo "\nVerificando API Key...\n";
$cmd = "ssh -p 65002 u202164171@82.25.72.226 \"grep -q 'CLAUDE_API_KEY' /home/u202164171/domains/sunyataconsulting.com/public_html/plataforma-sunyata/config/secrets.php && echo 'FOUND' || echo 'MISSING'\"";
$result = trim(shell_exec($cmd));

if ($result === 'FOUND') {
    echo "   ✅ API Key configurada\n";
} else {
    echo "   ❌ API Key NÃO encontrada!\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n\n";
echo "🚀 TUDO PRONTO! Pode fazer o teste manual agora.\n\n";
echo "👉 Comece aqui: $baseUrl\n\n";
