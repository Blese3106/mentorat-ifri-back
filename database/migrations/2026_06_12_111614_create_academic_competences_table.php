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
        Schema::create('academic_competences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentoring_request_id')->constrained()->onDelete('cascade');
        
            $table->string('nom');                                           // Ex: Laravel, Communication
            $table->enum('type', ['technique', 'soft_skill']);
            $table->unsignedTinyInteger('niveau_initial')->default(0);       // 0-5
            $table->unsignedTinyInteger('niveau_actuel')->default(0);        // 0-5
            $table->unsignedTinyInteger('niveau_cible')->default(3);         // 0-5
            $table->text('commentaire_mentor')->nullable();
            $table->date('date_evaluation')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_competences');
    }
};
