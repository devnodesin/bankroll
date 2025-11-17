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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->index();
            $table->date('date')->index();
            $table->text('description');
            $table->decimal('withdraw', 10, 2)->nullable();
            $table->decimal('deposit', 10, 2)->nullable();
            $table->decimal('balance', 10, 2);
            $table->string('reference_number')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->integer('year')->index();
            $table->integer('month')->index();
            $table->timestamps();
            
            // Composite index for common query pattern
            $table->index(['bank_name', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
