<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_diagnostics', function (Blueprint $table) {
            $table->id();
 
            // Lien avec la demande de mentorat acceptée
            $table->foreignId('mentoring_request_id')
                  ->unique() // Un seul diagnostic par relation
                  ->constrained('mentoring_requests')
                  ->onDelete('cascade');
 
            // Profil académique de l'étudiant
            $table->string('filiere')->nullable();
            $table->string('niveau')->nullable(); // L1, L2, L3, M1, M2
            $table->integer('annee_promotion')->nullable();
 
            // Compétences déjà acquises (JSON : ["PHP", "HTML", ...])
            $table->json('competences_acquises')->nullable();
 
            // Compétences à développer
            $table->json('competences_a_developper')->nullable();
 
            // Soft skills identifiés
            $table->json('soft_skills')->nullable();
 
            // Objectifs personnels de l'étudiant (texte libre)
            $table->text('objectifs_personnels')->nullable();
 
            // Difficultés identifiées en amont
            $table->text('difficultes_initiales')->nullable();
 
            // Observations générales du mentor
            $table->text('observations')->nullable();
 
            // Statut : draft (brouillon) ou completed (finalisé)
            $table->enum('statut', ['draft', 'completed'])->default('draft');
 
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('academic_diagnostics');
    }
};
