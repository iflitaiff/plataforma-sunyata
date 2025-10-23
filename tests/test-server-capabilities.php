<?php
/**
 * Script de Análise de Capacidades do Servidor Hostinger
 *
 * Verifica: PHP, extensões, permissões, comandos disponíveis
 */

header('Content-Type: text/plain; charset=utf-8');

echo "═══════════════════════════════════════════════════════════\n";
echo "  ANÁLISE DE CAPACIDADES - SERVIDOR HOSTINGER\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// 1. INFORMAÇÕES BÁSICAS DO SISTEMA
echo "📊 SISTEMA OPERACIONAL:\n";
echo str_repeat("─", 60) . "\n";
echo "OS: " . PHP_OS . "\n";
echo "OS Family: " . PHP_OS_FAMILY . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Filename: " . __FILE__ . "\n\n";

// 2. VERSÕES E CONFIGURAÇÕES PHP
echo "🐘 PHP:\n";
echo str_repeat("─", 60) . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP SAPI: " . PHP_SAPI . "\n";
echo "Zend Version: " . zend_version() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Disable Functions: " . (ini_get('disable_functions') ?: 'None') . "\n";
echo "Open Basedir: " . (ini_get('open_basedir') ?: 'None') . "\n\n";

// 3. EXTENSÕES PHP INSTALADAS
echo "📦 EXTENSÕES PHP RELEVANTES:\n";
echo str_repeat("─", 60) . "\n";
$relevantExtensions = [
    'curl', 'json', 'mbstring', 'pdo', 'pdo_mysql', 'openssl',
    'zip', 'gd', 'imagick', 'fileinfo', 'exif',
    'iconv', 'intl', 'xml', 'xmlreader', 'xmlwriter', 'simplexml',
    'dom', 'opcache', 'zlib', 'sodium', 'bcmath'
];

foreach ($relevantExtensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "$status $ext\n";
}

echo "\n📋 TODAS EXTENSÕES (" . count(get_loaded_extensions()) . " total):\n";
echo implode(', ', get_loaded_extensions()) . "\n\n";

// 4. FERRAMENTAS DE LINHA DE COMANDO
echo "🔧 FERRAMENTAS CLI DISPONÍVEIS:\n";
echo str_repeat("─", 60) . "\n";

$commands = [
    'composer' => 'Composer (gerenciador de dependências PHP)',
    'git' => 'Git (controle de versão)',
    'convert' => 'ImageMagick (processamento de imagens)',
    'pdftotext' => 'Poppler (extração de texto de PDF)',
    'gs' => 'Ghostscript (processamento de PDF)',
    'tesseract' => 'Tesseract OCR (reconhecimento de texto)',
    'python3' => 'Python 3',
    'node' => 'Node.js',
    'npm' => 'NPM',
    'ffmpeg' => 'FFmpeg (processamento de vídeo/áudio)',
    'zip' => 'Zip',
    'unzip' => 'Unzip',
    'tar' => 'Tar',
    'curl' => 'cURL CLI',
    'wget' => 'Wget',
    'mysql' => 'MySQL CLI',
    'ssh' => 'SSH Client'
];

foreach ($commands as $cmd => $desc) {
    $path = @shell_exec("which $cmd 2>/dev/null");
    if ($path && trim($path)) {
        echo "✅ $cmd: " . trim($path);
        // Tentar obter versão
        $version = @shell_exec("$cmd --version 2>&1 | head -1");
        if ($version) {
            echo " (" . trim(substr($version, 0, 80)) . ")";
        }
        echo "\n";
    } else {
        echo "❌ $cmd: Não disponível\n";
    }
}
echo "\n";

// 5. PERMISSÕES DE ESCRITA
echo "📁 PERMISSÕES DE DIRETÓRIO:\n";
echo str_repeat("─", 60) . "\n";
$baseDir = dirname(__DIR__);
$testDirs = [
    $baseDir => 'Root do projeto',
    $baseDir . '/uploads' => 'Diretório uploads (criar se necessário)',
    sys_get_temp_dir() => 'Temp dir do sistema',
    '/tmp' => '/tmp'
];

foreach ($testDirs as $dir => $label) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }

    $readable = is_readable($dir) ? '✅' : '❌';
    $writable = is_writable($dir) ? '✅' : '❌';
    $executable = is_executable($dir) ? '✅' : '❌';

    echo "$label:\n";
    echo "  Path: $dir\n";
    echo "  Exists: " . (file_exists($dir) ? '✅' : '❌') . "\n";
    echo "  Read: $readable  Write: $writable  Execute: $executable\n";

    if (file_exists($dir)) {
        echo "  Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
    }
    echo "\n";
}

// 6. FUNÇÕES PERIGOSAS/DESABILITADAS
echo "⚠️  FUNÇÕES EXECUTÁVEIS:\n";
echo str_repeat("─", 60) . "\n";
$execFunctions = [
    'exec', 'shell_exec', 'system', 'passthru', 'proc_open',
    'popen', 'pcntl_exec'
];

foreach ($execFunctions as $func) {
    $available = function_exists($func) ? '✅' : '❌';
    echo "$available $func()\n";
}
echo "\n";

// 7. LIMITES DE RECURSOS
echo "💾 LIMITES E RECURSOS:\n";
echo str_repeat("─", 60) . "\n";
echo "Disk Free Space: " . formatBytes(disk_free_space('.')) . "\n";
echo "Disk Total Space: " . formatBytes(disk_total_space('.')) . "\n";

if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    echo "Load Average: " . implode(', ', $load) . "\n";
}

// Teste de memória disponível
$memoryTest = memory_get_usage();
echo "Memory Usage (current): " . formatBytes($memoryTest) . "\n";
echo "Memory Peak: " . formatBytes(memory_get_peak_usage()) . "\n\n";

// 8. CAPACIDADES DE PROCESSAMENTO DE ARQUIVOS
echo "📄 PROCESSAMENTO DE ARQUIVOS:\n";
echo str_repeat("─", 60) . "\n";

// PDF
if (extension_loaded('pdflib')) {
    echo "✅ PDFLib extension available\n";
} else {
    echo "❌ PDFLib extension not available\n";
}

// Verificar se pode processar PDFs via shell
$pdfToText = @shell_exec("which pdftotext 2>/dev/null");
echo ($pdfToText ? "✅" : "❌") . " pdftotext (Poppler utils)\n";

// ZIP
echo (extension_loaded('zip') ? "✅" : "❌") . " ZIP extension\n";

// GD/Imagick para processar imagens
if (extension_loaded('gd')) {
    echo "✅ GD extension\n";
    $gdInfo = gd_info();
    echo "   - JPEG: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - PNG: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - GIF: " . ($gdInfo['GIF Read Support'] ? 'Yes' : 'No') . "\n";
}

if (extension_loaded('imagick')) {
    echo "✅ ImageMagick extension\n";
    $imagick = new Imagick();
    $formats = $imagick->queryFormats();
    echo "   - Supports " . count($formats) . " formats\n";
    echo "   - PDF support: " . (in_array('PDF', $formats) ? 'Yes' : 'No') . "\n";
}

echo "\n";

// 9. COMPOSER E DEPENDÊNCIAS
echo "📚 COMPOSER:\n";
echo str_repeat("─", 60) . "\n";
$composerPath = @shell_exec("which composer 2>/dev/null");
if ($composerPath && trim($composerPath)) {
    echo "✅ Composer: " . trim($composerPath) . "\n";
    $composerVersion = @shell_exec("composer --version 2>&1");
    echo "   Version: " . trim($composerVersion) . "\n";

    // Verificar se composer.json existe
    $composerJson = $baseDir . '/composer.json';
    if (file_exists($composerJson)) {
        echo "✅ composer.json exists\n";
        $vendorDir = $baseDir . '/vendor';
        if (is_dir($vendorDir)) {
            echo "✅ vendor/ directory exists\n";

            // Contar pacotes instalados
            $installed = $vendorDir . '/composer/installed.json';
            if (file_exists($installed)) {
                $data = json_decode(file_get_contents($installed), true);
                $count = isset($data['packages']) ? count($data['packages']) : 0;
                echo "   Packages installed: $count\n";
            }
        } else {
            echo "⚠️  vendor/ directory missing (run composer install)\n";
        }
    } else {
        echo "❌ composer.json not found\n";
    }
} else {
    echo "❌ Composer not available\n";
    echo "   📝 Pode instalar localmente e fazer upload do vendor/\n";
}
echo "\n";

// 10. RECOMENDAÇÕES
echo "💡 RECOMENDAÇÕES:\n";
echo str_repeat("─", 60) . "\n";

$recommendations = [];

if (!extension_loaded('imagick')) {
    $recommendations[] = "Solicitar habilitação da extensão ImageMagick (processar PDFs/imagens)";
}

if (!trim(@shell_exec("which pdftotext 2>/dev/null"))) {
    $recommendations[] = "Poppler utils não disponível (extração de texto de PDF limitada)";
}

if (ini_get('memory_limit') !== '-1' && intval(ini_get('memory_limit')) < 256) {
    $recommendations[] = "Memory limit baixo (" . ini_get('memory_limit') . "), considerar aumentar para 256M+";
}

if (intval(ini_get('upload_max_filesize')) < 10) {
    $recommendations[] = "Upload max filesize baixo, pode limitar uploads de documentos";
}

if (!function_exists('exec') || !function_exists('shell_exec')) {
    $recommendations[] = "Funções exec desabilitadas - processamento via CLI limitado";
}

if (empty($recommendations)) {
    echo "✅ Servidor bem configurado para a aplicação!\n";
} else {
    foreach ($recommendations as $i => $rec) {
        echo ($i + 1) . ". $rec\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  FIM DA ANÁLISE\n";
echo "═══════════════════════════════════════════════════════════\n";

// Helper function
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
