<?php
/**
 * Limpar todos os caches do servidor
 */

echo "<h1>Limpando Caches do Servidor</h1>";

// 1. Limpar OPcache
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<p>✅ OPcache limpo com sucesso!</p>";
    } else {
        echo "<p>⚠️ Falha ao limpar OPcache</p>";
    }
} else {
    echo "<p>ℹ️ OPcache não está disponível</p>";
}

// 2. Limpar cache de realpath
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "<p>✅ Cache de realpath limpo!</p>";
}

// 3. Limpar sessões antigas do banco
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Sunyata\Core\Database;

try {
    $db = Database::getInstance();

    // Remover sessões antigas (mais de 24h)
    $deleted = $db->query("DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    echo "<p>✅ Sessões antigas removidas do banco</p>";

} catch (Exception $e) {
    echo "<p>⚠️ Erro ao limpar sessões: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✅ Todos os caches foram limpos!</h2>";
echo "<p><a href='/'>← Voltar para a home</a></p>";
