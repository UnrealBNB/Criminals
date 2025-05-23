<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateFailedJobsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->text('payload');
            $table->text('exception');
            $table->integer('failed_at');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('failed_jobs');
    }
}