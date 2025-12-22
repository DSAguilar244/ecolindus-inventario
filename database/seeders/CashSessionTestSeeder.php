<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CashSessionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ensures there is a user with id=1 and an open cash session for that user.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // Ensure a user with id = 1 exists. Use updateOrInsert so we don't duplicate.
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Test User',
                'email' => 'test-user+seed@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Create or update an open cash session for user_id = 1
        DB::table('cash_sessions')->updateOrInsert(
            ['user_id' => 1, 'status' => 'open'],
            [
                'opened_at' => $now,
                'opening_amount' => 100.00,
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
