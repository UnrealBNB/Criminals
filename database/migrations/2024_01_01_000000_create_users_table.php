<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 200);
            $table->string('password', 250);
            $table->string('email', 100);
            $table->integer('type')->default(1);
            $table->integer('level')->default(0);
            $table->string('session_id', 100)->nullable();
            $table->boolean('activated')->default(0);
            $table->timestamp('signup_date')->default('CURRENT_TIMESTAMP');
            $table->string('website', 200)->nullable();
            $table->text('info')->nullable();
            $table->timestamp('online_time')->nullable();
            $table->integer('attack_power')->default(0);
            $table->integer('defence_power')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('clicks_today')->default(0);
            $table->integer('bank')->default(0);
            $table->integer('cash')->default(0);
            $table->boolean('showonline')->default(1);
            $table->boolean('protection')->default(1);
            $table->integer('hlround')->default(1);
            $table->integer('clan_id')->default(0);
            $table->integer('clan_level')->default(0);
            $table->integer('attacks_won')->default(0);
            $table->integer('attacks_lost')->default(0);
            $table->integer('bank_left')->default(5);
            $table->integer('country_id')->default(1);

            $table->unique('username');
            $table->unique('email');
            $table->index('username');
            $table->index('clan_id');
            $table->index('type');
            $table->index('online_time');
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
}