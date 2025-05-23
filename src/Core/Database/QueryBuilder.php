<?php

declare(strict_types=1);

namespace App\Core\Database;

use Closure;

class QueryBuilder
{
    private array $select = ['*'];
    private ?string $from = null;
    private array $joins = [];
    private array $wheres = [];
    private array $groups = [];
    private array $havings = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $bindings = [
        'select' => [],
        'join' => [],
        'where' => [],
        'having' => [],
    ];

    public function __construct(
        private readonly Database $db,
        private readonly ?string $table = null
    ) {
        if ($this->table !== null) {
            $this->from($this->table);
        }
    }

    public function select(string|array $columns = ['*']): self
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function addSelect(string|array $columns): self
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->select = array_merge($this->select, $columns);
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $alias ? "{$table} AS {$alias}" : $table;
        return $this;
    }

    public function join(string $table, string|Closure $first, ?string $operator = null, ?string $second = null, string $type = 'INNER'): self
    {
        if ($first instanceof Closure) {
            $join = new JoinClause($type, $table);
            $first($join);
            $this->joins[] = $join;
            $this->bindings['join'] = array_merge($this->bindings['join'], $join->getBindings());
        } else {
            $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        }

        return $this;
    }

    public function leftJoin(string $table, string|Closure $first, ?string $operator = null, ?string $second = null): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string|Closure $first, ?string $operator = null, ?string $second = null): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function where(string|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): self
    {
        if ($column instanceof Closure) {
            $query = new static($this->db);
            $column($query);
            $this->wheres[] = [
                'type' => 'nested',
                'query' => $query,
                'boolean' => $boolean,
            ];
            $this->bindings['where'] = array_merge($this->bindings['where'], $query->getBindings()['where']);
            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');

        if ($value !== null) {
            $this->bindings['where'][] = $value;
        }

        return $this;
    }

    public function orWhere(string|Closure $column, mixed $operator = null, mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        $this->bindings['where'] = array_merge($this->bindings['where'], $values);

        return $this;
    }

    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean,
        ];

        return $this;
    }

    public function whereBetween(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        $this->bindings['where'] = array_merge($this->bindings['where'], $values);

        return $this;
    }

    public function groupBy(string|array $columns): self
    {
        $this->groups = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = compact('column', 'operator', 'value', 'boolean');

        if ($value !== null) {
            $this->bindings['having'][] = $value;
        }

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = "{$column} {$direction}";
        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        return $this->db->fetchAll($this->toSql(), $this->getBindings());
    }

    public function first(): ?array
    {
        return $this->limit(1)->db->fetchOne($this->toSql(), $this->getBindings());
    }

    public function count(string $column = '*'): int
    {
        $result = $this->select("COUNT({$column}) AS aggregate")->first();
        return (int) ($result['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select);

        if ($this->from) {
            $sql .= ' FROM ' . $this->from;
        }

        foreach ($this->joins as $join) {
            $sql .= ' ' . $join;
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    public function getBindings(): array
    {
        return array_merge(
            $this->bindings['select'],
            $this->bindings['join'],
            $this->bindings['where'],
            $this->bindings['having']
        );
    }

    private function compileWheres(): string
    {
        $sql = [];

        foreach ($this->wheres as $i => $where) {
            $method = match($where['type'] ?? 'basic') {
                'nested' => 'compileNestedWhere',
                'in' => 'compileInWhere',
                'null' => 'compileNullWhere',
                'between' => 'compileBetweenWhere',
                default => 'compileBasicWhere',
            };

            $sql[] = ($i === 0 ? '' : $where['boolean'] . ' ') . $this->{$method}($where);
        }

        return implode(' ', $sql);
    }

    private function compileBasicWhere(array $where): string
    {
        return "{$where['column']} {$where['operator']} ?";
    }

    private function compileNestedWhere(array $where): string
    {
        return '(' . substr($where['query']->compileWheres(), 0) . ')';
    }

    private function compileInWhere(array $where): string
    {
        $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
        return "{$where['column']} IN ({$placeholders})";
    }

    private function compileNullWhere(array $where): string
    {
        return "{$where['column']} IS NULL";
    }

    private function compileBetweenWhere(array $where): string
    {
        return "{$where['column']} BETWEEN ? AND ?";
    }

    private function compileHavings(): string
    {
        $sql = [];

        foreach ($this->havings as $i => $having) {
            $sql[] = ($i === 0 ? '' : $having['boolean'] . ' ') .
                "{$having['column']} {$having['operator']} ?";
        }

        return implode(' ', $sql);
    }
}

class JoinClause
{
    private array $clauses = [];
    private array $bindings = [];

    public function __construct(
        private readonly string $type,
        private readonly string $table
    ) {}

    public function on(string $first, string $operator, string $second): self
    {
        $this->clauses[] = "{$first} {$operator} {$second}";
        return $this;
    }

    public function orOn(string $first, string $operator, string $second): self
    {
        $this->clauses[] = "OR {$first} {$operator} {$second}";
        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function __toString(): string
    {
        return "{$this->type} JOIN {$this->table} ON " . implode(' ', $this->clauses);
    }
}