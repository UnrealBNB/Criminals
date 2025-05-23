<?php

declare(strict_types=1);

namespace App\Core\Database;

class Schema
{
    public function __construct(
        private readonly Database $db
    ) {}

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $this->buildCreateStatement($blueprint);
        $this->db->execute($sql);

        // Add indexes
        foreach ($blueprint->getIndexes() as $index) {
            $this->db->execute($index);
        }
    }

    public function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, false);
        $callback($blueprint);

        foreach ($blueprint->getAlterations() as $alteration) {
            $this->db->execute($alteration);
        }
    }

    public function drop(string $table): void
    {
        $this->db->execute("DROP TABLE IF EXISTS `{$table}`");
    }

    public function dropIfExists(string $table): void
    {
        $this->drop($table);
    }

    public function hasTable(string $table): bool
    {
        $result = $this->db->fetchOne(
            "SHOW TABLES LIKE :table",
            ['table' => $table]
        );

        return $result !== null;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $result = $this->db->fetchOne(
            "SHOW COLUMNS FROM `{$table}` WHERE Field = :column",
            ['column' => $column]
        );

        return $result !== null;
    }

    private function buildCreateStatement(Blueprint $blueprint): string
    {
        $columns = [];

        foreach ($blueprint->getColumns() as $column) {
            $columns[] = $this->buildColumnDefinition($column);
        }

        if ($primary = $blueprint->getPrimaryKey()) {
            $columns[] = "PRIMARY KEY (`{$primary}`)";
        }

        foreach ($blueprint->getForeignKeys() as $foreign) {
            $columns[] = $this->buildForeignKeyDefinition($foreign);
        }

        $sql = "CREATE TABLE `{$blueprint->getTable()}` (\n  ";
        $sql .= implode(",\n  ", $columns);
        $sql .= "\n)";

        if ($engine = $blueprint->getEngine()) {
            $sql .= " ENGINE={$engine}";
        }

        if ($charset = $blueprint->getCharset()) {
            $sql .= " DEFAULT CHARSET={$charset}";
        }

        if ($collation = $blueprint->getCollation()) {
            $sql .= " COLLATE={$collation}";
        }

        return $sql;
    }

    private function buildColumnDefinition(array $column): string
    {
        $sql = "`{$column['name']}` {$column['type']}";

        if (isset($column['unsigned']) && $column['unsigned']) {
            $sql .= ' UNSIGNED';
        }

        if (isset($column['nullable']) && $column['nullable']) {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }

        if (isset($column['default'])) {
            if ($column['default'] === 'CURRENT_TIMESTAMP') {
                $sql .= ' DEFAULT CURRENT_TIMESTAMP';
            } else {
                $sql .= " DEFAULT '{$column['default']}'";
            }
        }

        if (isset($column['autoIncrement']) && $column['autoIncrement']) {
            $sql .= ' AUTO_INCREMENT';
        }

        return $sql;
    }

    private function buildForeignKeyDefinition(array $foreign): string
    {
        $sql = "CONSTRAINT `{$foreign['name']}` FOREIGN KEY (`{$foreign['column']}`)";
        $sql .= " REFERENCES `{$foreign['references']}` (`{$foreign['on']}`)";

        if (isset($foreign['onDelete'])) {
            $sql .= " ON DELETE {$foreign['onDelete']}";
        }

        if (isset($foreign['onUpdate'])) {
            $sql .= " ON UPDATE {$foreign['onUpdate']}";
        }

        return $sql;
    }
}