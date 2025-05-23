<?php

declare(strict_types=1);

namespace App\Core\Database;

class Blueprint
{
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private array $alterations = [];
    private ?string $primaryKey = null;
    private ?string $engine = 'InnoDB';
    private ?string $charset = 'utf8mb4';
    private ?string $collation = 'utf8mb4_unicode_ci';

    public function __construct(
        private readonly string $table,
        private readonly bool $creating = true
    ) {}

    // Column types
    public function id(string $column = 'id'): self
    {
        $this->integer($column, true, true);
        $this->primaryKey = $column;
        return $this;
    }

    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false): self
    {
        $this->addColumn($column, 'INT(11)', [
            'autoIncrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
        return $this;
    }

    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): self
    {
        $this->addColumn($column, 'BIGINT(20)', [
            'autoIncrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
        return $this;
    }

    public function string(string $column, int $length = 255): self
    {
        $this->addColumn($column, "VARCHAR({$length})");
        return $this;
    }

    public function text(string $column): self
    {
        $this->addColumn($column, 'TEXT');
        return $this;
    }

    public function boolean(string $column): self
    {
        $this->addColumn($column, 'TINYINT(1)', ['default' => 0]);
        return $this;
    }

    public function timestamp(string $column): self
    {
        $this->addColumn($column, 'TIMESTAMP');
        return $this;
    }

    public function timestamps(): self
    {
        $this->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable();
        return $this;
    }

    // Modifiers
    public function nullable(): self
    {
        $lastColumn = &$this->columns[count($this->columns) - 1];
        $lastColumn['nullable'] = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $lastColumn = &$this->columns[count($this->columns) - 1];
        $lastColumn['default'] = $value;
        return $this;
    }

    public function unsigned(): self
    {
        $lastColumn = &$this->columns[count($this->columns) - 1];
        $lastColumn['unsigned'] = true;
        return $this;
    }

    // Indexes
    public function primary(string|array $columns): self
    {
        if (is_string($columns)) {
            $this->primaryKey = $columns;
        } else {
            $this->primaryKey = implode('_', $columns);
        }
        return $this;
    }

    public function index(string|array $columns, ?string $name = null): self
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        $name = $name ?: $this->table . '_' . implode('_', $columns) . '_index';

        $this->indexes[] = "CREATE INDEX `{$name}` ON `{$this->table}` (`" . implode('`, `', $columns) . "`)";

        return $this;
    }

    public function unique(string|array $columns, ?string $name = null): self
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        $name = $name ?: $this->table . '_' . implode('_', $columns) . '_unique';

        $this->indexes[] = "CREATE UNIQUE INDEX `{$name}` ON `{$this->table}` (`" . implode('`, `', $columns) . "`)";

        return $this;
    }

    public function foreign(string $column): ForeignKeyDefinition
    {
        $foreign = new ForeignKeyDefinition($this, $column);
        $this->foreignKeys[] = $foreign;
        return $foreign;
    }

    // Alterations
    public function addColumn(string $name, string $type, array $options = []): self
    {
        if ($this->creating) {
            $this->columns[] = array_merge(['name' => $name, 'type' => $type], $options);
        } else {
            $definition = $this->buildColumnDefinition(['name' => $name, 'type' => $type] + $options);
            $this->alterations[] = "ALTER TABLE `{$this->table}` ADD COLUMN {$definition}";
        }
        return $this;
    }

    public function dropColumn(string $column): self
    {
        $this->alterations[] = "ALTER TABLE `{$this->table}` DROP COLUMN `{$column}`";
        return $this;
    }

    // Getters
    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    public function getAlterations(): array
    {
        return $this->alterations;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function getCollation(): ?string
    {
        return $this->collation;
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
}

class ForeignKeyDefinition
{
    private array $attributes = [];

    public function __construct(
        private readonly Blueprint $blueprint,
        string $column
    ) {
        $this->attributes['column'] = $column;
        $this->attributes['name'] = 'fk_' . $blueprint->getTable() . '_' . $column;
    }

    public function references(string $column): self
    {
        $this->attributes['on'] = $column;
        return $this;
    }

    public function on(string $table): self
    {
        $this->attributes['references'] = $table;
        return $this;
    }

    public function onDelete(string $action): self
    {
        $this->attributes['onDelete'] = $action;
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $this->attributes['onUpdate'] = $action;
        return $this;
    }

    public function __destruct()
    {
        // This is automatically added to blueprint when object is destroyed
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}