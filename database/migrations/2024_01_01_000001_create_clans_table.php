<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateClansTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('clans', function (Blueprint $table) {
            $table->id('clan_id');
            $table->string('clan_name', 200);
            $table->integer('clan_owner_id')->unsigned();
            $table->integer('clan_type');
            $table->integer('clan_clicks')->default(0);
            $table->integer('attack_power')->default(0);
            $table->integer('defence_power')->default(0);
            $table->integer('cash')->default(0);
            $table->integer('bank')->default(0);
            $table->integer('bankleft')->default(10);
            $table->integer('clicks_today')->default(0);

            $table->unique('clan_name');
            $table->index('clan_owner_id');
            $table->index('clan_name');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('clans');
    }
}