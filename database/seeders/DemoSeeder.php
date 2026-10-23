<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/** Local only: an admin login (`admin@swvh.test` / `password`). */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@swvh.test'],
            ['name' => 'Demo Admin', 'password' => 'password', 'email_verified_at' => now()],
        );
    }
}
