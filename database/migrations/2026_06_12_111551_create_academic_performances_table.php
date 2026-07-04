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
        Schema::create('academic_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_request_id')->constrained()->onDelete('cascade');
        
            $table->string('matiere');                          // Ex: Algorithmique, BDD...
            $table->decimal('note', 4, 2)->nullable();          // Note obtenue /20
            $table->decimal('note_precedente', 4, 2)->nullable();// Pour mesurer la progression
            $table->decimal('moyenne_generale', 4, 2)->nullable();
            $table->enum('semestre', ['S1','S2','S3','S4','S5','S6'])->nullable();
            $table->string('annee_academique')->nullable();      // Ex: 2024-2025
            $table->boolean('ue_validee')->default(false);       // Unité d'enseignement validée
        
            // Évaluation qualitative du mentor
            $table->enum('niveau_maitrise', ['faible', 'moyen', 'bon', 'excellent'])->nullable();
            $table->text('commentaire')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_performances');
    }
};
