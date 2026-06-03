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
        Schema::create('request_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                  ->constrained('mentoring_requests')
                  ->onDelete('cascade');
 
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->onDelete('cascade');
 
            $table->enum('sender_type', ['student', 'mentor']);
 
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_messages');
    }
};
