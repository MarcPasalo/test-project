<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        $owner = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('test123'),
        ]);

        $owner->currentTeam->users()->attach(
            User::factory()->create([
                'email' => 'admin@example.com',
                'password' => bcrypt('test123'),
                'current_team_id' => $owner->currentTeam->id
            ]), ['role' => 'admin']
        );

        $owner->currentTeam->users()->attach(
            User::factory()->create([
                'email' => 'editor@example.com',
                'password' => bcrypt('test123'),
                'current_team_id' => $owner->currentTeam->id
            ]), ['role' => 'editor']
        );
    }
}
