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
        Schema::create('product_scan_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('scanned_by')->constrained('users')->onDelete('cascade');
            $table->string('ean_code', 13);
            $table->timestamp('scanned_at');
            $table->string('location')->nullable(); // Optional: gdzie zeskanowano (pantry/scanner)
            $table->text('notes')->nullable(); // Optional: notatki użytkownika
            $table->timestamps();
            
            // Indeksy dla szybkiego wyszukiwania
            $table->index('ean_code');
            $table->index('scanned_at');
            $table->index(['product_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_scan_history');
    }
};
