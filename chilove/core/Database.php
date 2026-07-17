<?php

namespace ChiLove\Core;

use PDO;

/**
 * Minimal $wpdb-style database layer over PDO / MySQL.
 * Connects lazily on first query, so the app still boots if the DB is down.
 */
final class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    /** WordPress-style table prefix. */
    public string $prefix = 'chi_';

    private function __construct(private array $cfg) {}

    public static function boot(array $cfg): self
    {
        return self::$instance ??= new self($cfg);
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Database not booted — call Database::boot() first.');
        }
        return self::$instance;
    }

    private function pdo(): PDO
    {
        if ($this->pdo === null) {
            $c = $this->cfg;
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $c['host'], $c['port'], $c['name'], $c['charset']
            );
            $this->pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return $this->pdo;
    }

    public function isConnected(): bool
    {
        try {
            $this->pdo()->query('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return object[] */
    public function getResults(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRow(string $sql, array $params = []): ?object
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getVar(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function query(string $sql, array $params = []): int
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo()->lastInsertId();
    }
}
