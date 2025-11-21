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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ean_code', 13)->nullable()->unique();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->string('subcategory')->nullable(); // e.g., "drób", "wołowina" for "mięso" category
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('ean_code');
            $table->index('category_id');
            $table->index('created_by');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
