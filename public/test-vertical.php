<?php
// Habilitar exibição de erros temporariamente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Acesso à Vertical</h1>";

try {
    echo "<p>1. Carregando autoload...</p>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";

    echo "<p>2. Carregando config...</p>";
    require_once __DIR__ . '/../config/config.php';
    echo "<p>✅ Config OK</p>";

    echo "<p>3. Iniciando sessão...</p>";
    session_name(SESSION_NAME);
    session_start();
    echo "<p>✅ Sessão iniciada</p>";

    echo "<p>4. Testando require_login()...</p>";
    require_login();
    echo "<p>✅ require_login() passou</p>";

    echo "<p>5. Verificando dados da sessão:</p>";
    echo "<pre>";
    echo "user_id: " . ($_SESSION['user_id'] ?? 'NÃO DEFINIDO') . "\n";
    echo "user array: " . (isset($_SESSION['user']) ? 'EXISTE' : 'NÃO EXISTE') . "\n";
    if (isset($_SESSION['user'])) {
        print_r($_SESSION['user']);
    }
    echo "</pre>";

    echo "<p>6. Verificando selected_vertical:</p>";
    if (!isset($_SESSION['user']['selected_vertical'])) {
        echo "<p>❌ selected_vertical NÃO ESTÁ DEFINIDO!</p>";
    } else {
        echo "<p>✅ selected_vertical = " . $_SESSION['user']['selected_vertical'] . "</p>";
    }

    echo "<p>7. Verificando constantes:</p>";
    echo "<pre>";
    echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NÃO DEFINIDO') . "\n";
    echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NÃO DEFINIDO') . "\n";
    echo "SUPPORT_EMAIL: " . (defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'NÃO DEFINIDO') . "\n";
    echo "</pre>";

    echo "<h2>✅ TODOS OS TESTES PASSARAM!</h2>";
    echo "<p>Se você está vendo esta mensagem, o problema está em outro lugar.</p>";

} catch (Exception $e) {
    echo "<h2>❌ ERRO CAPTURADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
