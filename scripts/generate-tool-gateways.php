<?php
/**
 * Script para gerar automaticamente gateways PHP para ferramentas HTML
 * Uso: php scripts/generate-tool-gateways.php
 */

// Mapeamento de ferramentas por vertical (conforme tabela do usuário)
$vertical_tools = [
    'docencia' => [
        'canvas-docente',
        'canvas-pesquisa',
        'biblioteca-prompts-jogos',
        'guia-prompts-jogos',
        'repositorio-prompts'
    ],
    'pesquisa' => [
        'canvas-docente',
        'canvas-pesquisa',
        'repositorio-prompts'
    ],
    'ifrj-alunos' => [
        'biblioteca-prompts-jogos',
        'guia-prompts-jogos',
        'canvas-pesquisa',
        'repositorio-prompts'
    ],
    'juridico' => [
        'canvas-juridico',
        'guia-prompts-juridico',
        'padroes-avancados-juridico',
        'repositorio-prompts'
    ]
];

// Template para gateways
function generateGateway($vertical, $tool_slug, $tool_name) {
    return <<<PHP
<?php
/**
 * Gateway: {$tool_name}
 * Embeda o HTML da ferramenta com controle de acesso
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

use Sunyata\\Core\\Database;

require_login();

// Verificar acesso à vertical
if (!isset(\$_SESSION['user']['selected_vertical'])) {
    \$_SESSION['error'] = 'Por favor, complete o onboarding primeiro';
    redirect(BASE_URL . '/onboarding-step1.php');
}

\$user_vertical = \$_SESSION['user']['selected_vertical'];
\$is_demo = \$_SESSION['user']['is_demo'] ?? false;

// Verificar se tem acesso (vertical {$vertical} OU usuário demo)
if (\$user_vertical !== '{$vertical}' && !\$is_demo) {
    \$_SESSION['error'] = 'Você não tem acesso a esta ferramenta';
    redirect(BASE_URL . '/dashboard.php');
}

// Log de acesso para analytics
try {
    \$db = Database::getInstance();
    \$db->insert('tool_access_logs', [
        'user_id' => \$_SESSION['user_id'],
        'tool_slug' => '{$tool_slug}',
        'vertical' => \$user_vertical,
        'ip_address' => \$_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => \$_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
} catch (Exception \$e) {
    error_log('Erro ao registrar acesso à ferramenta: ' . \$e->getMessage());
}

// Caminho para o HTML da ferramenta
\$tool_html = __DIR__ . '/../../ferramentas/{$tool_slug}.html';

// Verificar se arquivo existe
if (!file_exists(\$tool_html)) {
    http_response_code(404);
    echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ferramenta não encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>⚠️ Ferramenta não encontrada</h4>
            <p>O arquivo da ferramenta não foi localizado no servidor.</p>
            <a href="' . BASE_URL . '/areas/{$vertical}/" class="btn btn-primary">Voltar</a>
        </div>
    </div>
</body>
</html>';
    exit;
}

// Embedar o HTML da ferramenta
readfile(\$tool_html);

PHP;
}

// Nomes amigáveis para ferramentas
$tool_names = [
    'canvas-docente' => 'Canvas Docente',
    'canvas-pesquisa' => 'Canvas Pesquisa',
    'canvas-juridico' => 'Canvas Jurídico',
    'biblioteca-prompts-jogos' => 'Biblioteca de Prompts (Jogos)',
    'guia-prompts-jogos' => 'Guia de Prompts (Jogos)',
    'guia-prompts-juridico' => 'Guia de Prompts (Jurídico)',
    'padroes-avancados-juridico' => 'Padrões Avançados (Jurídico)',
    'repositorio-prompts' => 'Repositório de Prompts'
];

$generated_count = 0;
$base_path = __DIR__ . '/../public/areas';

foreach ($vertical_tools as $vertical => $tools) {
    $vertical_path = "{$base_path}/{$vertical}";

    if (!is_dir($vertical_path)) {
        mkdir($vertical_path, 0755, true);
    }

    foreach ($tools as $tool_slug) {
        $tool_name = $tool_names[$tool_slug] ?? ucfirst(str_replace('-', ' ', $tool_slug));
        $gateway_path = "{$vertical_path}/{$tool_slug}.php";

        $content = generateGateway($vertical, $tool_slug, $tool_name);
        file_put_contents($gateway_path, $content);

        echo "✓ Criado: {$vertical}/{$tool_slug}.php\n";
        $generated_count++;
    }
}

echo "\n✅ Total de gateways gerados: {$generated_count}\n";
