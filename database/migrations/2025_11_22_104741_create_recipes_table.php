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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('mealdb_id')->unique()->nullable(); // ID z TheMealDB
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('area')->nullable(); // Kuchnia (Italian, Chinese, etc.)
            $table->text('instructions');
            $table->string('thumbnail')->nullable();
            $table->string('youtube')->nullable();
            $table->foreignId('user_group_id')->nullable()->constrained('user_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
            
            $table->index(['user_group_id', 'is_favorite']);
            $table->index('mealdb_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
