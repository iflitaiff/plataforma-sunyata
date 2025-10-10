<?php
/**
 * Database Connection Handler
 *
 * @package Sunyata\Core
 */

namespace Sunyata\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_CASE => PDO::CASE_LOWER,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection error');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new \Exception('Database query error');
        }
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $this->query($sql, $data);

        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $sql = "UPDATE $table SET $set WHERE $where";

        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);

        return $stmt->rowCount();
    }

    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $whereParams);

        return $stmt->rowCount();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollBack();
    }
}
