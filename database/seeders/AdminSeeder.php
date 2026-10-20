<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        User::updateOrCreate(
            ['email' => strtolower($email)],
            [
                'name' => 'System Administrator',
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
