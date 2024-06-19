<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('users')->insert([
            'name' => 'User 1',
            'email' => 'user1@webtech.id',
            'password' => Hash::make('password1')
        ]);

        DB::table('users')->insert([
            'name' => 'User 2',
            'email' => 'user2@webtech.id',
            'password' => Hash::make('password2')
        ]);

        DB::table('users')->insert([
            'name' => 'User 3',
            'email' => 'user3@worldskills.org',
            'password' => Hash::make('password3')
        ]);
    }
}
