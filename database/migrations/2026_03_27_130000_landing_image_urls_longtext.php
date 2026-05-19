<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE landing_settings MODIFY hero_image_url LONGTEXT NULL');
            DB::statement('ALTER TABLE landing_settings MODIFY feature_image_url LONGTEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE landing_settings ALTER COLUMN hero_image_url TYPE TEXT');
            DB::statement('ALTER TABLE landing_settings ALTER COLUMN feature_image_url TYPE TEXT');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE landing_settings MODIFY hero_image_url VARCHAR(2000) NULL');
            DB::statement('ALTER TABLE landing_settings MODIFY feature_image_url VARCHAR(2000) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE landing_settings ALTER COLUMN hero_image_url TYPE VARCHAR(2000)');
            DB::statement('ALTER TABLE landing_settings ALTER COLUMN feature_image_url TYPE VARCHAR(2000)');
        }
    }
};
