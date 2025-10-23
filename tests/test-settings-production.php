<?php
/**
 * Teste de Settings em Produção
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use Sunyata\Core\Database;
use Sunyata\Core\Settings;

echo "=== Teste Settings em Produção ===\n\n";

try {
    $db = Database::getInstance();
    echo "✅ Database conectado\n";

    $settings = Settings::getInstance();
    echo "✅ Settings instanciado\n";

    $juridico = $settings->get('juridico_requires_approval');
    echo "juridico_requires_approval: " . ($juridico ? 'true' : 'false') . "\n";

    $platform = $settings->get('platform_name');
    echo "platform_name: $platform\n";

    echo "\n✅ Tudo funcionando!\n";

} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
