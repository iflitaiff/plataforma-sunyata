<?php
// Habilitar exibição de erros temporariamente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_name(SESSION_NAME);
session_start();

echo "<h1>Teste de Configuração</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Name: " . SESSION_NAME . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'não definido') . "</p>";

// Testar Database
try {
    $db = Sunyata\Core\Database::getInstance();
    echo "<p>✅ Database conectado!</p>";

    $result = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    if ($result) {
        echo "<p>✅ Query funcionou! Total de usuários: " . $result['count'] . "</p>";
    } else {
        echo "<p>⚠️ Query retornou vazio</p>";
    }

    // Testar se usuário existe na sessão
    $userId = $_SESSION['user_id'];
    $user = $db->fetchOne("SELECT id, email, selected_vertical, completed_onboarding FROM users WHERE id = :id", ['id' => $userId]);
    echo "<p>User da sessão:</p>";
    echo "<pre>" . print_r($user, true) . "</pre>";

} catch (Exception $e) {
    echo "<p>❌ Erro no Database: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Testar require_login
echo "<hr><p>Testando require_login()...</p>";
try {
    require_login();
    echo "<p>✅ require_login() passou!</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro no require_login: " . $e->getMessage() . "</p>";
}
