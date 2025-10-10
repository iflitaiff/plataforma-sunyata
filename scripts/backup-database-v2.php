<?php
/**
 * Script de Backup do Banco de Dados (v2)
 * Usa a classe Database do projeto
 *
 * Uso: php scripts/backup-database-v2.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use Sunyata\Core\Database;

$backup_file = __DIR__ . '/../backup_pre_verticais_' . date('Ymd_His') . '.sql';

echo "🔄 Iniciando backup do banco de dados...\n";
echo "Database: u202164171_sunyata\n";
echo "Arquivo: " . basename($backup_file) . "\n\n";

try {
    $db = Database::getInstance();

    $backup_content = "-- Backup Database: u202164171_sunyata\n";
    $backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $backup_content .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $backup_content .= "SET time_zone = '+00:00';\n\n";

    // Obter todas as tabelas
    $tables = $db->queryAll("SHOW TABLES");

    if (empty($tables)) {
        throw new Exception("Nenhuma tabela encontrada no banco");
    }

    $table_names = array_map(function($row) {
        return reset($row);
    }, $tables);

    echo "📋 Tabelas encontradas: " . count($table_names) . "\n\n";

    foreach ($table_names as $table) {
        echo "  - Exportando: {$table}...";

        // Drop table
        $backup_content .= "-- Table: {$table}\n";
        $backup_content .= "DROP TABLE IF EXISTS `{$table}`;\n";

        // Create table
        $create = $db->query("SHOW CREATE TABLE `{$table}`");
        $backup_content .= $create['Create Table'] . ";\n\n";

        // Insert data
        $rows = $db->queryAll("SELECT * FROM `{$table}`");

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_map(function($val) {
                    if (is_null($val)) {
                        return 'NULL';
                    }
                    return "'" . addslashes($val) . "'";
                }, array_values($row));

                $cols = '`' . implode('`, `', $columns) . '`';
                $vals = implode(', ', $values);
                $backup_content .= "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n";
            }
            echo " " . count($rows) . " registros\n";
        } else {
            echo " (vazia)\n";
        }

        $backup_content .= "\n";
    }

    $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // Salvar arquivo
    file_put_contents($backup_file, $backup_content);

    $size = filesize($backup_file);
    echo "\n✅ Backup criado com sucesso!\n";
    echo "Tamanho: " . number_format($size / 1024, 2) . " KB\n";
    echo "Local: {$backup_file}\n";

} catch (Exception $e) {
    echo "\n❌ ERRO ao criar backup: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getLine')) {
        echo "Linha: " . $e->getLine() . "\n";
        echo "Arquivo: " . $e->getFile() . "\n";
    }
    exit(1);
}
