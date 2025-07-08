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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('package');
            $table->decimal('price', 8, 2);
            $table->integer('duration');
            $table->enum('status', ['pending', 'active', 'expired'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('custom_payment_method')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('currency')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
