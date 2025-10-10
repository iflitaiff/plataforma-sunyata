<?php
/**
 * Script de Backup do Banco de Dados
 * Cria um backup SQL via PHP quando mysqldump não está disponível
 *
 * Uso: php scripts/backup-database.php
 */

$db_host = 'localhost';
$db_name = 'u202164171_sunyata';
$db_user = 'u202164171_sunyata';
$db_pass = 'MiGOq%tMrUP+9Qy@bxR';

$backup_file = __DIR__ . '/../backup_pre_verticais_' . date('Ymd_His') . '.sql';

echo "🔄 Iniciando backup do banco de dados...\n";
echo "Database: {$db_name}\n";
echo "Arquivo: " . basename($backup_file) . "\n\n";

try {
    // Tentar usar mysqldump via shell_exec primeiro
    $mysqldump_path = trim(shell_exec('which mysqldump 2>/dev/null'));

    if (!empty($mysqldump_path) && file_exists($mysqldump_path)) {
        echo "✓ Usando mysqldump...\n";
        $command = sprintf(
            '%s -h %s -u %s -p%s %s > %s 2>&1',
            escapeshellcmd($mysqldump_path),
            escapeshellarg($db_host),
            escapeshellarg($db_user),
            escapeshellarg($db_pass),
            escapeshellarg($db_name),
            escapeshellarg($backup_file)
        );

        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($backup_file) && filesize($backup_file) > 0) {
            $size = filesize($backup_file);
            echo "✅ Backup criado com sucesso!\n";
            echo "Tamanho: " . number_format($size / 1024, 2) . " KB\n";
            echo "Local: {$backup_file}\n";
            exit(0);
        }
    }

    // Se mysqldump falhou ou não existe, usar método PHP puro
    echo "ℹ️ mysqldump não disponível, usando método PHP...\n\n";

    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $backup_content = "-- Backup Database: {$db_name}\n";
    $backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- Host: {$db_host}\n\n";
    $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $backup_content .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $backup_content .= "SET time_zone = '+00:00';\n\n";

    // Obter todas as tabelas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "📋 Tabelas encontradas: " . count($tables) . "\n\n";

    foreach ($tables as $table) {
        echo "  - Exportando: {$table}...";

        // Drop table
        $backup_content .= "-- Table: {$table}\n";
        $backup_content .= "DROP TABLE IF EXISTS `{$table}`;\n";

        // Create table
        $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $backup_content .= $create['Create Table'] . ";\n\n";

        // Insert data
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $values = array_map(function($val) use ($pdo) {
                    return is_null($val) ? 'NULL' : $pdo->quote($val);
                }, array_values($row));

                $columns = '`' . implode('`, `', array_keys($row)) . '`';
                $backup_content .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
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
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
