<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE landing_settings MODIFY hero_image_url LONGTEXT NULL');
            DB::statement('ALTER TABLE landing_settings MODIFY feature_image_url LONGTEXT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE landing_settings MODIFY hero_image_url VARCHAR(2000) NULL');
            DB::statement('ALTER TABLE landing_settings MODIFY feature_image_url VARCHAR(2000) NULL');
        }
    }
};
