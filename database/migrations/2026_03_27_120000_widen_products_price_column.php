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
            DB::statement('ALTER TABLE products MODIFY price DECIMAL(15, 2) NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN price TYPE DECIMAL(15, 2)');
            DB::statement('ALTER TABLE products ALTER COLUMN price SET DEFAULT 0');
            DB::statement('ALTER TABLE products ALTER COLUMN price SET NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY price DECIMAL(10, 2) NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE products ALTER COLUMN price TYPE DECIMAL(10, 2)');
            DB::statement('ALTER TABLE products ALTER COLUMN price SET DEFAULT 0');
            DB::statement('ALTER TABLE products ALTER COLUMN price SET NOT NULL');
        }
    }
};
