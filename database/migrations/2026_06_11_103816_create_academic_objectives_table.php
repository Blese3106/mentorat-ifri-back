<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_objectives', function (Blueprint $table) {
            $table->id();
 
            // Lien avec la demande de mentorat
            $table->foreignId('mentoring_request_id')
                  ->constrained('mentoring_requests')
                  ->onDelete('cascade');
 
            // Qui a créé l'objectif (mentor ou étudiant ensemble)
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');
 
            $table->string('titre');
            $table->text('description')->nullable();
 
            // Catégorie : académique, technique, professionnel, soft_skill
            $table->enum('categorie', ['academique', 'technique', 'professionnel', 'soft_skill'])
                  ->default('academique');
 
            // Statut de l'objectif
            $table->enum('statut', [
                'non_commence',
                'en_cours',
                'atteint',
                'reporte',
                'abandonne'
            ])->default('non_commence');
 
            // Niveau de progression 0-100%
            $table->unsignedTinyInteger('progression')->default(0);
 
            // Dates
            $table->date('date_cible')->nullable();
            $table->date('date_atteint')->nullable(); // remplie quand statut = atteint
 
            // Notes du mentor sur cet objectif
            $table->text('commentaire_mentor')->nullable();
 
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('academic_objectives');
    }
};
 
