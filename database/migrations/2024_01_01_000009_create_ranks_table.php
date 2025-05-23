<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateRanksTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('ranks', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name', 40);
            $table->integer('power_low');
            $table->integer('power_high');

            $table->index('id');
        });

        // Insert default ranks
        $this->insertDefaultRanks();
    }

    public function down(): void
    {
        $this->schema->dropIfExists('ranks');
    }

    private function insertDefaultRanks(): void
    {
        $ranks = [
            [1, 'Zwerver', 0, 100],
            [2, 'Bedelaar', 100, 700],
            [3, 'Crimineel', 700, 1300],
            [4, 'Zakkenroller', 1300, 2000],
            [5, 'Tuig', 2000, 2800],
            [6, 'Geweldadig', 2800, 3700],
            [7, 'Autodief', 3700, 4700],
            [8, 'Drugsdealer', 4700, 5800],
            [9, 'Gangster', 5800, 7000],
            [10, 'Overvaller', 7000, 8800],
            [11, 'Bendeleider', 8800, 12000],
            [12, 'Godfather', 12000, 999999999],
        ];

        foreach ($ranks as $rank) {
            $this->db->insert('ranks', [
                'id' => $rank[0],
                'name' => $rank[1],
                'power_low' => $rank[2],
                'power_high' => $rank[3],
            ]);
        }
    }
}