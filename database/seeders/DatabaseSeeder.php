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
     */
    public function run(): void
    {
        // Demo account for local testing (see README). Safe to run more than once.
        User::updateOrCreate(
            ['email' => 'demo@example.test'],
            [
                'name' => 'Utilizator Demo',
                'password' => Hash::make('Demo-Parola-2026'),
                'email_verified_at' => now(),
            ],
        );
    }
}
