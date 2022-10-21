<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Greeting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('|> Start seeding...');

        $startTime = microtime(true);

        User::factory(100)->create(['birthdate' => today('UTC')]);
        User::factory(100)->create(['birthdate' => today('UTC')]);
        User::factory(100)->create();
        User::factory(100)->create();

        foreach (User::all() as $user) {
            Greeting::factory()->withUser($user)->create();
        }

        $endTime = round(microtime(true) - $startTime, 2);

        $this->command->info("|> ✔ OK: Took {$endTime} seconds.");
    }
}
