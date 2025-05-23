<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateJobsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->text('payload');
            $table->integer('attempts')->default(0);
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at');
            $table->integer('created_at');

            $table->index(['queue', 'reserved_at']);
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('jobs');
    }
}