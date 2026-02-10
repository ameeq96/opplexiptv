<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['shop_product_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_translations');
    }
};
