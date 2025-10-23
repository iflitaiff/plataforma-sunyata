#!/usr/bin/env php
<?php
/**
 * CLI Admin Tool: API Cost Monitor
 *
 * Verifica custo da API Claude e envia alertas por email
 *
 * Uso:
 *   php check-api-cost.php           - Check e envia email se necessário
 *   php check-api-cost.php --dry-run - Apenas mostra stats, não envia email
 *
 * Adicionar ao crontab para rodar diariamente:
 *   0 9 * * * cd /path/to/project && php scripts/admin-cli/check-api-cost.php
 *
 * @package Sunyata\Scripts
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

use Sunyata\Core\Database;

// Apenas CLI
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.\n");
}

$dryRun = in_array('--dry-run', $argv);

$db = Database::getInstance();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          Monitor de Custo API Claude                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Configurações
$COST_LIMIT_MONTHLY = 10.00; // USD
$ALERT_THRESHOLD_50 = $COST_LIMIT_MONTHLY * 0.50;
$ALERT_THRESHOLD_80 = $COST_LIMIT_MONTHLY * 0.80;
$ALERT_THRESHOLD_100 = $COST_LIMIT_MONTHLY * 1.00;

// Buscar stats do mês
$stats = $db->fetchOne("
    SELECT
        COUNT(*) as total_prompts,
        SUM(tokens_total) as total_tokens,
        SUM(cost_usd) as total_cost
    FROM prompt_history
    WHERE status = 'success'
    AND MONTH(created_at) = MONTH(NOW())
    AND YEAR(created_at) = YEAR(NOW())
");

$cost = $stats['total_cost'] ?? 0;
$prompts = $stats['total_prompts'] ?? 0;
$tokens = $stats['total_tokens'] ?? 0;

$percent = ($cost / $COST_LIMIT_MONTHLY) * 100;

echo sprintf("📊 Estatísticas do Mês Atual:\n");
echo sprintf("   - Prompts Gerados: %d\n", $prompts);
echo sprintf("   - Tokens Usados: %s\n", number_format($tokens));
echo sprintf("   - Custo Total: USD %.4f\n", $cost);
echo sprintf("   - Limite Mensal: USD %.2f\n", $COST_LIMIT_MONTHLY);
echo sprintf("   - Uso: %.1f%%\n\n", $percent);

// Determinar nível de alerta
$alertLevel = null;
if ($cost >= $ALERT_THRESHOLD_100) {
    $alertLevel = 'critical';
    $alertMessage = "⛔ CRÍTICO: Limite mensal EXCEDIDO!";
} elseif ($cost >= $ALERT_THRESHOLD_80) {
    $alertLevel = 'high';
    $alertMessage = "⚠️ ALERTA: Uso acima de 80% do limite";
} elseif ($cost >= $ALERT_THRESHOLD_50) {
    $alertLevel = 'medium';
    $alertMessage = "⚡ AVISO: Uso acima de 50% do limite";
} else {
    $alertMessage = "✅ OK: Uso dentro do normal";
}

echo "$alertMessage\n\n";

// Enviar email se necessário
if ($alertLevel && !$dryRun) {
    echo "📧 Enviando notificação por email...\n";

    $to = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'filipe.litaiff@ifrj.edu.br';
    $subject = "[Plataforma Sunyata] Alerta de Custo API Claude - " . ucfirst($alertLevel);

    $body = <<<EMAIL
Olá,

Este é um alerta automático do sistema de monitoramento de custo da API Claude.

═══════════════════════════════════════════════════════════════
ESTATÍSTICAS DO MÊS ATUAL
═══════════════════════════════════════════════════════════════

Prompts Gerados: $prompts
Tokens Usados: {$tokens}
Custo Total: USD {$cost}
Limite Mensal: USD {$COST_LIMIT_MONTHLY}
Uso: {$percent}%

$alertMessage

═══════════════════════════════════════════════════════════════
AÇÕES RECOMENDADAS
═══════════════════════════════════════════════════════════════

EMAIL;

    if ($alertLevel === 'critical') {
        $body .= <<<EMAIL

⛔ O custo EXCEDEU o limite configurado!

1. Revisar imediatamente o uso da API
2. Considerar desabilitar temporariamente o Canvas Jurídico
3. Verificar se há uso anômalo (ex: loops, testes)
4. Aumentar o limite mensal se justificado


EMAIL;
    } elseif ($alertLevel === 'high') {
        $body .= <<<EMAIL

⚠️ O custo está próximo do limite (>80%)

1. Monitorar uso diário
2. Revisar configurações de max_tokens
3. Considerar otimização de prompts
4. Preparar ação para limite


EMAIL;
    } elseif ($alertLevel === 'medium') {
        $body .= <<<EMAIL

⚡ O custo atingiu 50% do limite

1. Revisar tendência de crescimento
2. Projetar custo estimado para fim do mês
3. Considerar ajustes preventivos


EMAIL;
    }

    $body .= <<<EMAIL

═══════════════════════════════════════════════════════════════

Acesse o dashboard admin para mais detalhes:
https://portal.sunyataconsulting.com/admin/

Para alterar o limite mensal, edite:
scripts/admin-cli/check-api-cost.php (linha 29)

---
Este é um email automático. Para desabilitar, remova do crontab.
Plataforma Sunyata - Sunyata Consulting

EMAIL;

    $headers = [
        'From: Plataforma Sunyata <noreply@sunyataconsulting.com>',
        'Reply-To: ' . $to,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=utf-8'
    ];

    if (mail($to, $subject, $body, implode("\r\n", $headers))) {
        echo "✅ Email enviado para: $to\n\n";
    } else {
        echo "❌ Falha ao enviar email\n\n";
    }

} elseif ($dryRun) {
    echo "🔍 Modo dry-run: Email NÃO foi enviado\n\n";
} else {
    echo "ℹ️  Nenhum alerta necessário no momento\n\n";
}

exit($alertLevel === 'critical' ? 1 : 0);
