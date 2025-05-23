<?php

declare(strict_types=1);

namespace Database\Migrations;

use App\Core\Database\Blueprint;
use App\Core\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $this->schema->create('settings', function (Blueprint $table) {
            $table->integer('setting_id')->primary();
            $table->string('setting_name', 200);
            $table->text('setting_value');
            $table->string('setting_extra', 200)->nullable();

            $table->unique('setting_name');
        });

        // Insert default settings
        $this->insertDefaultSettings();
    }

    public function down(): void
    {
        $this->schema->dropIfExists('settings');
    }

    private function insertDefaultSettings(): void
    {
        $this->db->insert('settings', [
            'setting_id' => 1,
            'setting_name' => 'rules',
            'setting_value' => 'Default game rules...',
            'setting_extra' => '',
        ]);

        $this->db->insert('settings', [
            'setting_id' => 2,
            'setting_name' => 'price',
            'setting_value' => 'Default price information...',
            'setting_extra' => '',
        ]);

        $this->db->insert('settings', [
            'setting_id' => 3,
            'setting_name' => 'layout',
            'setting_value' => 'begangster',
            'setting_extra' => '',
        ]);
    }
}