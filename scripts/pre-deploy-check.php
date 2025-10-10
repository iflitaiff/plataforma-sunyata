<?php
/**
 * Script de Verificação Pré-Deploy
 * Verifica se todos os arquivos necessários existem e estão corretos
 *
 * Uso: php scripts/pre-deploy-check.php
 */

echo "🔍 Verificação Pré-Deploy - Sistema de Verticais\n";
echo str_repeat("=", 60) . "\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar arquivos de onboarding
echo "📋 Verificando arquivos de onboarding...\n";
$onboarding_files = [
    'public/onboarding-step1.php',
    'public/onboarding-step2.php',
    'public/onboarding-save-vertical.php',
    'public/onboarding-ifrj.php',
    'public/onboarding-juridico.php'
];

foreach ($onboarding_files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $success[] = "✓ $file existe";
        // Verificar sintaxe
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return_var);
        if ($return_var !== 0) {
            $errors[] = "✗ $file tem erro de sintaxe: " . implode("\n", $output);
        }
    } else {
        $errors[] = "✗ $file NÃO ENCONTRADO";
    }
}

// 2. Verificar estrutura de verticais
echo "\n📁 Verificando estrutura de verticais...\n";
$verticals = ['docencia', 'pesquisa', 'ifrj-alunos', 'juridico', 'vendas', 'marketing', 'licitacoes', 'rh', 'geral'];

foreach ($verticals as $vertical) {
    $index_path = __DIR__ . "/../public/areas/{$vertical}/index.php";
    if (file_exists($index_path)) {
        $success[] = "✓ Vertical {$vertical} OK";
    } else {
        $errors[] = "✗ Vertical {$vertical} - index.php não encontrado";
    }
}

// 3. Verificar ferramentas HTML
echo "\n🛠️ Verificando ferramentas HTML...\n";
$expected_tools = [
    'canvas-docente.html',
    'canvas-pesquisa.html',
    'canvas-juridico.html',
    'biblioteca-prompts-jogos.html',
    'guia-prompts-jogos.html',
    'guia-prompts-juridico.html',
    'padroes-avancados-juridico.html'
];

foreach ($expected_tools as $tool) {
    $path = __DIR__ . "/../public/ferramentas/{$tool}";
    if (file_exists($path)) {
        $success[] = "✓ Ferramenta {$tool} existe";
    } else {
        $warnings[] = "⚠ Ferramenta {$tool} NÃO ENCONTRADA (gateway vai retornar 404)";
    }
}

// 4. Verificar gateways de ferramentas
echo "\n🚪 Verificando gateways...\n";
$gateway_checks = [
    'docencia' => ['canvas-docente', 'canvas-pesquisa', 'biblioteca-prompts-jogos', 'guia-prompts-jogos', 'repositorio-prompts'],
    'pesquisa' => ['canvas-docente', 'canvas-pesquisa', 'repositorio-prompts'],
    'ifrj-alunos' => ['biblioteca-prompts-jogos', 'guia-prompts-jogos', 'canvas-pesquisa', 'repositorio-prompts'],
    'juridico' => ['canvas-juridico', 'guia-prompts-juridico', 'padroes-avancados-juridico', 'repositorio-prompts']
];

$gateway_count = 0;
foreach ($gateway_checks as $vertical => $tools) {
    foreach ($tools as $tool) {
        $path = __DIR__ . "/../public/areas/{$vertical}/{$tool}.php";
        if (file_exists($path)) {
            $gateway_count++;
        } else {
            $errors[] = "✗ Gateway {$vertical}/{$tool}.php NÃO ENCONTRADO";
        }
    }
}
$success[] = "✓ {$gateway_count} gateways encontrados";

// 5. Verificar migrations
echo "\n💾 Verificando migrations...\n";
$migration_file = __DIR__ . '/../config/migrations/001_vertical_system.sql';
if (file_exists($migration_file)) {
    $success[] = "✓ Migration SQL existe";

    // Verificar se contém as palavras-chave esperadas
    $content = file_get_contents($migration_file);
    $keywords = ['user_profiles', 'vertical_access_requests', 'tool_access_logs', 'completed_onboarding'];
    foreach ($keywords as $keyword) {
        if (strpos($content, $keyword) === false) {
            $warnings[] = "⚠ Migration não contém '{$keyword}'";
        }
    }
} else {
    $errors[] = "✗ Migration SQL NÃO ENCONTRADA";
}

// 6. Verificar arquivos modificados
echo "\n✏️ Verificando arquivos modificados...\n";
$modified_files = [
    'config/auth.php' => ['has_vertical_access', 'has_tool_access', 'get_user_tools'],
    'public/callback.php' => ['completed_onboarding'],
    'public/dashboard.php' => ['get_user_tools']
];

foreach ($modified_files as $file => $required_strings) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $all_found = true;
        foreach ($required_strings as $string) {
            if (strpos($content, $string) === false) {
                $errors[] = "✗ {$file} não contém '{$string}'";
                $all_found = false;
            }
        }
        if ($all_found) {
            $success[] = "✓ {$file} atualizado corretamente";
        }
    } else {
        $errors[] = "✗ {$file} NÃO ENCONTRADO";
    }
}

// 7. Verificar admin analytics
echo "\n📊 Verificando admin analytics...\n";
$analytics_path = __DIR__ . '/../public/admin/analytics.php';
if (file_exists($analytics_path)) {
    $success[] = "✓ Admin analytics existe";
} else {
    $errors[] = "✗ Admin analytics NÃO ENCONTRADO";
}

// 8. Verificar permissões
echo "\n🔒 Verificando permissões...\n";
$check_permissions = [
    'public/areas' => 0755,
    'public/ferramentas' => 0755,
    'config/migrations' => 0755
];

foreach ($check_permissions as $dir => $expected_perm) {
    $path = __DIR__ . '/../' . $dir;
    if (file_exists($path)) {
        $actual_perm = fileperms($path) & 0777;
        if ($actual_perm >= $expected_perm) {
            $success[] = "✓ {$dir} permissões OK (" . decoct($actual_perm) . ")";
        } else {
            $warnings[] = "⚠ {$dir} permissões podem ser insuficientes (" . decoct($actual_perm) . ")";
        }
    }
}

// Relatório Final
echo "\n" . str_repeat("=", 60) . "\n";
echo "📝 RELATÓRIO FINAL\n";
echo str_repeat("=", 60) . "\n\n";

if (!empty($errors)) {
    echo "❌ ERROS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVISOS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   {$warning}\n";
    }
    echo "\n";
}

echo "✅ SUCESSOS (" . count($success) . "):\n";
foreach (array_slice($success, 0, 5) as $item) {
    echo "   {$item}\n";
}
if (count($success) > 5) {
    echo "   ... e mais " . (count($success) - 5) . " itens OK\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

if (empty($errors)) {
    echo "✅ SISTEMA PRONTO PARA DEPLOY!\n";
    echo "\nPróximos passos:\n";
    echo "1. Fazer backup do banco: mysqldump -u user -p db > backup.sql\n";
    echo "2. Aplicar migration: php scripts/apply-migration.php\n";
    echo "3. Testar funcionalidades críticas\n";
    exit(0);
} else {
    echo "❌ CORRIJA OS ERROS ANTES DE FAZER DEPLOY\n";
    exit(1);
}
