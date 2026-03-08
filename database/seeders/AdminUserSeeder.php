<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD', '@PORTUHUB2026');
        if (AdminUser::where('username', $username)->exists()) {
            $user = AdminUser::where('username', $username)->first();
            $user->update(['password_hash' => Hash::make($password)]);
            $this->command->info("Updated password for admin user: {$username}");
            return;
        }
        AdminUser::create([
            'username' => $username,
            'password_hash' => Hash::make($password),
        ]);
        $this->command->info("Created admin user: {$username}");
    }
}
