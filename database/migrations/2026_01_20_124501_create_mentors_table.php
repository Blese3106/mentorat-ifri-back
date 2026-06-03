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
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->boolean('oldstudent_ifri')->default(false);
            $table->integer('promotion')->nullable();
            $table->enum('filiere', ['GL', 'IM', 'SI', 'IA', 'SEIOT'])->nullable();
            $table->string('role')->nullable();
            $table->string('company')->nullable();
            $table->string('poste')->nullable();
            $table->string('email_contact')->unique();
            $table->integer('experience')->nullable();
            $table->decimal('price', 6, 2)->default(0);
            $table->text('bio');
            $table->enum('type', ['free', 'paid', 'both']);
            $table->string('linkedin');
            $table->string('portfolio')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('expertise')->nullable();
            $table->string('path_cv');
            $table->string('diplome')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
