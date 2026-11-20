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
                'last_name' => 'Demo',
                'first_name' => 'Administrator',
                'password' => Hash::make('Admin-Parola-2026'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
        );

        $demo = User::firstOrCreate(
            ['email' => 'demo@example.test'],
            [
                'last_name' => 'Demo',
                'first_name' => 'Utilizator',
                'password' => Hash::make('Demo-Parola-2026'),
                'email_verified_at' => now(),
            ],
        );

        // A few example subscriptions, only when the demo account is first created.
        if ($demo->wasRecentlyCreated) {
            $demo->subscriptions()->createMany([
                ['name' => 'Sală', 'monthly_cost' => 120.00, 'status' => 'paused'],
                ['name' => 'Spotify', 'monthly_cost' => 9.99, 'status' => 'active'],
                ['name' => 'Netflix', 'monthly_cost' => 15.99, 'status' => 'active'],
            ]);
        }
    }
}
