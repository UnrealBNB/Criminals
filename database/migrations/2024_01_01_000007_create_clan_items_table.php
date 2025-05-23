<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateClanItemsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('clan_items', function (Blueprint $table) {
            $table->integer('clan_id');
            $table->integer('item_id');
            $table->integer('item_count')->default(0);

            $table->index('clan_id');
            $table->unique(['clan_id', 'item_id']);
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('clan_items');
    }
}