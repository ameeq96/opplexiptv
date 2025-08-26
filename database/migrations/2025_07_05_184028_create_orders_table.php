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
            $table->string('package')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('cost_price', 8, 2)->nullable();
            $table->decimal('sell_price', 8, 2)->nullable();
            $table->decimal('profit', 8, 2)->nullable();
            $table->integer('credits')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('status', ['pending', 'active', 'expired'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('custom_payment_method')->nullable();
            $table->string('custom_package')->nullable();
            $table->date('buying_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('currency')->nullable();
            $table->string('iptv_username')->nullable();
            $table->enum('type', ['package', 'reseller'])->default('package');
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
