<?php

declare(strict_types=1);

namespace App\Core\Database;

use ArrayAccess;
use JsonSerializable;
use App\Core\Container\Container;

abstract class Model implements ArrayAccess, JsonSerializable
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = ['*'];
    protected array $hidden = [];
    protected array $casts = [];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    protected bool $wasRecentlyCreated = false;

    protected static ?Database $db = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);

        if (isset($attributes[$this->primaryKey])) {
            $this->exists = true;
            $this->syncOriginal();
        }
    }

    public static function setDatabase(Database $db): void
    {
        static::$db = $db;
    }

    protected static function getDatabase(): Database
    {
        if (static::$db === null) {
            static::$db = Container::getInstance()->get(Database::class);
        }

        return static::$db;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static;
        return new QueryBuilder(static::getDatabase(), $instance->getTable());
    }

    public static function find(int|string $id): ?static
    {
        $instance = new static;
        $result = static::query()
            ->where($instance->primaryKey, $id)
            ->first();

        return $result ? new static($result) : null;
    }

    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new ModelNotFoundException(
                "Model " . static::class . " with ID {$id} not found"
            );
        }

        return $model;
    }

    public static function findMany(array $ids): array
    {
        $instance = new static;
        $results = static::query()
            ->whereIn($instance->primaryKey, $ids)
            ->get();

        return array_map(fn($row) => new static($row), $results);
    }

    public static function all(): array
    {
        $results = static::query()->get();
        return array_map(fn($row) => new static($row), $results);
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function updateOrCreate(array $attributes, array $values = []): static
    {
        $query = static::query();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        $existing = $query->first();

        if ($existing) {
            $model = new static($existing);
            $model->fill($values);
            $model->save();
        } else {
            $model = static::create(array_merge($attributes, $values));
        }

        return $model;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    public function update(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $deleted = static::getDatabase()->delete(
                $this->getTable(),
                "{$this->primaryKey} = ?",
                [$this->getKey()]
            ) > 0;

        if ($deleted) {
            $this->exists = false;
        }

        return $deleted;
    }

    public function fresh(): ?static
    {
        if (!$this->exists) {
            return null;
        }

        return static::find($this->getKey());
    }

    public function refresh(): self
    {
        if (!$this->exists) {
            return $this;
        }

        $fresh = $this->fresh();

        if ($fresh) {
            $this->attributes = $fresh->attributes;
            $this->syncOriginal();
        }

        return $this;
    }

    protected function performInsert(): bool
    {
        $attributes = $this->getAttributesForInsert();

        if (empty($attributes)) {
            return false;
        }

        $id = static::getDatabase()->insert($this->getTable(), $attributes);

        $this->setAttribute($this->primaryKey, $id);
        $this->exists = true;
        $this->wasRecentlyCreated = true;
        $this->syncOriginal();

        return true;
    }

    protected function performUpdate(): bool
    {
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return true;
        }

        $updated = static::getDatabase()->update(
                $this->getTable(),
                $dirty,
                "{$this->primaryKey} = ?",
                [$this->getKey()]
            ) > 0;

        if ($updated) {
            $this->syncOriginal();
        }

        return $updated;
    }

    public function getAttribute(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;

        if ($this->hasGetMutator($key)) {
            return $this->getMutatedAttributeValue($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        if ($this->hasSetMutator($key)) {
            $this->setMutatedAttributeValue($key, $value);
            return $this;
        }

        $this->attributes[$key] = $value;
        return $this;
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->primaryKey);
    }

    public function getTable(): string
    {
        if (empty($this->table)) {
            $class = class_basename(static::class);
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
        }

        return $this->table;
    }

    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    public function isDirty(string $key = null): bool
    {
        if ($key === null) {
            return !empty($this->getDirty());
        }

        return array_key_exists($key, $this->getDirty());
    }

    public function wasChanged(string $key = null): bool
    {
        return $this->isDirty($key);
    }

    public function getOriginal(string $key = null): mixed
    {
        if ($key === null) {
            return $this->original;
        }

        return $this->original[$key] ?? null;
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    protected function getAttributesForInsert(): array
    {
        return array_filter($this->attributes, fn($key) => !in_array($key, [$this->primaryKey]), ARRAY_FILTER_USE_KEY);
    }

    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->fillable)) {
            return true;
        }

        if ($this->guarded === ['*']) {
            return false;
        }

        return !in_array($key, $this->guarded);
    }

    protected function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
    }

    protected function hasSetMutator(string $key): bool
    {
        return method_exists($this, 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute');
    }

    protected function getMutatedAttributeValue(string $key, mixed $value): mixed
    {
        return $this->{'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute'}($value);
    }

    protected function setMutatedAttributeValue(string $key, mixed $value): void
    {
        $this->{'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute'}($value);
    }

    protected function hasCast(string $key): bool
    {
        return array_key_exists($key, $this->casts);
    }

    protected function castAttribute(string $key, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match($this->casts[$key] ?? 'string') {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            'object' => is_string($value) ? json_decode($value) : $value,
            'datetime' => new \DateTime($value),
            default => $value,
        };
    }

    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }

        return $attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }
}

class ModelNotFoundException extends \RuntimeException {}