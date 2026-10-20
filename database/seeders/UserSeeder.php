<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Hanya untuk database auth yang masih kosong (dev lokal / instalasi pertama).
// Tabel users di Supabase dipakai semua app, jadi seeder ini tidak menambah apa pun bila sudah ada user.
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            $this->command?->warn('Tabel users sudah berisi data, UserSeeder dilewati.');

            return;
        }

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
        ]);
        $admin->syncRoles(config('access.super_admin_role'));
    }
}
