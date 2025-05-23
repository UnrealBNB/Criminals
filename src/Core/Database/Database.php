<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    private ?PDO $pdo = null;
    private ?PDOStatement $statement = null;
    private int $transactionLevel = 0;

    public function __construct(
        private readonly array $config
    ) {
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    private function connect(): void
    {
        $dsn = match($this->config['driver'] ?? 'mysql') {
            'mysql' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'] ?? 3306,
                $this->config['database'],
                $this->config['charset'] ?? 'utf8mb4'
            ),
            'sqlite' => 'sqlite:' . $this->config['database'],
            default => throw new RuntimeException('Unsupported database driver'),
        };

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'] ?? null,
                $this->config['password'] ?? null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function disconnect(): void
    {
        $this->statement = null;
        $this->pdo = null;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $this->statement = $this->pdo->prepare($sql);
            $this->bindValues($params);
            $this->statement->execute();
            return $this->statement;
        } catch (PDOException $e) {
            throw new RuntimeException('Query failed: ' . $e->getMessage() . ' SQL: ' . $sql, 0, $e);
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return $this->statement->rowCount();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->query($sql, $params)->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    public function fetchPairs(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})";
        $this->execute($sql, $data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "`{$col}` = :{$col}", array_keys($data)));

        $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
        $params = array_merge($data, $whereParams);

        return $this->execute($sql, $params);
    }

    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->execute($sql, $whereParams);
    }

    public function beginTransaction(): bool
    {
        if ($this->transactionLevel === 0) {
            $result = $this->pdo->beginTransaction();
        } else {
            $this->execute("SAVEPOINT trans{$this->transactionLevel}");
            $result = true;
        }

        $this->transactionLevel++;
        return $result;
    }

    public function commit(): bool
    {
        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->commit();
        }

        return true;
    }

    public function rollBack(): bool
    {
        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->rollBack();
        }

        $this->execute("ROLLBACK TO trans{$this->transactionLevel}");
        return true;
    }

    public function inTransaction(): bool
    {
        return $this->transactionLevel > 0;
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function bindValues(array $params): void
    {
        foreach ($params as $key => $value) {
            $type = match(true) {
                is_null($value) => PDO::PARAM_NULL,
                is_bool($value) => PDO::PARAM_BOOL,
                is_int($value) => PDO::PARAM_INT,
                default => PDO::PARAM_STR,
            };

            $this->statement->bindValue(
                is_int($key) ? $key + 1 : ':' . ltrim($key, ':'),
                $value,
                $type
            );
        }
    }
}