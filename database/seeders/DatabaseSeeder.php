<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Pomodoro;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'patema',
            'email' => 'patema@gmail.com',
            'password' => Hash::make('123456789'),
        ]);

    }
}
