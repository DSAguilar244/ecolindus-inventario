<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DevUserSeeder extends Seeder
{
    public function run()
    {
        // Create a simple developer user for local testing
        User::factory()->create([
            'email' => 'dev@local',
            'password' => bcrypt('password'),
        ]);
        $this->command->info('Dev user created: dev@local / password');
    }
}
