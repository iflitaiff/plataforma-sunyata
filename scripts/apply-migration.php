#!/usr/bin/env php
<?php
/**
 * Script para aplicar migrações do banco de dados
 * Uso: php scripts/apply-migration.php 001
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Sunyata\Core\Database;

// Verificar argumentos
if ($argc < 2) {
    echo "❌ Uso: php scripts/apply-migration.php <numero_migration> [--yes]\n";
    echo "   Exemplo: php scripts/apply-migration.php 001\n";
    echo "   Exemplo: php scripts/apply-migration.php 001 --yes (sem confirmação)\n";
    exit(1);
}

$migration_number = $argv[1];
$auto_confirm = isset($argv[2]) && ($argv[2] === '--yes' || $argv[2] === '-y');
$migration_file = __DIR__ . "/../config/migrations/{$migration_number}_*.sql";
$files = glob($migration_file);

if (empty($files)) {
    echo "❌ Erro: Migration {$migration_number} não encontrada\n";
    exit(1);
}

$migration_path = $files[0];
$migration_name = basename($migration_path, '.sql');

echo "🔍 Migration encontrada: {$migration_name}\n";
echo "📄 Arquivo: {$migration_path}\n\n";

// Ler conteúdo da migration
$sql_content = file_get_contents($migration_path);

if ($sql_content === false) {
    echo "❌ Erro ao ler arquivo de migration\n";
    exit(1);
}

// Confirmar execução
if (!$auto_confirm) {
    echo "⚠️  ATENÇÃO: Esta migration fará alterações no banco de dados!\n";
    echo "   Você tem certeza que deseja continuar? (s/N): ";

    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim(strtolower($line)) !== 's') {
        echo "❌ Migration cancelada pelo usuário\n";
        exit(0);
    }
} else {
    echo "✅ Confirmação automática (--yes)\n";
}

echo "\n🚀 Aplicando migration...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Desabilitar autocommit para fazer tudo em uma transação
    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
    $pdo->beginTransaction();

    // Separar comandos SQL (split por ;)
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            // Remover comentários e linhas vazias
            $stmt = preg_replace('/--.*$/m', '', $stmt);
            $stmt = trim($stmt);
            return !empty($stmt);
        }
    );

    $executed = 0;
    foreach ($statements as $statement) {
        // Pular se for só comentário
        if (preg_match('/^\/\*.*\*\/$/s', $statement)) {
            continue;
        }

        try {
            $pdo->exec($statement);
            $executed++;
            echo "✅ Statement executado ({$executed}/" . count($statements) . ")\n";
        } catch (PDOException $e) {
            // Alguns erros são aceitáveis (ex: coluna já existe)
            if (strpos($e->getMessage(), 'Duplicate column name') !== false ||
                strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠️  Statement pulado (já existe): " . substr($statement, 0, 50) . "...\n";
            } else {
                throw $e;
            }
        }
    }

    // Commit da transação
    $pdo->commit();

    echo "\n✅ Migration {$migration_name} aplicada com sucesso!\n";
    echo "   Total de statements executados: {$executed}\n\n";

    // Registrar migration aplicada (criar tabela se não existir)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) UNIQUE NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO migrations (migration_name)
        VALUES (:name)
    ");
    $stmt->execute(['name' => $migration_name]);

    echo "📝 Migration registrada no histórico\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Erro ao aplicar migration:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "   Linha: " . $e->getLine() . "\n";
    echo "   Arquivo: " . $e->getFile() . "\n\n";
    exit(1);
}

echo "\n🎉 Processo concluído!\n";
