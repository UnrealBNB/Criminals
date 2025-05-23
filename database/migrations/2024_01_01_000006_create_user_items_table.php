<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateUserItemsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('user_items', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('item_id');
            $table->integer('item_count')->default(0);

            $table->index('user_id');
            $table->unique(['user_id', 'item_id']);
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('user_items');
    }
}