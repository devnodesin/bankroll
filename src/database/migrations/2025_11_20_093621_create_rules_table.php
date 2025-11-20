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
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('description_match'); // Substring to match in transaction description
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['withdraw', 'deposit', 'both'])->default('both');
            $table->timestamps();
            
            // Index for better query performance
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
