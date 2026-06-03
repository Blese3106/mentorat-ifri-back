<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epreuves', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('filiere', ['GL', 'SI', 'IM', 'IA', 'SEIOT']);
            $table->year('year');
            $table->enum('semester', ['S1', 'S2']);
            $table->enum('type', ['Examen final', 'Rattrapage', 'TP noté', 'Projet']);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->default(0);
            $table->integer('downloads')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epreuves');
    }
};