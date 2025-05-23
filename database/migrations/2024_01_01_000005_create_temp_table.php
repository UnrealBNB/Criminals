<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateTempTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('temp', function (Blueprint $table) {
            $table->integer('userid')->nullable();
            $table->string('area', 200)->nullable();
            $table->string('variable', 200)->nullable();
            $table->string('extra', 200)->nullable();
            $table->timestamp('created_at')->default('CURRENT_TIMESTAMP');

            $table->index('userid');
            $table->index('area');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('temp');
    }
}