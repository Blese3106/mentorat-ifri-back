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
        Schema::create('ifristudents', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->unique();
            $table->string('name');
            $table->enum('filiere', ['GL', 'IM', 'SI', 'IA', 'SEIOT']);
            $table->year('promotion')->nullable();

            $table->boolean('is_registered')->default(false);

            $table->foreignId('user_id')
                  ->nullable()
                  ->unique()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ifristudents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
