#!/usr/bin/env php
<?php
/**
 * CLI Admin Tool: Platform Statistics
 *
 * Exibe estatísticas gerais da plataforma
 *
 * Uso:
 *   php stats.php              - Estatísticas gerais
 *   php stats.php users        - Estatísticas de usuários
 *   php stats.php vertical     - Estatísticas por vertical
 *   php stats.php api          - Estatísticas de uso API Claude
 *
 * @package Sunyata\Scripts
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

use Sunyata\Core\Database;
use Sunyata\AI\ClaudeService;

// Apenas CLI
if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.\n");
}

$db = Database::getInstance();

// Parse command
$command = $argv[1] ?? 'general';

switch ($command) {
    case 'users':
        showUserStats($db);
        break;

    case 'vertical':
        showVerticalStats($db);
        break;

    case 'api':
        showApiStats();
        break;

    case 'general':
    default:
        showGeneralStats($db);
        break;
}

function showGeneralStats($db) {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║          Plataforma Sunyata - Estatísticas Gerais            ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    // Total de usuários
    $totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
    echo sprintf("👥 Total de Usuários: %d\n", $totalUsers);

    // Usuários ativos (últimos 7 dias)
    $activeUsers = $db->fetchOne("
        SELECT COUNT(*) as count FROM users
        WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ")['count'];
    echo sprintf("✅ Usuários Ativos (7 dias): %d (%.1f%%)\n",
        $activeUsers,
        $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0
    );

    // Novos usuários (últimos 7 dias)
    $newUsers = $db->fetchOne("
        SELECT COUNT(*) as count FROM users
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ")['count'];
    echo sprintf("🆕 Novos Usuários (7 dias): %d\n", $newUsers);

    // Por nível de acesso
    echo "\n📊 Por Nível de Acesso:\n";
    $levels = $db->fetchAll("
        SELECT access_level, COUNT(*) as count
        FROM users
        GROUP BY access_level
        ORDER BY count DESC
    ");
    foreach ($levels as $level) {
        echo sprintf("   - %s: %d\n", ucfirst($level['access_level']), $level['count']);
    }

    // Solicitações pendentes
    $pending = $db->fetchOne("
        SELECT COUNT(*) as count FROM vertical_access_requests WHERE status = 'pending'
    ")['count'];
    echo sprintf("\n⏳ Solicitações Pendentes: %d\n", $pending);

    // Sessões ativas
    $sessions = $db->fetchOne("
        SELECT COUNT(*) as count FROM sessions
        WHERE TIMESTAMPDIFF(HOUR, last_activity, NOW()) < 24
    ")['count'];
    echo sprintf("🔐 Sessões Ativas (<24h): %d\n", $sessions);

    // Espaço em disco (se disponível)
    if (function_exists('disk_free_space')) {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $used = $total - $free;
        $usedPercent = ($used / $total) * 100;
        echo sprintf("\n💾 Espaço em Disco: %.2f GB usado de %.2f GB (%.1f%%)\n",
            $used / 1024 / 1024 / 1024,
            $total / 1024 / 1024 / 1024,
            $usedPercent
        );
    }

    echo "\n";
}

function showUserStats($db) {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║          Plataforma Sunyata - Estatísticas de Usuários       ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    // Usuários com onboarding completo
    $completed = $db->fetchOne("
        SELECT COUNT(*) as count FROM users WHERE completed_onboarding = 1
    ")['count'];
    $total = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
    echo sprintf("✅ Onboarding Completo: %d / %d (%.1f%%)\n",
        $completed, $total, $total > 0 ? ($completed / $total) * 100 : 0
    );

    // Top 10 usuários mais ativos (tool access)
    echo "\n🔥 Top 10 Usuários Mais Ativos:\n";
    $topUsers = $db->fetchAll("
        SELECT u.name, u.email, COUNT(t.id) as accesses,
               MAX(t.accessed_at) as last_access
        FROM users u
        LEFT JOIN tool_access_logs t ON u.id = t.user_id
        GROUP BY u.id
        HAVING accesses > 0
        ORDER BY accesses DESC
        LIMIT 10
    ");

    if (!empty($topUsers)) {
        foreach ($topUsers as $i => $user) {
            echo sprintf("   %2d. %s (%s) - %d acessos\n",
                $i + 1,
                $user['name'],
                $user['email'],
                $user['accesses']
            );
        }
    } else {
        echo "   Nenhum acesso registrado ainda.\n";
    }

    // Últimos cadastros
    echo "\n📅 Últimos 5 Cadastros:\n";
    $recent = $db->fetchAll("
        SELECT name, email, access_level, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");
    foreach ($recent as $user) {
        echo sprintf("   - %s (%s) [%s] - %s\n",
            $user['name'],
            $user['email'],
            $user['access_level'],
            date('d/m/Y H:i', strtotime($user['created_at']))
        );
    }

    echo "\n";
}

function showVerticalStats($db) {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║          Plataforma Sunyata - Estatísticas por Vertical      ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    $verticals = $db->fetchAll("
        SELECT selected_vertical, COUNT(*) as users
        FROM users
        WHERE selected_vertical IS NOT NULL
        GROUP BY selected_vertical
        ORDER BY users DESC
    ");

    if (empty($verticals)) {
        echo "Nenhum usuário com vertical definida.\n\n";
        return;
    }

    foreach ($verticals as $v) {
        echo sprintf("📊 %s: %d usuários\n",
            ucfirst(str_replace('_', ' ', $v['selected_vertical'])),
            $v['users']
        );

        // Ferramentas mais acessadas nesta vertical
        $tools = $db->fetchAll("
            SELECT tool_slug, COUNT(*) as accesses
            FROM tool_access_logs
            WHERE vertical = :vertical
            GROUP BY tool_slug
            ORDER BY accesses DESC
            LIMIT 3
        ", ['vertical' => $v['selected_vertical']]);

        if (!empty($tools)) {
            echo "   Ferramentas mais usadas:\n";
            foreach ($tools as $tool) {
                echo sprintf("      - %s: %d acessos\n", $tool['tool_slug'], $tool['accesses']);
            }
        }
        echo "\n";
    }
}

function showApiStats() {
    echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║          Plataforma Sunyata - Estatísticas API Claude        ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    try {
        $claudeService = new ClaudeService();
        $stats = $claudeService->getUsageStats();

        echo sprintf("🤖 Total de Prompts Gerados: %d\n", $stats['total_prompts']);
        echo sprintf("🔢 Total de Tokens Usados: %s\n", number_format($stats['total_tokens']));
        echo sprintf("💰 Custo Total: USD %.4f\n", $stats['total_cost_usd']);

        if (!empty($stats['by_vertical'])) {
            echo "\n📊 Por Vertical:\n";
            foreach ($stats['by_vertical'] as $v) {
                echo sprintf("   %s: %d prompts | %s tokens | USD %.4f\n",
                    ucfirst($v['vertical']),
                    $v['count'],
                    number_format($v['tokens']),
                    $v['cost']
                );
            }
        }

        if (!empty($stats['last_7_days'])) {
            echo "\n📅 Últimos 7 Dias:\n";
            foreach ($stats['last_7_days'] as $day) {
                echo sprintf("   %s: %d prompts | %s tokens\n",
                    date('d/m/Y', strtotime($day['date'])),
                    $day['count'],
                    number_format($day['tokens'])
                );
            }
        }

        echo "\n";

    } catch (Exception $e) {
        echo "❌ Erro ao obter estatísticas da API: " . $e->getMessage() . "\n\n";
    }
}
