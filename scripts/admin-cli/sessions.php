#!/usr/bin/env php
<?php
/**
 * CLI Admin Tool: Session Management
 *
 * Gerencia sessões ativas do sistema
 *
 * Uso:
 *   php sessions.php list            - Lista sessões ativas
 *   php sessions.php kill <id>       - Encerra sessão específica
 *   php sessions.php clean           - Remove sessões expiradas
 *   php sessions.php kill-user <uid> - Encerra todas sessões de um usuário
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

$db = Database::getInstance();

// Parse command
$command = $argv[1] ?? 'help';
$arg = $argv[2] ?? null;

switch ($command) {
    case 'list':
        listActiveSessions($db);
        break;

    case 'kill':
        if (!$arg) {
            echo "Erro: Session ID não fornecido.\n";
            echo "Uso: php sessions.php kill <session_id>\n";
            exit(1);
        }
        killSession($db, $arg);
        break;

    case 'kill-user':
        if (!$arg) {
            echo "Erro: User ID não fornecido.\n";
            echo "Uso: php sessions.php kill-user <user_id>\n";
            exit(1);
        }
        killUserSessions($db, (int)$arg);
        break;

    case 'clean':
        cleanExpiredSessions($db);
        break;

    case 'help':
    default:
        showHelp();
        break;
}

function listActiveSessions($db) {
    echo "\n═══════════════════════════════════════════════════════════════\n";
    echo "  SESSÕES ATIVAS\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    $sessions = $db->fetchAll("
        SELECT s.id, s.user_id, u.name, u.email, s.ip_address,
               s.last_activity, s.created_at,
               TIMESTAMPDIFF(MINUTE, s.last_activity, NOW()) as minutes_idle
        FROM sessions s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE TIMESTAMPDIFF(HOUR, s.last_activity, NOW()) < 24
        ORDER BY s.last_activity DESC
    ");

    if (empty($sessions)) {
        echo "Nenhuma sessão ativa encontrada.\n\n";
        return;
    }

    foreach ($sessions as $s) {
        echo sprintf("ID: %s\n", substr($s['id'], 0, 16) . '...');
        echo sprintf("Usuário: %s (%s) [ID: %d]\n", $s['name'], $s['email'], $s['user_id']);
        echo sprintf("IP: %s\n", $s['ip_address']);
        echo sprintf("Última atividade: %s (%d min atrás)\n",
            date('d/m/Y H:i:s', strtotime($s['last_activity'])),
            $s['minutes_idle']
        );
        echo sprintf("Criada em: %s\n", date('d/m/Y H:i:s', strtotime($s['created_at'])));
        echo "───────────────────────────────────────────────────────────────\n";
    }

    echo sprintf("\nTotal: %d sessões ativas\n\n", count($sessions));
}

function killSession($db, $sessionId) {
    echo "\n⚠️  Encerrando sessão: $sessionId\n";

    $deleted = $db->delete('sessions', 'id = :id', ['id' => $sessionId]);

    if ($deleted > 0) {
        echo "✅ Sessão encerrada com sucesso!\n\n";
    } else {
        echo "❌ Sessão não encontrada.\n\n";
    }
}

function killUserSessions($db, $userId) {
    echo "\n⚠️  Encerrando todas as sessões do usuário ID: $userId\n";

    // Buscar info do usuário
    $user = $db->fetchOne("SELECT name, email FROM users WHERE id = :id", ['id' => $userId]);

    if (!$user) {
        echo "❌ Usuário não encontrado.\n\n";
        return;
    }

    echo sprintf("Usuário: %s (%s)\n", $user['name'], $user['email']);

    $deleted = $db->delete('sessions', 'user_id = :user_id', ['user_id' => $userId]);

    echo sprintf("✅ %d sessão(ões) encerrada(s) com sucesso!\n\n", $deleted);
}

function cleanExpiredSessions($db) {
    echo "\n🧹 Limpando sessões expiradas (> 24h inativas)...\n";

    $deleted = $db->getConnection()->exec("
        DELETE FROM sessions
        WHERE TIMESTAMPDIFF(HOUR, last_activity, NOW()) >= 24
    ");

    echo sprintf("✅ %d sessão(ões) expirada(s) removida(s).\n\n", $deleted);
}

function showHelp() {
    echo <<<HELP

╔═══════════════════════════════════════════════════════════════╗
║          Plataforma Sunyata - Gerenciador de Sessões          ║
╚═══════════════════════════════════════════════════════════════╝

Uso: php sessions.php <comando> [argumentos]

Comandos disponíveis:

  list                    Lista todas sessões ativas (<24h)
  kill <session_id>       Encerra sessão específica
  kill-user <user_id>     Encerra todas sessões de um usuário
  clean                   Remove sessões expiradas (>24h)
  help                    Mostra esta ajuda

Exemplos:

  php sessions.php list
  php sessions.php kill abc123def456...
  php sessions.php kill-user 42
  php sessions.php clean


HELP;
}
