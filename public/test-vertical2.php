<?php
// Habilitar exibição de erros temporariamente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Inclusão da Vertical</h1>";

try {
    echo "<p>1. Verificando arquivo da vertical pesquisa...</p>";
    $vertical_file = __DIR__ . '/areas/pesquisa/index.php';
    echo "<p>Caminho: $vertical_file</p>";

    if (file_exists($vertical_file)) {
        echo "<p>✅ Arquivo existe</p>";
        echo "<p>Tamanho: " . filesize($vertical_file) . " bytes</p>";
        echo "<p>Permissões: " . substr(sprintf('%o', fileperms($vertical_file)), -4) . "</p>";
    } else {
        echo "<p>❌ Arquivo NÃO existe!</p>";
    }

    echo "<p>2. Listando conteúdo do diretório areas/:</p>";
    $areas_dir = __DIR__ . '/areas';
    if (is_dir($areas_dir)) {
        echo "<pre>";
        $items = scandir($areas_dir);
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..') {
                $full_path = $areas_dir . '/' . $item;
                $type = is_dir($full_path) ? '[DIR]' : '[FILE]';
                echo "$type $item\n";

                if (is_dir($full_path) && $item === 'pesquisa') {
                    echo "  Conteúdo de pesquisa/:\n";
                    $sub_items = scandir($full_path);
                    foreach ($sub_items as $sub_item) {
                        if ($sub_item !== '.' && $sub_item !== '..') {
                            echo "    - $sub_item\n";
                        }
                    }
                }
            }
        }
        echo "</pre>";
    } else {
        echo "<p>❌ Diretório areas/ não existe!</p>";
    }

    echo "<p>3. Agora vou tentar INCLUIR o arquivo da vertical...</p>";
    echo "<hr>";

    // Preparar ambiente antes de incluir
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../config/config.php';
    session_name(SESSION_NAME);
    session_start();

    // Tentar incluir a página da vertical
    include $vertical_file;

} catch (Exception $e) {
    echo "<h2>❌ ERRO CAPTURADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ FATAL ERROR CAPTURADO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
