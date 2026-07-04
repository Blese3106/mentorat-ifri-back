<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('mentors')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->string('message')->nullable();
            $table->string('student_filiere')->nullable();
            $table->string('student_niveau')->nullable(); 
            $table->integer('student_promotion')->nullable(); 
            $table->text('student_difficulties')->nullable();
            $table->text('student_goals')->nullable();
            $table->date('preferred_date')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('mentor_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_requests');
    }
};