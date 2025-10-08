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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('rental_id');
            $table->decimal('amount', 10, 2);
            $table->string('method')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed']);
            $table->timestamps();  // payment_date

            $table->foreign('rental_id')->references('uuid')->on('rentals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
