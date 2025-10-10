<?php
/**
 * Script para gerar index.php para todas as verticais
 * Uso: php scripts/generate-vertical-indexes.php
 */

// Configuração das verticais
$verticais_config = [
    'docencia' => [
        'nome' => 'Docência',
        'icone' => '👨‍🏫',
        'descricao' => 'Ferramentas para planejamento de aulas e gestão pedagógica',
        'disponivel' => true,
        'ferramentas' => [
            ['id' => 'canvas-docente', 'nome' => 'Canvas Docente', 'descricao' => 'Planejamento estruturado de aulas e atividades pedagógicas', 'icone' => '📋'],
            ['id' => 'canvas-pesquisa', 'nome' => 'Canvas Pesquisa', 'descricao' => 'Estruturação de projetos de pesquisa acadêmica', 'icone' => '🔬'],
            ['id' => 'biblioteca-prompts-jogos', 'nome' => 'Biblioteca de Prompts (Jogos)', 'descricao' => 'Coleção de prompts para gamificação educacional', 'icone' => '🎮'],
            ['id' => 'guia-prompts-jogos', 'nome' => 'Guia de Prompts (Jogos)', 'descricao' => 'Guia prático para criação de prompts em jogos educativos', 'icone' => '📖'],
            ['id' => 'repositorio-prompts', 'nome' => 'Repositório de Prompts', 'descricao' => 'Dicionário geral de prompts e técnicas', 'icone' => '📚']
        ]
    ],
    'pesquisa' => [
        'nome' => 'Pesquisa',
        'icone' => '🔬',
        'descricao' => 'Recursos para estruturação de projetos de pesquisa acadêmica e científica',
        'disponivel' => true,
        'ferramentas' => [
            ['id' => 'canvas-docente', 'nome' => 'Canvas Docente', 'descricao' => 'Planejamento estruturado de aulas e atividades', 'icone' => '📋'],
            ['id' => 'canvas-pesquisa', 'nome' => 'Canvas Pesquisa', 'descricao' => 'Estruturação de projetos de pesquisa acadêmica', 'icone' => '🔬'],
            ['id' => 'repositorio-prompts', 'nome' => 'Repositório de Prompts', 'descricao' => 'Dicionário geral de prompts e técnicas', 'icone' => '📚']
        ]
    ],
    'ifrj-alunos' => [
        'nome' => 'IFRJ - Alunos',
        'icone' => '🎓',
        'descricao' => 'Área exclusiva para alunos do IFRJ com ferramentas de apoio ao aprendizado',
        'disponivel' => true,
        'ferramentas' => [
            ['id' => 'biblioteca-prompts-jogos', 'nome' => 'Biblioteca de Prompts (Jogos)', 'descricao' => 'Coleção de prompts para gamificação', 'icone' => '🎮'],
            ['id' => 'guia-prompts-jogos', 'nome' => 'Guia de Prompts (Jogos)', 'descricao' => 'Guia prático para prompts em jogos', 'icone' => '📖'],
            ['id' => 'canvas-pesquisa', 'nome' => 'Canvas Pesquisa', 'descricao' => 'Estruturação de projetos de pesquisa', 'icone' => '🔬'],
            ['id' => 'repositorio-prompts', 'nome' => 'Repositório de Prompts', 'descricao' => 'Dicionário geral de prompts', 'icone' => '📚']
        ]
    ],
    'juridico' => [
        'nome' => 'Jurídico',
        'icone' => '⚖️',
        'descricao' => 'Ferramentas especializadas para profissionais do Direito',
        'disponivel' => true,
        'ferramentas' => [
            ['id' => 'canvas-juridico', 'nome' => 'Canvas Jurídico', 'descricao' => 'Estruturação de peças e análises jurídicas', 'icone' => '📋'],
            ['id' => 'guia-prompts-juridico', 'nome' => 'Guia de Prompts (Jurídico)', 'descricao' => 'Prompts especializados para Direito', 'icone' => '📖'],
            ['id' => 'padroes-avancados-juridico', 'nome' => 'Padrões Avançados (Jurídico)', 'descricao' => 'Técnicas avançadas para área jurídica', 'icone' => '⚡'],
            ['id' => 'repositorio-prompts', 'nome' => 'Repositório de Prompts', 'descricao' => 'Dicionário geral de prompts', 'icone' => '📚']
        ]
    ],
    'vendas' => [
        'nome' => 'Vendas',
        'icone' => '📈',
        'descricao' => 'Ferramentas para otimizar processos de vendas e relacionamento com clientes',
        'disponivel' => false,
        'ferramentas' => []
    ],
    'marketing' => [
        'nome' => 'Marketing',
        'icone' => '📢',
        'descricao' => 'Recursos para criação de conteúdo, campanhas e estratégias de marketing digital',
        'disponivel' => false,
        'ferramentas' => []
    ],
    'licitacoes' => [
        'nome' => 'Licitações',
        'icone' => '📋',
        'descricao' => 'Ferramentas para elaboração de propostas e gestão de processos licitatórios',
        'disponivel' => false,
        'ferramentas' => []
    ],
    'rh' => [
        'nome' => 'Recursos Humanos',
        'icone' => '👥',
        'descricao' => 'Soluções para recrutamento, seleção e gestão de pessoas',
        'disponivel' => false,
        'ferramentas' => []
    ],
    'geral' => [
        'nome' => 'Geral',
        'icone' => '🌐',
        'descricao' => 'Ferramentas de propósito geral para diversas áreas e aplicações',
        'disponivel' => false,
        'ferramentas' => []
    ]
];

// Template para vertical disponível
function generateAvailableIndex($slug, $config) {
    $ferramentas_php = var_export($config['ferramentas'], true);

    return <<<PHP
<?php
/**
 * Vertical: {$config['nome']}
 * Página principal com ferramentas disponíveis
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

require_login();

// Verificar acesso à vertical
if (!isset(\$_SESSION['user']['selected_vertical'])) {
    \$_SESSION['error'] = 'Por favor, complete o onboarding primeiro';
    redirect(BASE_URL . '/onboarding-step1.php');
}

\$user_vertical = \$_SESSION['user']['selected_vertical'];
\$is_demo = \$_SESSION['user']['is_demo'] ?? false;

// Verificar se tem acesso (vertical correta OU usuário demo)
if (\$user_vertical !== '{$slug}' && !\$is_demo) {
    \$_SESSION['error'] = 'Você não tem acesso a esta vertical';
    redirect(BASE_URL . '/dashboard.php');
}

// Definição das ferramentas desta vertical
\$ferramentas = {$ferramentas_php};

// Adicionar URLs às ferramentas
foreach (\$ferramentas as &\$ferramenta) {
    \$ferramenta['url'] = BASE_URL . "/areas/{$slug}/{\$ferramenta['id']}.php";
}

\$pageTitle = 'Vertical: {$config['nome']}';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \$pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .vertical-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .tool-card {
            transition: all 0.3s ease;
            height: 100%;
            border: 2px solid transparent;
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }
        .tool-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="vertical-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1>{$config['icone']} {$config['nome']}</h1>
                            <p class="lead mb-0">{$config['descricao']}</p>
                        </div>
                        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-light">
                            ← Voltar ao Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <?php if (\$is_demo): ?>
            <div class="alert alert-info">
                <strong>ℹ️ Modo Demo:</strong> Você está visualizando esta vertical em modo demonstração.
            </div>
        <?php endif; ?>

        <!-- Ferramentas Grid -->
        <div class="row g-4">
            <?php foreach (\$ferramentas as \$ferramenta): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= \$ferramenta['url'] ?>" class="text-decoration-none">
                        <div class="card tool-card">
                            <div class="card-body text-center p-4">
                                <div class="tool-icon"><?= \$ferramenta['icone'] ?></div>
                                <h5 class="card-title"><?= \$ferramenta['nome'] ?></h5>
                                <p class="card-text text-muted"><?= \$ferramenta['descricao'] ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Help Section -->
        <div class="row mt-5">
            <div class="col-md-8 offset-md-2">
                <div class="alert alert-light">
                    <h6>💡 Dica:</h6>
                    <p class="mb-0">
                        Explore todas as ferramentas para encontrar a que melhor se adequa às suas necessidades.
                        Para suporte, entre em contato: <a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

PHP;
}

// Template para vertical indisponível
function generateUnavailableIndex($slug, $config) {
    return <<<PHP
<?php
/**
 * Vertical: {$config['nome']} (Em breve)
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

session_name(SESSION_NAME);
session_start();

require_login();

\$pageTitle = 'Vertical: {$config['nome']} - Em Breve';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \$pageTitle ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .coming-soon-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <div class="coming-soon-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg">
                        <div class="card-body text-center p-5">
                            <div style="font-size: 5rem;" class="mb-4">{$config['icone']}</div>
                            <h1 class="mb-3">{$config['nome']}</h1>
                            <p class="text-muted lead mb-4">{$config['descricao']}</p>

                            <div class="alert alert-warning">
                                <strong>🚧 Em Desenvolvimento</strong>
                                <p class="mb-0 mt-2">
                                    Esta vertical está sendo desenvolvida e estará disponível em breve.
                                    Você receberá um email quando for liberada!
                                </p>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary">
                                    ← Voltar ao Dashboard
                                </a>
                                <a href="<?= BASE_URL ?>/onboarding-step2.php" class="btn btn-outline-secondary">
                                    Escolher Outra Vertical
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

PHP;
}

$base_path = __DIR__ . '/../public/areas';
$generated_count = 0;

foreach ($verticais_config as $slug => $config) {
    $vertical_path = "{$base_path}/{$slug}";

    if (!is_dir($vertical_path)) {
        mkdir($vertical_path, 0755, true);
    }

    $index_path = "{$vertical_path}/index.php";

    if ($config['disponivel']) {
        $content = generateAvailableIndex($slug, $config);
    } else {
        $content = generateUnavailableIndex($slug, $config);
    }

    file_put_contents($index_path, $content);

    $status = $config['disponivel'] ? '✓ Disponível' : '⏳ Em breve';
    echo "{$status}: {$slug}/index.php\n";
    $generated_count++;
}

echo "\n✅ Total de index.php gerados: {$generated_count}\n";
