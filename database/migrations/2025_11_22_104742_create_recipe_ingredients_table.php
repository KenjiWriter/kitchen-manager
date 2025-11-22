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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->string('original_name'); // Oryginalna nazwa z API
            $table->string('normalized_name'); // Znormalizowana nazwa do mapowania
            $table->string('measure'); // Ilość (np. "200g", "1 cup")
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null'); // Zmapowany produkt
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->onDelete('set null'); // Zmapowana kategoria
            $table->decimal('estimated_quantity', 10, 2)->nullable(); // Szacowana ilość w gramach
            $table->timestamps();
            
            $table->index('recipe_id');
            $table->index('product_id');
            $table->index('normalized_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
