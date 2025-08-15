<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Pomodoro;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'patema',
            'email' => 'patema@gmail.com',
            'password' => '123456789',
        ]);

        Note::factory()->create([
            'title' => 'Note 1',
            'body' => 'Body 1',
        ]);

        Pomodoro::factory()->create([
            'type' => 'study',
            'started_at' => 'Body 1',
        ]);
    }
}
