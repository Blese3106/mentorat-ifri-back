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
        Schema::create('academic_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('type', ['lecture', 'exercice', 'projet', 'atelier', 'autre'])->default('exercice');
            $table->enum('priorite', ['faible', 'normale', 'haute'])->default('normale');
            $table->enum('statut', ['a_faire', 'en_cours', 'termine', 'en_retard'])->default('a_faire');
        
            $table->date('date_limite')->nullable();
            $table->text('commentaire_mentor')->nullable();
            $table->text('rendu_etudiant')->nullable();       // Ce que l'étudiant a rendu/commenté
            $table->timestamp('completed_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_tasks');
    }
};
