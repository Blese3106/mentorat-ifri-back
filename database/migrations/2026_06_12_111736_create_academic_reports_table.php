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
        Schema::create('academic_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        
            $table->string('titre');
            $table->enum('periode', ['mensuel', 'trimestriel', 'semestriel', 'final'])->default('mensuel');
            $table->date('date_debut');
            $table->date('date_fin');
        
            // Contenu du bilan
            $table->text('objectifs_atteints')->nullable();
            $table->text('competences_developpees')->nullable();
            $table->text('points_ameliorer')->nullable();
            $table->text('difficultes_persistantes')->nullable();
            $table->text('recommandations')->nullable();
        
            // Note globale d'engagement 1-5
            $table->unsignedTinyInteger('note_engagement')->nullable();
            // Note globale de progression 1-5
            $table->unsignedTinyInteger('note_progression')->nullable();
        
            $table->enum('statut', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_reports');
    }
};
