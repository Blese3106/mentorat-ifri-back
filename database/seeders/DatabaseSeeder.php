<?php

namespace Database\Seeders;

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

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ifriconnect.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Marie',
            'email' => 'marie@gmail.com',
            'password' => Hash::make('marie123'),
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Dupont',
            'email' => 'dupont@gmail.com',
            'password' => Hash::make('dupont123'),
            'role' => 'mentor',
        ]);

        User::create([
            'name' => 'Kouassi',
            'email' => 'kouassi@gmail.com',
            'password' => Hash::make('kouassi123'),
            'role' => 'mentor',
        ]);

        $this->call([
            IfriStudentSeeder::class,
            MentorSeeder::class,
        ]);
    }
}
