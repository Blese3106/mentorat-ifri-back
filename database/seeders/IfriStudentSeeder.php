<?php

namespace Database\Seeders;

use App\Models\IfriStudent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IfriStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'identifier' => '10572222',
                'name' => 'Jean Dupont',
                'filiere' => 'GL',
                'promotion' => 2024,
                'is_registered' => false,
            ],
            [
                'identifier' => '10502222',
                'name' => 'Marie Koffi',
                'filiere' => 'IM',
                'promotion' => 2023,
                'is_registered' => false,
            ],
            [
                'identifier' => '10532223',
                'name' => 'Armand Hounkpe',
                'filiere' => 'SI',
                'promotion' => 2025,
                'is_registered' => false,
            ],
            [
                'identifier' => '10542223',
                'name' => 'Fatima Bello',
                'filiere' => 'IA',
                'promotion' => 2024,
                'is_registered' => false,
            ],
            [
                'identifier' => '10552524',
                'name' => 'Paul Adjovi',
                'filiere' => 'SEIOT',
                'promotion' => 2023,
                'is_registered' => false,
            ],
        ];

        foreach ($students as $student) {
            IfriStudent::create($student);
        }
    }
}
