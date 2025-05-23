<?php

declare(strict_types=1);

namespace App\Core\Database;

abstract class Migration
{
    protected Database $db;
    protected Schema $schema;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->schema = new Schema($db);
    }

    abstract public function up(): void;
    abstract public function down(): void;

    public function getName(): string
    {
        return static::class;
    }
}