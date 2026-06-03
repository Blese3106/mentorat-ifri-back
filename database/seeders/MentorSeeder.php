<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MentorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mentors')->insert([
            [   
                'user_id' => 3,
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'photo' => 'mentors/images/téléchargement.jpeg',
                'email' => 'dupont@gmail.com',
                'phone' => '97000000',
                'oldstudent_ifri' => true,
                'promotion' => 2018,
                'filiere' => 'GL',
                'role' => 'Développeur Backend',
                'company' => 'TechCorp',
                'poste' => 'Senior Developer',
                'email_contact' => 'fortu@gmail.com',
                'experience' => 5,
                'price' => 50.00,
                'bio' => 'Passionné de développement web et Laravel.',
                'type' => 'paid',
                'linkedin' => 'https://linkedin.com/in/jeandupont',
                'portfolio' => null,
                'status' => 'approved',
                'expertise' => json_encode(['Laravel', 'PHP', 'MySQL']),
                'path_cv' => 'cv/jean.pdf',
                'diplome' => 'Licence Informatique',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'firstname' => 'Marie',
                'lastname' => 'Kouassi',
                'photo' => 'mentors/images/téléchargement (1).jpeg',
                'email' => 'kouassi@gmail.com',
                'phone' => '96000000',
                'oldstudent_ifri' => false,
                'promotion' => null,
                'filiere' => 'IA',
                'role' => 'Data Scientist',
                'company' => 'DataLab',
                'poste' => 'ML Engineer',
                'email_contact' => 'mouty@gmail.com',
                'experience' => 3,
                'price' => 0.00,
                'bio' => 'Spécialiste en intelligence artificielle.',
                'type' => 'free',
                'linkedin' => 'https://linkedin.com/in/marie',
                'portfolio' => 'https://marie.dev',
                'status' => 'approved',
                'expertise' => json_encode(['Python', 'Machine Learning']),
                'path_cv' => 'cv/marie.pdf',
                'diplome' => 'Master IA',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}