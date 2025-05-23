<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateClicksTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('clicks', function (Blueprint $table) {
            $table->integer('userid');
            $table->string('clicked_ip', 50);

            $table->index(['userid', 'clicked_ip']);
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('clicks');
    }
}