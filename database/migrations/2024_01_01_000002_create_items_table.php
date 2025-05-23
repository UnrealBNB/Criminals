<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateItemsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_name', 200);
            $table->integer('item_attack_power')->default(0);
            $table->integer('item_defence_power')->default(0);
            $table->integer('item_area');
            $table->integer('item_costs');
            $table->integer('item_sell');

            $table->index('item_area');
        });

        // Insert default items
        $this->insertDefaultItems();
    }

    public function down(): void
    {
        $this->schema->dropIfExists('items');
    }

    private function insertDefaultItems(): void
    {
        $items = [
            ['Mes', 20, 20, 1, 2000, 1500],
            ['Walter P99', 50, 50, 1, 5000, 3000],
            ['Uzi', 65, 65, 1, 6000, 4000],
            ['Flashbang', 110, 110, 1, 10000, 7500],
            ['Granaat', 170, 170, 1, 15000, 10000],
            ['MP5k', 80, 80, 1, 7500, 5000],
            ['Shotgun', 200, 200, 1, 17500, 10000],
            ['G36C', 270, 270, 1, 22500, 15000],
            ['SIG 552', 310, 310, 1, 25000, 20000],
            ['Ak47', 390, 390, 1, 30000, 20000],
            ['Ak Beta', 570, 570, 1, 40000, 20000],
            ['Scherpschut geweer', 670, 670, 1, 45000, 25000],
            ['M4', 780, 780, 1, 50000, 35000],
            ['Granaat Lanceerder', 1030, 1030, 1, 60000, 40000],
            ['Bazooka', 1490, 1490, 1, 75000, 50000],
            ['Kogelvrij vest', 140, 140, 2, 12500, 8000],
            ['Bulldog', 0, 30, 3, 2500, 1500],
            ['Camera', 0, 90, 3, 8000, 4000],
            ['Hek', 0, 170, 3, 15000, 10000],
            ['Muur', 0, 240, 3, 20000, 15000],
            ['Bunker', 0, 470, 3, 35000, 20000],
            ['Mobieltje', 0, 0, 4, 1000, 500],
            ['FN P90', 900, 900, 5, 50000, 20000],
            ['Chip', 400, 400, 6, 25000, 15000],
            ['Helm', 240, 240, 7, 20000, 10000],
            ['Politie wagen', 470, 470, 7, 35000, 15000],
            ['Huis', 0, 0, 8, 25000, 15000],
            ['Muur', 0, 3000, 8, 50000, 20000],
            ['Coffeeshop', 0, 0, 9, 90000, 30000],
            ['Chemie Lab', 0, 0, 10, 90000, 30000],
            ['Aandeel', 0, 0, 11, 90000, 30000],
        ];

        foreach ($items as $item) {
            $this->db->insert('items', [
                'item_name' => $item[0],
                'item_attack_power' => $item[1],
                'item_defence_power' => $item[2],
                'item_area' => $item[3],
                'item_costs' => $item[4],
                'item_sell' => $item[5],
            ]);
        }
    }
}