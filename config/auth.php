<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTA: A função require_login() está definida em config/config.php
// para evitar conflito de redeclaração de função.

function current_user_name(): string
{
    return $_SESSION['user']['name'] ?? 'Visitante';
}

/**
 * Verifica se o usuário tem acesso a uma vertical específica
 *
 * @param string $vertical Slug da vertical (docencia, pesquisa, etc)
 * @return bool True se tem acesso, False caso contrário
 */
function has_vertical_access(string $vertical): bool
{
    // Usuário deve estar logado
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Usuários demo têm acesso a todas as verticais
    if (isset($_SESSION['user']['is_demo']) && $_SESSION['user']['is_demo']) {
        return true;
    }

    // Verificar se a vertical do usuário corresponde
    $user_vertical = $_SESSION['user']['selected_vertical'] ?? null;
    return $user_vertical === $vertical;
}

/**
 * Verifica se o usuário tem acesso a uma ferramenta específica
 *
 * @param string $tool_slug Slug da ferramenta (canvas-docente, etc)
 * @return bool True se tem acesso, False caso contrário
 */
function has_tool_access(string $tool_slug): bool
{
    // Usuário deve estar logado
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Usuários demo têm acesso a todas as ferramentas
    if (isset($_SESSION['user']['is_demo']) && $_SESSION['user']['is_demo']) {
        return true;
    }

    // Mapeamento de ferramentas por vertical
    $tool_to_verticals = [
        'canvas-docente' => ['docencia', 'pesquisa'],
        'canvas-pesquisa' => ['docencia', 'pesquisa', 'ifrj_alunos'],
        'canvas-juridico' => ['juridico'],
        'biblioteca-prompts-jogos' => ['docencia', 'ifrj_alunos'],
        'guia-prompts-jogos' => ['docencia', 'ifrj_alunos'],
        'guia-prompts-juridico' => ['juridico'],
        'padroes-avancados-juridico' => ['juridico'],
        'repositorio-prompts' => ['docencia', 'pesquisa', 'ifrj_alunos', 'juridico'] // Disponível para todos
    ];

    // Verificar se a ferramenta existe
    if (!isset($tool_to_verticals[$tool_slug])) {
        return false;
    }

    $user_vertical = $_SESSION['user']['selected_vertical'] ?? null;
    return in_array($user_vertical, $tool_to_verticals[$tool_slug]);
}

/**
 * Retorna as ferramentas disponíveis para a vertical do usuário atual
 *
 * @return array Lista de ferramentas com metadados
 */
function get_user_tools(): array
{
    // Usuário deve estar logado
    if (!isset($_SESSION['user_id'])) {
        return [];
    }

    $user_vertical = $_SESSION['user']['selected_vertical'] ?? null;
    $is_demo = $_SESSION['user']['is_demo'] ?? false;

    // Todas as ferramentas disponíveis com metadados
    $all_tools = [
        'canvas-docente' => [
            'id' => 'canvas-docente',
            'nome' => 'Canvas Docente',
            'descricao' => 'Planejamento estruturado de aulas',
            'icone' => '📋',
            'url' => BASE_URL . '/ferramentas/canvas-docente.html',
            'verticais' => ['docencia', 'pesquisa']
        ],
        'canvas-pesquisa' => [
            'id' => 'canvas-pesquisa',
            'nome' => 'Canvas Pesquisa',
            'descricao' => 'Estruturação de projetos de pesquisa',
            'icone' => '🔬',
            'url' => BASE_URL . '/ferramentas/canvas-pesquisa.html',
            'verticais' => ['docencia', 'pesquisa', 'ifrj_alunos']
        ],
        'canvas-juridico' => [
            'id' => 'canvas-juridico',
            'nome' => 'Canvas Jurídico',
            'descricao' => 'Estruturação de peças jurídicas',
            'icone' => '⚖️',
            'url' => BASE_URL . '/ferramentas/canvas-juridico.html',
            'verticais' => ['juridico']
        ],
        'biblioteca-prompts-jogos' => [
            'id' => 'biblioteca-prompts-jogos',
            'nome' => 'Biblioteca de Prompts (Jogos)',
            'descricao' => 'Prompts para gamificação educacional',
            'icone' => '🎮',
            'url' => BASE_URL . '/ferramentas/biblioteca-prompts-jogos.html',
            'verticais' => ['docencia', 'ifrj_alunos']
        ],
        'guia-prompts-jogos' => [
            'id' => 'guia-prompts-jogos',
            'nome' => 'Guia de Prompts (Jogos)',
            'descricao' => 'Guia para prompts em jogos educativos',
            'icone' => '📖',
            'url' => BASE_URL . '/ferramentas/guia-prompts-jogos.html',
            'verticais' => ['docencia', 'ifrj_alunos']
        ],
        'guia-prompts-juridico' => [
            'id' => 'guia-prompts-juridico',
            'nome' => 'Guia de Prompts (Jurídico)',
            'descricao' => 'Prompts especializados para Direito',
            'icone' => '📖',
            'url' => BASE_URL . '/ferramentas/guia-prompts-juridico.html',
            'verticais' => ['juridico']
        ],
        'padroes-avancados-juridico' => [
            'id' => 'padroes-avancados-juridico',
            'nome' => 'Padrões Avançados (Jurídico)',
            'descricao' => 'Técnicas avançadas para área jurídica',
            'icone' => '⚡',
            'url' => BASE_URL . '/ferramentas/padroes-avancados-juridico.html',
            'verticais' => ['juridico']
        ],
        'repositorio-prompts' => [
            'id' => 'repositorio-prompts',
            'nome' => 'Repositório de Prompts',
            'descricao' => 'Dicionário geral de prompts',
            'icone' => '📚',
            'url' => BASE_URL . '/repositorio-prompts.php',
            'verticais' => ['docencia', 'pesquisa', 'ifrj_alunos', 'juridico']
        ]
    ];

    // Se é demo, retorna todas as ferramentas
    if ($is_demo) {
        return array_values($all_tools);
    }

    // Filtrar ferramentas pela vertical do usuário
    if (!$user_vertical) {
        return [];
    }

    $user_tools = [];
    foreach ($all_tools as $tool) {
        if (in_array($user_vertical, $tool['verticais'])) {
            $user_tools[] = $tool;
        }
    }

    return $user_tools;
}
