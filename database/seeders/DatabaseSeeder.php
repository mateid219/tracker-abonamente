<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Demo accounts for testing (see README). Runs on every Docker start:
     * firstOrCreate only creates missing accounts and never overwrites changes.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'Administrator Demo',
                'password' => Hash::make('Admin-Parola-2026'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
        );

        User::firstOrCreate(
            ['email' => 'demo@example.test'],
            [
                'name' => 'Utilizator Demo',
                'password' => Hash::make('Demo-Parola-2026'),
                'email_verified_at' => now(),
            ],
        );
    }
}
