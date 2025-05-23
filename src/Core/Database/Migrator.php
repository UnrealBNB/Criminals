<?php

declare(strict_types=1);

namespace App\Core\Database;

use RuntimeException;

class Migrator
{
    private array $migrations = [];
    private string $migrationPath;
    private string $migrationTable = 'migrations';

    public function __construct(
        private readonly Database $db
    ) {
        $this->migrationPath = app()->basePath('database/migrations');
        $this->createMigrationTableIfNotExists();
    }

    public function run(): void
    {
        $files = $this->getMigrationFiles();
        $ran = $this->getRanMigrations();

        $migrations = array_diff($files, $ran);

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
    }

    public function rollback(int $steps = 1): void
    {
        $migrations = $this->getRanMigrations();
        $migrations = array_slice($migrations, -$steps);

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration);
        }
    }

    public function fresh(): void
    {
        $this->dropAllTables();
        $this->run();
    }

    private function createMigrationTableIfNotExists(): void
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationPath . '/*.php');
        return array_map(fn($file) => basename($file, '.php'), $files);
    }

    private function getRanMigrations(): array
    {
        return $this->db->fetchAll("SELECT migration FROM {$this->migrationTable}")
            ?: [];
    }

    private function runMigration(string $migration): void
    {
        $instance = $this->resolve($migration);

        $this->db->beginTransaction();

        try {
            $instance->up();

            $batch = $this->getNextBatchNumber();

            $this->db->insert($this->migrationTable, [
                'migration' => $migration,
                'batch' => $batch,
            ]);

            $this->db->commit();

            echo "Migrated: {$migration}\n";
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException("Migration failed: {$migration} - " . $e->getMessage(), 0, $e);
        }
    }

    private function rollbackMigration(string $migration): void
    {
        $instance = $this->resolve($migration);

        $this->db->beginTransaction();

        try {
            $instance->down();

            $this->db->delete(
                $this->migrationTable,
                'migration = :migration',
                ['migration' => $migration]
            );

            $this->db->commit();

            echo "Rolled back: {$migration}\n";
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new RuntimeException("Rollback failed: {$migration} - " . $e->getMessage(), 0, $e);
        }
    }

    private function resolve(string $migration): Migration
    {
        $class = $this->getMigrationClass($migration);

        if (!class_exists($class)) {
            require_once $this->migrationPath . '/' . $migration . '.php';
        }

        return new $class($this->db);
    }

    private function getMigrationClass(string $migration): string
    {
        $parts = explode('_', $migration);
        $parts = array_slice($parts, 4); // Remove timestamp

        return 'Database\\Migrations\\' . str_replace(' ', '', ucwords(implode(' ', $parts)));
    }

    private function getNextBatchNumber(): int
    {
        $batch = $this->db->fetchColumn("SELECT MAX(batch) FROM {$this->migrationTable}");
        return ($batch ?? 0) + 1;
    }

    private function dropAllTables(): void
    {
        $tables = $this->db->fetchAll("SHOW TABLES");

        $this->db->execute("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $this->db->execute("DROP TABLE IF EXISTS `{$tableName}`");
        }

        $this->db->execute("SET FOREIGN_KEY_CHECKS = 1");
    }
}