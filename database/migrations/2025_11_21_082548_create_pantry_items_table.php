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
        Schema::create('pantry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_group_id')->nullable()->constrained('user_groups')->onDelete('cascade');
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location')->nullable(); // e.g., "lodówka", "zamrażarka", "spiżarnia"
            $table->text('notes')->nullable();
            $table->timestamp('consumed_at')->nullable(); // when item was used/removed
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('added_by');
            $table->index('user_group_id');
            $table->index('expiry_date');
            $table->index('consumed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pantry_items');
    }
};
