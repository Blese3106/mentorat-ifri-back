<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company');
            $table->string('location');
            $table->enum('type', ['Stage', 'CDI', 'CDD', 'Freelance', 'Alternance']);
            $table->string('duration');
            $table->enum('filiere', ['GL', 'SI', 'IM', 'IA', 'SEIOT', 'Toutes'])->default('Toutes');
            $table->text('description');
            $table->json('requirements')->nullable(); // tableau de compétences
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};