#!/usr/bin/env php
<?php
/**
 * CLI Admin Tool: Cache Management
 *
 * Gerencia cache da plataforma
 *
 * Uso:
 *   php cache.php clear-settings    - Limpa cache de Settings
 *   php cache.php clear-sessions    - Remove sessões expiradas
 *   php cache.php clear-logs        - Remove logs antigos (>90 dias)
 *   php cache.php clear-all         - Limpa tudo
 *
 * @package Sunyata\Scripts
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

use Sunyata\Core\Database;
use Sunyata\Core\Settings;

// Apenas CLI
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.\n");
}

$db = Database::getInstance();

// Parse command
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'clear-settings':
        clearSettingsCache();
        break;

    case 'clear-sessions':
        clearExpiredSessions($db);
        break;

    case 'clear-logs':
        clearOldLogs($db);
        break;

    case 'clear-all':
        clearAll($db);
        break;

    case 'help':
    default:
        showHelp();
        break;
}

function clearSettingsCache() {
    echo "\n🧹 Limpando cache de Settings...\n";

    try {
        $settings = Settings::getInstance();
        $settings->clearCache();
        echo "✅ Cache de Settings limpo com sucesso!\n\n";
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "\n\n";
    }
}

function clearExpiredSessions($db) {
    echo "\n🧹 Removendo sessões expiradas (>24h)...\n";

    $deleted = $db->getConnection()->exec("
        DELETE FROM sessions
        WHERE TIMESTAMPDIFF(HOUR, last_activity, NOW()) >= 24
    ");

    echo sprintf("✅ %d sessão(ões) removida(s).\n\n", $deleted);
}

function clearOldLogs($db) {
    echo "\n🧹 Removendo logs antigos (>90 dias)...\n";

    // Tool access logs
    $deleted1 = $db->getConnection()->exec("
        DELETE FROM tool_access_logs
        WHERE accessed_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    ");
    echo sprintf("   - tool_access_logs: %d registros removidos\n", $deleted1);

    // Audit logs (manter por mais tempo - 2 anos)
    $deleted2 = $db->getConnection()->exec("
        DELETE FROM audit_logs
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 730 DAY)
        AND user_id IS NULL
    ");
    echo sprintf("   - audit_logs (anonimizados): %d registros removidos\n", $deleted2);

    // Prompt history (opcional - manter por 1 ano)
    $deleted3 = $db->getConnection()->exec("
        DELETE FROM prompt_history
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)
    ");
    echo sprintf("   - prompt_history: %d registros removidos\n", $deleted3);

    echo sprintf("\n✅ Total: %d registros removidos.\n\n", $deleted1 + $deleted2 + $deleted3);
}

function clearAll($db) {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║          Limpeza Completa de Cache e Dados Antigos           ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";

    clearSettingsCache();
    clearExpiredSessions($db);
    clearOldLogs($db);

    echo "✅ Limpeza completa finalizada!\n\n";
}

function showHelp() {
    echo <<<HELP

╔═══════════════════════════════════════════════════════════════╗
║          Plataforma Sunyata - Gerenciador de Cache            ║
╚═══════════════════════════════════════════════════════════════╝

Uso: php cache.php <comando>

Comandos disponíveis:

  clear-settings          Limpa cache de configurações em memória
  clear-sessions          Remove sessões expiradas (>24h)
  clear-logs              Remove logs antigos (>90 dias)
  clear-all               Executa todas limpezas acima
  help                    Mostra esta ajuda

Exemplos:

  php cache.php clear-settings
  php cache.php clear-all

Nota: Logs de auditoria são mantidos por 2 anos (LGPD).
      Prompt history é mantido por 1 ano.


HELP;
}
