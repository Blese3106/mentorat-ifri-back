<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_difficulties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_request_id')->constrained()->onDelete('cascade');
        
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('categorie', [
                'matiere',          // Difficulté dans une matière
                'competence',       // Lacune technique
                'motivation',       // Manque de motivation
                'organisation',     // Mauvaise organisation
                'personnel',        // Contraintes personnelles
                'autre'
            ])->default('matiere');
            $table->enum('severite', ['faible', 'moderee', 'elevee'])->default('moderee');
            $table->enum('statut', ['en_cours', 'resolue', 'persistante'])->default('en_cours');
        
            // Plan d'action du mentor
            $table->text('recommandations')->nullable();
            $table->text('ressources')->nullable();          // Ressources complémentaires
            $table->text('plan_action')->nullable();
            $table->date('date_reevaluation')->nullable();
            $table->date('date_resolution')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_difficulties');
    }
};
