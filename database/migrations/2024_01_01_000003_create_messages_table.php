<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->boolean('message_read')->default(0);
            $table->timestamp('message_time')->default('CURRENT_TIMESTAMP');
            $table->integer('message_from_id');
            $table->integer('message_to_id');
            $table->string('message_subject', 250);
            $table->text('message_message');
            $table->boolean('message_deleted_from')->default(0);
            $table->boolean('message_deleted_to')->default(0);

            $table->index('message_from_id');
            $table->index('message_to_id');
            $table->index(['message_to_id', 'message_deleted_to', 'message_read']);
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('messages');
    }
}